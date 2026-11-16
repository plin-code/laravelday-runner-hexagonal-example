<?php

namespace App\Application\Contracts;

use App\Domain\Contests\Models\Contest;
use App\Domain\Runners\Models\Runner;

interface RegistrationNotifier
{
    /**
     * Tell the runner that the registration to the contest is confirmed.
     */
    public function registrationConfirmed(Runner $runner, Contest $contest): void;
}
