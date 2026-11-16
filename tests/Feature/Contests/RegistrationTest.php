<?php

use App\Domain\Contests\Models\Contest;
use App\Domain\Runners\Models\Runner;
use App\Domain\Users\Models\User;
use App\Infrastructure\Mail\RegistrationConfirmedMail;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia;

/**
 * Valid form input of an adult, with the given overrides.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function registrationInput(array $overrides = []): array
{
    return [
        'first_name' => 'Mario',
        'last_name' => 'Rossi',
        'birth_date' => '1990-05-20',
        'email' => 'mario.rossi@example.com',
        ...$overrides,
    ];
}

beforeEach(function () {
    $this->travelTo('2026-11-16 10:00:00');
});

test('registration form of a contest is displayed', function () {
    $contest = Contest::factory()->create(['name' => 'Corsa dei Colli', 'date' => '2027-05-02']);

    $this->get(route('contests.registration.create', $contest))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('contests/registration')
            ->where('contest.id', $contest->id)
            ->where('contest.name', 'Corsa dei Colli')
            ->where('contest.date', '2027-05-02'));
});

test('a new person is registered with a user, a runner and a queued confirmation mail', function () {
    Mail::fake();
    $contest = Contest::factory()->create(['date' => '2027-05-02']);

    $this->post(route('contests.registration.store', $contest), registrationInput())
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $user = User::query()->where('email', 'mario.rossi@example.com')->sole();
    $runner = $user->runner;

    expect($user->name)->toBe('Mario Rossi')
        ->and($user->is_admin)->toBeFalse()
        ->and($runner->first_name)->toBe('Mario')
        ->and($runner->last_name)->toBe('Rossi')
        ->and($runner->birth_date->toDateString())->toBe('1990-05-20');

    $this->assertDatabaseHas('contest_runner', [
        'contest_id' => $contest->id,
        'runner_id' => $runner->id,
        'registered_at' => '2026-11-16 10:00:00',
        'checked_in_at' => null,
    ]);

    Mail::assertQueued(
        RegistrationConfirmedMail::class,
        fn (RegistrationConfirmedMail $mail): bool => $mail->hasTo('mario.rossi@example.com')
            && $mail->runner->is($runner)
            && $mail->contest->is($contest),
    );
});

test('confirmation mail greets the runner and names the contest', function () {
    $contest = Contest::factory()->create(['name' => 'Corsa dei Colli', 'date' => '2027-05-02']);
    $runner = Runner::factory()->create(['first_name' => 'Mario', 'last_name' => 'Rossi']);

    (new RegistrationConfirmedMail($runner, $contest))
        ->assertHasSubject('Iscrizione confermata: Corsa dei Colli')
        ->assertSeeInText('Ciao Mario Rossi')
        ->assertSeeInText('02/05/2027');
});

test('names and email are repaired before they are stored', function () {
    Mail::fake();
    $contest = Contest::factory()->create(['date' => '2027-05-02']);

    $this->post(route('contests.registration.store', $contest), registrationInput([
        'first_name' => "NICOLO'",
        'last_name' => 'D’ANGELO,',
        'email' => 'Nicolo.DAngeloòGmail.COM',
    ]))->assertSessionHasNoErrors();

    $user = User::query()->where('email', 'nicolo.dangelo@gmail.com')->sole();

    expect($user->name)->toBe("Nicolò D'Angelo")
        ->and($user->runner->first_name)->toBe('Nicolò')
        ->and($user->runner->last_name)->toBe("D'Angelo");
});

test('names holding markdown or a link are rejected', function (string $field, string $value) {
    Mail::fake();
    $contest = Contest::factory()->create(['date' => '2027-05-02']);

    $this->post(route('contests.registration.store', $contest), registrationInput([$field => $value]))
        ->assertSessionHasErrors($field);

    expect(User::query()->count())->toBe(0);
    Mail::assertNothingQueued();
})->with([
    'markdown link in first name' => ['first_name', '[Mario](https://evil.example)'],
    'bold in first name' => ['first_name', '**Mario**'],
    'url in last name' => ['last_name', 'https://evil.example'],
    'image in last name' => ['last_name', '![x](https://evil.example/x.png)'],
    'heading in last name' => ['last_name', '# Rossi'],
    'digits in last name' => ['last_name', 'Rossi2'],
    'dot in the middle of last name' => ['last_name', 'evil.example'],
]);

test('an email that cannot be repaired is rejected', function () {
    $contest = Contest::factory()->create(['date' => '2027-05-02']);

    $this->post(route('contests.registration.store', $contest), registrationInput(['email' => 'mario']))
        ->assertSessionHasErrors('email');

    expect(User::query()->count())->toBe(0);
});

test('registration to a contest already held is rejected', function () {
    Mail::fake();
    $contest = Contest::factory()->create(['date' => '2026-11-15']);

    $this->post(route('contests.registration.store', $contest), registrationInput())
        ->assertSessionHasErrors(['contest' => 'Questa gara si è già svolta.']);

    $this->assertDatabaseCount('contest_runner', 0);
    Mail::assertNothingQueued();
});

test('registration is still accepted on the day of the contest', function () {
    Mail::fake();
    $contest = Contest::factory()->create(['date' => '2026-11-16']);

    $this->post(route('contests.registration.store', $contest), registrationInput())
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('contest_runner', 1);
});

test('registration is rejected when registrations are closed', function () {
    $contest = Contest::factory()->closed()->create(['date' => '2027-05-02']);

    $this->post(route('contests.registration.store', $contest), registrationInput())
        ->assertSessionHasErrors(['contest' => 'Le iscrizioni a questa gara sono chiuse.']);

    $this->assertDatabaseCount('contest_runner', 0);
});

test('registration is rejected when no places are left', function () {
    $contest = Contest::factory()->create(['date' => '2027-05-02', 'available_places' => 1]);
    $contest->runners()->attach(Runner::factory()->create(), ['registered_at' => now()]);

    $this->post(route('contests.registration.store', $contest), registrationInput())
        ->assertSessionHasErrors(['contest' => 'I posti per questa gara sono esauriti.']);

    $this->assertDatabaseCount('contest_runner', 1);
});

test('a deleted runner is rejected and is not restored', function () {
    $contest = Contest::factory()->create(['date' => '2027-05-02']);
    $runner = Runner::factory()
        ->for(User::factory()->state(['email' => 'mario.rossi@example.com']))
        ->create();
    $runner->delete();

    $this->post(route('contests.registration.store', $contest), registrationInput())
        ->assertSessionHasErrors('email');

    expect($runner->fresh()->trashed())->toBeTrue();
    $this->assertDatabaseCount('contest_runner', 0);
});

test('a person not yet 18 on the day of the contest is rejected', function () {
    $contest = Contest::factory()->create(['date' => '2027-05-02']);

    $this->post(route('contests.registration.store', $contest), registrationInput(['birth_date' => '2009-05-03']))
        ->assertSessionHasErrors(['birth_date' => 'Devi avere compiuto 18 anni entro il giorno della gara.']);

    expect(User::query()->count())->toBe(0);
});

test('a person turning 18 on the day of the contest is accepted', function () {
    Mail::fake();
    $contest = Contest::factory()->create(['date' => '2027-05-02']);

    $this->post(route('contests.registration.store', $contest), registrationInput(['birth_date' => '2009-05-02']))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseCount('contest_runner', 1);
});

test('a runner already registered to the contest is rejected', function () {
    Mail::fake();
    $contest = Contest::factory()->create(['date' => '2027-05-02']);
    $runner = Runner::factory()
        ->for(User::factory()->state(['email' => 'mario.rossi@example.com']))
        ->create();
    $contest->runners()->attach($runner, ['registered_at' => now()]);

    $this->post(route('contests.registration.store', $contest), registrationInput())
        ->assertSessionHasErrors(['email' => 'Questa email è già iscritta alla gara.']);

    $this->assertDatabaseCount('contest_runner', 1);
    Mail::assertNothingQueued();
});

test('a known email reuses its user and runner without overwriting them', function () {
    Mail::fake();
    $contest = Contest::factory()->create(['date' => '2027-05-02']);
    $user = User::factory()->create(['name' => 'Maria Bianchi', 'email' => 'mario.rossi@example.com']);
    $runner = Runner::factory()->for($user)->create([
        'first_name' => 'Maria',
        'last_name' => 'Bianchi',
        'birth_date' => '1985-01-10',
    ]);
    $password = $user->password;

    $this->post(route('contests.registration.store', $contest), registrationInput())
        ->assertSessionHasNoErrors();

    expect(User::query()->count())->toBe(1)
        ->and(Runner::query()->count())->toBe(1)
        ->and($user->fresh()->name)->toBe('Maria Bianchi')
        ->and($user->fresh()->password)->toBe($password)
        ->and($runner->fresh()->first_name)->toBe('Maria')
        ->and($runner->fresh()->birth_date->toDateString())->toBe('1985-01-10');

    $this->assertDatabaseHas('contest_runner', ['contest_id' => $contest->id, 'runner_id' => $runner->id]);
});

test('age of a known runner is judged on the stored birth date, not on the submitted one', function () {
    Mail::fake();
    $contest = Contest::factory()->create(['date' => '2027-05-02']);
    Runner::factory()
        ->for(User::factory()->state(['email' => 'minor@example.com']))
        ->create(['birth_date' => '2012-01-01']);
    Runner::factory()
        ->for(User::factory()->state(['email' => 'adult@example.com']))
        ->create(['birth_date' => '1980-01-01']);

    $this->post(route('contests.registration.store', $contest), registrationInput([
        'email' => 'minor@example.com',
        'birth_date' => '1980-01-01',
    ]))->assertSessionHasErrors('birth_date');

    $this->post(route('contests.registration.store', $contest), registrationInput([
        'email' => 'adult@example.com',
        'birth_date' => '2012-01-01',
    ]))->assertSessionHasNoErrors();

    $this->assertDatabaseCount('contest_runner', 1);
});

test('a known email without a runner gets only the runner', function () {
    Mail::fake();
    $contest = Contest::factory()->create(['date' => '2027-05-02']);
    $admin = User::factory()->admin()->create(['name' => 'Admin', 'email' => 'mario.rossi@example.com']);

    $this->post(route('contests.registration.store', $contest), registrationInput())
        ->assertSessionHasNoErrors();

    $admin->refresh();

    expect(User::query()->count())->toBe(1)
        ->and($admin->name)->toBe('Admin')
        ->and($admin->is_admin)->toBeTrue()
        ->and($admin->runner->first_name)->toBe('Mario')
        ->and($admin->runner->birth_date->toDateString())->toBe('1990-05-20');

    $this->assertDatabaseHas('contest_runner', ['contest_id' => $contest->id, 'runner_id' => $admin->runner->id]);
});

test('registration returns 429 after ten attempts in a minute', function () {
    $contest = Contest::factory()->closed()->create(['date' => '2027-05-02']);

    foreach (range(1, 10) as $attempt) {
        $this->post(route('contests.registration.store', $contest), registrationInput())->assertRedirect();
    }

    $this->post(route('contests.registration.store', $contest), registrationInput())
        ->assertTooManyRequests();
});
