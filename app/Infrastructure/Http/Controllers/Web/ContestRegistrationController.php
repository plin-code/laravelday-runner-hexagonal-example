<?php

namespace App\Infrastructure\Http\Controllers\Web;

use App\Application\Actions\Contests\RegisterToContestAction;
use App\Application\DTOs\RegistrationData;
use App\Domain\Contests\Enums\RegistrationRejection;
use App\Domain\Contests\Exceptions\RegistrationRejectedException;
use App\Domain\Contests\Models\Contest;
use App\Infrastructure\Http\Controllers\Controller;
use App\Infrastructure\Http\Requests\Contests\StoreRegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ContestRegistrationController extends Controller
{
    /**
     * Show the registration form of a contest.
     */
    public function create(Contest $contest): Response
    {
        return Inertia::render('contests/registration', [
            'contest' => [
                'id' => $contest->id,
                'name' => $contest->name,
                'date' => $contest->date->toDateString(),
            ],
        ]);
    }

    /**
     * Register the person to the contest.
     */
    public function store(
        StoreRegistrationRequest $request,
        Contest $contest,
        RegisterToContestAction $registerToContest,
    ): RedirectResponse {
        /** @var array{first_name: string, last_name: string, birth_date: string, email: string} $validated */
        $validated = $request->validated();

        try {
            $registerToContest->execute($contest, RegistrationData::fromValidated($validated));
        } catch (RegistrationRejectedException $exception) {
            throw ValidationException::withMessages($this->rejectionMessage($exception->reason));
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Iscrizione a {$contest->name} registrata. Riceverai una mail di conferma.",
        ]);

        return to_route('home');
    }

    /**
     * Map a rejection of the domain to the form key that shows it and its message.
     *
     * @return array<string, string>
     */
    private function rejectionMessage(RegistrationRejection $reason): array
    {
        return match ($reason) {
            RegistrationRejection::ContestAlreadyHeld => ['contest' => 'Questa gara si è già svolta.'],
            RegistrationRejection::RegistrationsClosed => ['contest' => 'Le iscrizioni a questa gara sono chiuse.'],
            RegistrationRejection::NoPlacesLeft => ['contest' => 'I posti per questa gara sono esauriti.'],
            RegistrationRejection::RunnerDeleted => ['email' => 'Il profilo associato a questa email è stato disattivato. Contatta gli organizzatori.'],
            RegistrationRejection::Underage => ['birth_date' => 'Devi avere compiuto 18 anni entro il giorno della gara.'],
            RegistrationRejection::AlreadyRegistered => ['email' => 'Questa email è già iscritta alla gara.'],
        };
    }
}
