<?php

use App\Domain\Contests\Models\Contest;
use App\Domain\Runners\Models\Runner;
use App\Domain\Users\Models\User;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->travelTo('2027-05-02 08:30:00');

    $this->contest = Contest::factory()->create(['date' => '2027-05-02']);
    $this->runner = Runner::factory()->create();
});

test('check-in returns 200 and stores the time for a registered runner', function () {
    $this->contest->runners()->attach($this->runner, ['registered_at' => now()->subMonth()]);
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson(route('api.contests.runners.check-in', [$this->contest, $this->runner]))
        ->assertOk()
        ->assertJsonPath('data.contest_id', $this->contest->id)
        ->assertJsonPath('data.runner_id', $this->runner->id)
        ->assertJsonPath('data.checked_in_at', now()->toIso8601String());

    $this->assertDatabaseHas('contest_runner', [
        'contest_id' => $this->contest->id,
        'runner_id' => $this->runner->id,
        'checked_in_at' => '2027-05-02 08:30:00',
    ]);
});

test('check-in returns 401 without a token', function () {
    $this->contest->runners()->attach($this->runner, ['registered_at' => now()]);

    $this->postJson(route('api.contests.runners.check-in', [$this->contest, $this->runner]))
        ->assertUnauthorized();

    $this->assertDatabaseHas('contest_runner', ['runner_id' => $this->runner->id, 'checked_in_at' => null]);
});

test('check-in returns 403 for a user who is not an administrator', function () {
    $this->contest->runners()->attach($this->runner, ['registered_at' => now()]);
    Sanctum::actingAs(User::factory()->create());

    $this->postJson(route('api.contests.runners.check-in', [$this->contest, $this->runner]))
        ->assertForbidden();

    $this->assertDatabaseHas('contest_runner', ['runner_id' => $this->runner->id, 'checked_in_at' => null]);
});

test('check-in returns 404 when the runner is not registered to the contest', function () {
    Contest::factory()->create()->runners()->attach($this->runner, ['registered_at' => now()]);
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson(route('api.contests.runners.check-in', [$this->contest, $this->runner]))
        ->assertNotFound();

    $this->assertDatabaseHas('contest_runner', ['runner_id' => $this->runner->id, 'checked_in_at' => null]);
});

test('check-in returns 409 when it was already done and keeps the first time', function () {
    $this->contest->runners()->attach($this->runner, [
        'registered_at' => now()->subMonth(),
        'checked_in_at' => '2027-05-02 07:00:00',
    ]);
    Sanctum::actingAs(User::factory()->admin()->create());

    $this->postJson(route('api.contests.runners.check-in', [$this->contest, $this->runner]))
        ->assertConflict();

    $this->assertDatabaseHas('contest_runner', [
        'runner_id' => $this->runner->id,
        'checked_in_at' => '2027-05-02 07:00:00',
    ]);
});
