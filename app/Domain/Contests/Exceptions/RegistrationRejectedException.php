<?php

namespace App\Domain\Contests\Exceptions;

use App\Domain\Contests\Enums\RegistrationRejection;
use DomainException;

class RegistrationRejectedException extends DomainException
{
    public function __construct(public readonly RegistrationRejection $reason)
    {
        parent::__construct("Registration rejected: {$reason->value}.");
    }
}
