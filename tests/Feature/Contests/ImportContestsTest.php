<?php

use App\Domain\Contests\Models\Contest;

/**
 * Write a temporary CSV file and return its path.
 */
function contestsCsv(string $content): string
{
    $path = tempnam(sys_get_temp_dir(), 'contests');
    file_put_contents($path, $content);

    return $path;
}

test('contests are imported from a csv file', function () {
    $path = contestsCsv(<<<'CSV'
        name,date,available_places,registrations_open
        Corsa dei Colli,2027-05-02,150,1
        "Trail, delle Abbazie",2027-06-13,120,0
        CSV);

    $this->artisan('contests:import', ['path' => $path])->assertSuccessful();

    $this->assertDatabaseCount('contests', 2);
    $this->assertDatabaseHas('contests', [
        'name' => 'Corsa dei Colli',
        'date' => '2027-05-02',
        'available_places' => 150,
        'registrations_open' => true,
    ]);
    $this->assertDatabaseHas('contests', [
        'name' => 'Trail, delle Abbazie',
        'available_places' => 120,
        'registrations_open' => false,
    ]);
});

test('importing again updates the contests by name and date instead of duplicating them', function () {
    $this->artisan('contests:import', ['path' => contestsCsv(<<<'CSV'
        name,date,available_places,registrations_open
        Corsa dei Colli,2027-05-02,150,1
        CSV)])->assertSuccessful();

    $this->artisan('contests:import', ['path' => contestsCsv(<<<'CSV'
        name,date,available_places,registrations_open
        Corsa dei Colli,2027-05-02,200,0
        Corsa dei Colli,2028-05-07,150,1
        CSV)])->assertSuccessful();

    $this->assertDatabaseCount('contests', 2);
    $this->assertDatabaseHas('contests', [
        'name' => 'Corsa dei Colli',
        'date' => '2027-05-02',
        'available_places' => 200,
        'registrations_open' => false,
    ]);
});

test('import fails without the header line', function () {
    $path = contestsCsv(<<<'CSV'
        Corsa dei Colli,2027-05-02,150,1
        CSV);

    $this->artisan('contests:import', ['path' => $path])->assertFailed();

    $this->assertDatabaseCount('contests', 0);
});

test('one invalid row makes the import fail and write nothing', function () {
    $path = contestsCsv(<<<'CSV'
        name,date,available_places,registrations_open
        Corsa dei Colli,2027-05-02,150,1
        Trail delle Abbazie,13/06/2027,120,0
        CSV);

    $this->artisan('contests:import', ['path' => $path])
        ->expectsOutputToContain('Line 3')
        ->assertFailed();

    $this->assertDatabaseCount('contests', 0);
});

test('import fails when the file does not exist', function () {
    $this->artisan('contests:import', ['path' => '/nowhere/contests.csv'])->assertFailed();
});

test('sample file holds ten future contests, at least one of them closed', function () {
    $this->artisan('contests:import')->assertSuccessful();

    expect(Contest::query()->count())->toBe(10)
        ->and(Contest::query()->whereDate('date', '<=', now())->count())->toBe(0)
        ->and(Contest::query()->where('registrations_open', false)->count())->toBeGreaterThanOrEqual(1);
});
