<?php

namespace App\Application\Actions\Contests;

use App\Application\Actions\BaseAction;
use App\Domain\Contests\Exceptions\RunnerAlreadyCheckedInException;
use App\Domain\Contests\Exceptions\RunnerNotRegisteredException;
use App\Domain\Contests\Models\Contest;
use App\Domain\Runners\Models\Runner;
use Carbon\CarbonImmutable;

class CheckInRunnerAction extends BaseAction
{
    /**
     * Check a registered runner in at a contest and return the time of the check-in.
     *
     * A single conditional UPDATE sets the time only while it is still empty, so two
     * concurrent requests cannot both succeed.
     *
     * @throws RunnerNotRegisteredException
     * @throws RunnerAlreadyCheckedInException
     */
    public function execute(Contest $contest, Runner $runner): CarbonImmutable
    {
        $checkedInAt = CarbonImmutable::now()->startOfSecond();

        $updated = $contest->runners()
            ->wherePivotNull('checked_in_at')
            ->updateExistingPivot($runner->id, ['checked_in_at' => $checkedInAt], touch: false);

        if ($updated === 1) {
            $this->log('Runner checked in', [
                'contest_id' => $contest->id,
                'runner_id' => $runner->id,
            ]);

            return $checkedInAt;
        }

        if ($contest->runners()->whereKey($runner->id)->exists()) {
            throw new RunnerAlreadyCheckedInException;
        }

        throw new RunnerNotRegisteredException;
    }
}
