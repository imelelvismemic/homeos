<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Član domaćinstva sa kojim je objekat dijeljen kad je visibility = specific
 * (DATA_MODEL.md §2).
 */
#[Fillable(['share_id', 'household_member_id'])]
class ShareRecipient extends Model
{
    public $timestamps = false;

    public function share(): BelongsTo
    {
        return $this->belongsTo(Share::class);
    }

    public function householdMember(): BelongsTo
    {
        return $this->belongsTo(HouseholdMember::class);
    }
}
