<?php

namespace App\Modules\Pets\Policies;

use App\Models\User;
use App\Modules\Pets\Models\CareRecord;

class CareRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CareRecord $record): bool
    {
        return $record->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, CareRecord $record): bool
    {
        return $record->isVisibleTo($user);
    }

    public function delete(User $user, CareRecord $record): bool
    {
        return $record->isVisibleTo($user);
    }
}
