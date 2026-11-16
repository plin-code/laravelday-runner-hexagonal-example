<?php

namespace App\Domain\Contests\Exceptions;

use DomainException;

class RunnerNotRegisteredException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The runner is not registered to this contest.');
    }
}
