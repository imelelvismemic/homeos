<?php

namespace App\Platform\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Odstupanje od podrazumijevane uključenosti modula, po domaćinstvu
 * (DATA_MODEL.md §11). Nema reda = vrijedi default iz config/homeos-apps.php.
 */
#[Fillable(['household_id', 'module_key', 'enabled'])]
class HouseholdModule extends Model
{
    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }
}
