<?php

namespace App\Domain\Contests\Enums;

enum RegistrationRejection: string
{
    case ContestAlreadyHeld = 'contest_already_held';
    case RegistrationsClosed = 'registrations_closed';
    case NoPlacesLeft = 'no_places_left';
    case RunnerDeleted = 'runner_deleted';
    case Underage = 'underage';
    case AlreadyRegistered = 'already_registered';
}
