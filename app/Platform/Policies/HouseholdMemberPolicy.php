<?php

namespace App\Platform\Policies;

use App\Models\User;
use App\Platform\Models\HouseholdMember;
use Filament\Facades\Filament;

class HouseholdMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, HouseholdMember $householdMember): bool
    {
        return $householdMember->household->users()->whereKey($user->id)->exists();
    }

    public function create(User $user): bool
    {
        $household = Filament::getTenant();

        return $household && $household->owner_id === $user->id;
    }

    public function delete(User $user, HouseholdMember $householdMember): bool
    {
        return $householdMember->household->owner_id === $user->id
            && $householdMember->role !== 'owner';
    }
}
