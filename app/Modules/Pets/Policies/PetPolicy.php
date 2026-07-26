<?php

namespace App\Modules\Pets\Policies;

use App\Models\User;
use App\Modules\Pets\Models\Pet;

class PetPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Pet $pet): bool
    {
        return $pet->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Pet $pet): bool
    {
        return $pet->isVisibleTo($user);
    }

    public function delete(User $user, Pet $pet): bool
    {
        return $pet->isVisibleTo($user);
    }
}
