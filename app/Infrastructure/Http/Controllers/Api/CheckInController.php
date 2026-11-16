<?php

namespace App\Infrastructure\Http\Controllers\Api;

use App\Application\Actions\Contests\CheckInRunnerAction;
use App\Domain\Contests\Exceptions\RunnerAlreadyCheckedInException;
use App\Domain\Contests\Exceptions\RunnerNotRegisteredException;
use App\Domain\Contests\Models\Contest;
use App\Domain\Runners\Models\Runner;
use App\Infrastructure\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class CheckInController extends Controller
{
    /**
     * Check a registered runner in at a contest.
     */
    public function __invoke(Contest $contest, Runner $runner, CheckInRunnerAction $checkInRunner): JsonResponse
    {
        try {
            $checkedInAt = $checkInRunner->execute($contest, $runner);
        } catch (RunnerNotRegisteredException) {
            return $this->errorResponse('The runner is not registered to this contest.', 404);
        } catch (RunnerAlreadyCheckedInException) {
            return $this->errorResponse('The runner has already checked in.', 409);
        }

        return $this->successResponse([
            'contest_id' => $contest->id,
            'runner_id' => $runner->id,
            'checked_in_at' => $checkedInAt->toIso8601String(),
        ], 'Check-in recorded.');
    }
}
