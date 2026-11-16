<?php

namespace App\Application\Actions\Contests;

use App\Application\Actions\BaseAction;
use App\Domain\Contests\Models\Contest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class ListUpcomingContestsAction extends BaseAction
{
    /**
     * Contests not held yet, the ones of today included, by date, each with its number of registered runners.
     *
     * @return Collection<int, Contest>
     */
    public function execute(): Collection
    {
        return Contest::query()
            ->upcoming(CarbonImmutable::today())
            ->withCount('runners')
            ->orderBy('date')
            ->orderBy('name')
            ->get();
    }
}
