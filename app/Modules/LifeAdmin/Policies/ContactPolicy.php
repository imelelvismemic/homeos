<?php

namespace App\Modules\LifeAdmin\Policies;

use App\Models\User;
use App\Modules\LifeAdmin\Models\Contact;

class ContactPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Contact $contact): bool
    {
        return $contact->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Contact $contact): bool
    {
        return $contact->isVisibleTo($user);
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $contact->isVisibleTo($user);
    }
}
