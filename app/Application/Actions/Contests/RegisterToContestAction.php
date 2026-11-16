<?php

namespace App\Application\Actions\Contests;

use App\Application\Actions\BaseAction;
use App\Application\Contracts\RegistrationNotifier;
use App\Application\DTOs\RegistrationData;
use App\Domain\Contests\Enums\RegistrationRejection;
use App\Domain\Contests\Exceptions\RegistrationRejectedException;
use App\Domain\Contests\Models\Contest;
use App\Domain\Runners\Models\Runner;
use App\Domain\Users\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegisterToContestAction extends BaseAction
{
    public function __construct(private readonly RegistrationNotifier $notifier) {}

    /**
     * Register a person to a contest, creating the user and the runner when the email is new.
     *
     * The contest row is locked so that the count of the places left and the new registration
     * are atomic. The confirmation leaves only after the commit.
     *
     * @throws RegistrationRejectedException
     */
    public function execute(Contest $contest, RegistrationData $data): Runner
    {
        return DB::transaction(function () use ($contest, $data): Runner {
            $contest = Contest::query()->lockForUpdate()->findOrFail($contest->id);

            if ($contest->hasBeenHeld(CarbonImmutable::today())) {
                throw new RegistrationRejectedException(RegistrationRejection::ContestAlreadyHeld);
            }

            if (! $contest->registrations_open) {
                throw new RegistrationRejectedException(RegistrationRejection::RegistrationsClosed);
            }

            if ($contest->placesLeft($contest->runners()->count()) === 0) {
                throw new RegistrationRejectedException(RegistrationRejection::NoPlacesLeft);
            }

            $user = User::query()->where('email', $data->email)->first();
            $runner = $user === null
                ? null
                : Runner::withTrashed()->where('user_id', $user->id)->first();

            if ($runner?->trashed()) {
                throw new RegistrationRejectedException(RegistrationRejection::RunnerDeleted);
            }

            if (! $contest->admitsRunnerBornOn($runner->birth_date ?? $data->birthDate)) {
                throw new RegistrationRejectedException(RegistrationRejection::Underage);
            }

            if ($runner !== null && $contest->runners()->whereKey($runner->id)->exists()) {
                throw new RegistrationRejectedException(RegistrationRejection::AlreadyRegistered);
            }

            $user ??= User::query()->create([
                'name' => $data->fullName(),
                'email' => $data->email,
                'password' => Str::password(32),
            ]);

            $runner ??= Runner::query()->create([
                'user_id' => $user->id,
                'first_name' => $data->firstName,
                'last_name' => $data->lastName,
                'birth_date' => $data->birthDate,
            ]);

            $contest->runners()->attach($runner->id, ['registered_at' => CarbonImmutable::now()]);

            $this->log('Runner registered to contest', [
                'contest_id' => $contest->id,
                'runner_id' => $runner->id,
            ]);

            DB::afterCommit(fn () => $this->notifier->registrationConfirmed($runner, $contest));

            return $runner;
        });
    }
}
