<?php

namespace App\Modules\Finance\Models;

use App\Models\User;
use App\Platform\Models\Household;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Finansijska kategorija (DATA_MODEL.md §4b). Household-level (nije privatna) —
 * dijele je transakcije, računi i budžeti.
 */
#[Fillable(['household_id', 'created_by', 'name', 'color'])]
class Category extends Model
{
    use HasFactory;

    protected $table = 'finance_categories';

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
