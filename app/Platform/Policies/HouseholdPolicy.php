<?php

namespace App\Platform\Policies;

use App\Models\User;
use App\Platform\Models\Household;

class HouseholdPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Household $household): bool
    {
        return $household->users()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Household $household): bool
    {
        return $household->owner_id === $user->id;
    }

    public function delete(User $user, Household $household): bool
    {
        return $household->owner_id === $user->id;
    }
}
