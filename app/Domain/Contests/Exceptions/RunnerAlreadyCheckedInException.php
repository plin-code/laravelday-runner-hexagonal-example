<?php

namespace App\Domain\Contests\Exceptions;

use DomainException;

class RunnerAlreadyCheckedInException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The runner has already checked in to this contest.');
    }
}
