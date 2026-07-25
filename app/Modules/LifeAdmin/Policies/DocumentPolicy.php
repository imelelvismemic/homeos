<?php

namespace App\Modules\LifeAdmin\Policies;

use App\Models\User;
use App\Modules\LifeAdmin\Models\Document;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Document $document): bool
    {
        return $document->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Document $document): bool
    {
        return $document->isVisibleTo($user);
    }

    public function delete(User $user, Document $document): bool
    {
        return $document->isVisibleTo($user);
    }
}
