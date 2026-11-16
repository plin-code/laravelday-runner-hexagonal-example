<?php

use App\Domain\Contests\Models\Contest;
use App\Domain\Runners\Models\Runner;
use Inertia\Testing\AssertableInertia;

test('home lists the contests not held yet by date, the one of today included', function () {
    $this->travelTo('2026-11-16 10:00:00');

    Contest::factory()->create(['name' => 'Later', 'date' => '2027-03-01']);
    Contest::factory()->create(['name' => 'Today', 'date' => '2026-11-16']);
    Contest::factory()->create(['name' => 'Yesterday', 'date' => '2026-11-15']);
    Contest::factory()->create(['name' => 'Sooner', 'date' => '2026-12-01']);

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('home')
            ->has('contests', 3)
            ->where('contests.0.name', 'Today')
            ->where('contests.0.date', '2026-11-16')
            ->where('contests.1.name', 'Sooner')
            ->where('contests.2.name', 'Later'));
});

test('home shows the places left and why a contest cannot be joined', function () {
    $open = Contest::factory()->create(['date' => now()->addDays(10), 'available_places' => 10]);
    $open->runners()->attach(Runner::factory(3)->create(), ['registered_at' => now()]);

    Contest::factory()->closed()->create(['date' => now()->addDays(20), 'available_places' => 10]);

    $soldOut = Contest::factory()->create(['date' => now()->addDays(30), 'available_places' => 1]);
    $soldOut->runners()->attach(Runner::factory()->create(), ['registered_at' => now()]);

    $this->get(route('home'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('contests.0.places_left', 7)
            ->where('contests.0.status', 'open')
            ->where('contests.1.places_left', 10)
            ->where('contests.1.status', 'closed')
            ->where('contests.2.places_left', 0)
            ->where('contests.2.status', 'sold_out'));
});
