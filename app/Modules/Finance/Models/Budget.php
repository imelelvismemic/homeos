<?php

namespace App\Modules\Finance\Models;

use App\Platform\Models\Household;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Budžet po kategoriji za mjesec (DATA_MODEL.md §4b). Mjesečni pregled poredi
 * potrošeno (transakcije) s ovim iznosom.
 */
#[Fillable(['household_id', 'created_by', 'category_id', 'month', 'amount'])]
class Budget extends Model
{
    use HasFactory;

    protected $table = 'finance_budgets';

    protected function casts(): array
    {
        return [
            'month' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
