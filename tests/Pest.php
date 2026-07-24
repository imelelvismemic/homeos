<?php

use App\Models\User;
use App\Platform\Models\Household;
use App\Platform\Models\HouseholdMember;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

/**
 * Tabela za Tests\Fixtures\ShareableThing (Faza 1 testovi Shareable traita).
 * Prati DATA_MODEL.md §3: household_id + created_by + title.
 */
function createShareableThingsTable(): void
{
    Schema::create('shareable_things', function (Blueprint $table) {
        $table->id();
        $table->foreignId('household_id')->constrained()->cascadeOnDelete();
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->string('title');
        $table->timestamps();
    });
}

/**
 * Kreira domaćinstvo sa vlasnikom i (opciono) dodatnim članovima.
 * Vraća [Household, ownerMember, [memberModels...]].
 *
 * @return array{0: Household, 1: HouseholdMember, 2: array<int, HouseholdMember>}
 */
function makeHousehold(int $extraMembers = 0): array
{
    $owner = User::factory()->create();
    $household = Household::create(['name' => 'Test', 'owner_id' => $owner->id]);
    $ownerMember = $household->members()->create([
        'user_id' => $owner->id,
        'role' => 'owner',
        'joined_at' => now(),
    ]);

    $members = [];
    for ($i = 0; $i < $extraMembers; $i++) {
        $u = User::factory()->create();
        $members[] = $household->members()->create([
            'user_id' => $u->id,
            'role' => 'member',
            'joined_at' => now(),
        ]);
    }

    return [$household, $ownerMember, $members];
}
