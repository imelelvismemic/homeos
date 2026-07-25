<?php

namespace App\Platform\Services;

use App\Platform\Models\Household;
use App\Platform\Models\HouseholdMember;
use RuntimeException;

/**
 * Administrativne radnje nad članovima domaćinstva (Faza 6). Logika je ovdje, ne
 * u Filament stranicama (CLAUDE.md §15). Guard-i čuvaju invariante: domaćinstvo
 * uvijek ima bar jednog vlasnika; član ne uklanja sam sebe kroz "ukloni".
 */
class HouseholdMemberService
{
    public function changeRole(HouseholdMember $member, string $role): void
    {
        if ($member->role === 'owner' && $role !== 'owner' && $this->ownerCount($member->household_id) <= 1) {
            throw new RuntimeException(__('platform.members.error_last_owner'));
        }

        $member->update(['role' => $role]);
    }

    public function remove(HouseholdMember $member): void
    {
        if ($member->role === 'owner' && $this->ownerCount($member->household_id) <= 1) {
            throw new RuntimeException(__('platform.members.error_last_owner'));
        }

        $member->delete();
    }

    public function transferOwnership(HouseholdMember $newOwner, HouseholdMember $currentOwner): void
    {
        $newOwner->update(['role' => 'owner']);
        $currentOwner->update(['role' => 'member']);

        // Household.owner_id prati stvarnog vlasnika (koristi se za "ko je kreirao").
        Household::whereKey($newOwner->household_id)->update(['owner_id' => $newOwner->user_id]);
    }

    private function ownerCount(int $householdId): int
    {
        return HouseholdMember::query()
            ->where('household_id', $householdId)
            ->where('role', 'owner')
            ->count();
    }
}
