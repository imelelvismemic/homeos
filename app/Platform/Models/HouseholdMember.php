<?php

namespace App\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Pivot: user ↔ household (DATA_MODEL.md §1). Has its own `id` (not a
 * composite-key-only pivot), so it doubles as a directly queryable model.
 */
#[Fillable(['household_id', 'user_id', 'role', 'joined_at'])]
class HouseholdMember extends Pivot
{
    public $incrementing = true;

    protected $table = 'household_members';

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
