<?php

namespace App\Infrastructure\Mail;

use App\Application\Contracts\RegistrationNotifier;
use App\Domain\Contests\Models\Contest;
use App\Domain\Runners\Models\Runner;
use Illuminate\Support\Facades\Mail;

class MailRegistrationNotifier implements RegistrationNotifier
{
    /**
     * Queue the confirmation mail to the address of the runner's user.
     */
    public function registrationConfirmed(Runner $runner, Contest $contest): void
    {
        Mail::to($runner->user->email)->send(new RegistrationConfirmedMail($runner, $contest));
    }
}
