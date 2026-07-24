<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Generička oznaka po domaćinstvu (DATA_MODEL.md §9). Ne pravi se ručno —
 * upravlja se kroz Taggable trait.
 */
#[Fillable(['household_id', 'name'])]
class Tag extends Model
{
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
