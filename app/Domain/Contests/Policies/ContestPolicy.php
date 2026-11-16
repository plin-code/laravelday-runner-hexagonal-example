<?php

namespace App\Domain\Contests\Policies;

use App\Domain\Contests\Models\Contest;
use App\Domain\Users\Models\User;

class ContestPolicy
{
    /**
     * Only administrators check runners in at a contest.
     */
    public function checkIn(User $user, Contest $contest): bool
    {
        return $user->is_admin;
    }
}
