<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Podešavanje po članu domaćinstva za jednu kategoriju obavještenja
 * (DATA_MODEL.md §1/§5).
 */
#[Fillable(['household_member_id', 'category', 'email_enabled', 'digest_enabled'])]
class NotificationPreference extends Model
{
    protected function casts(): array
    {
        return [
            'email_enabled' => 'boolean',
            'digest_enabled' => 'boolean',
        ];
    }

    public function householdMember(): BelongsTo
    {
        return $this->belongsTo(HouseholdMember::class);
    }
}
