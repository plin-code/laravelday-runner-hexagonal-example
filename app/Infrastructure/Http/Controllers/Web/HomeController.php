<?php

namespace App\Infrastructure\Http\Controllers\Web;

use App\Application\Actions\Contests\ListUpcomingContestsAction;
use App\Domain\Contests\Models\Contest;
use App\Infrastructure\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Show the upcoming contests.
     */
    public function __invoke(ListUpcomingContestsAction $listUpcomingContests): Response
    {
        return Inertia::render('home', [
            'contests' => $listUpcomingContests->execute()
                ->map(function (Contest $contest): array {
                    $placesLeft = $contest->placesLeft((int) $contest->runners_count);

                    return [
                        'id' => $contest->id,
                        'name' => $contest->name,
                        'date' => $contest->date->toDateString(),
                        'places_left' => $placesLeft,
                        'status' => match (true) {
                            ! $contest->registrations_open => 'closed',
                            $placesLeft === 0 => 'sold_out',
                            default => 'open',
                        },
                    ];
                })
                ->all(),
        ]);
    }
}
