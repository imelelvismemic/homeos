<?php

namespace App\Modules\LifeAdmin\Models;

use App\Models\User;
use App\Platform\Concerns\Shareable;
use App\Platform\Models\Household;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Važan kontakt domaćinstva (DATA_MODEL.md §4c) — npr. vodoinstalater, ljekar,
 * komšija. Bez datuma isteka. Shareable (može biti privatan ili dijeljen).
 */
#[Fillable([
    'household_id', 'created_by', 'name', 'relationship', 'phone', 'email', 'notes',
])]
class Contact extends Model
{
    use HasFactory;
    use Shareable;

    protected $table = 'life_admin_contacts';

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
