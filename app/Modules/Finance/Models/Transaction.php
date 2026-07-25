<?php

namespace App\Modules\Finance\Models;

use App\Models\User;
use App\Modules\Finance\Enums\TransactionType;
use App\Modules\Finance\Events\TransactionCreated;
use App\Platform\Concerns\Shareable;
use App\Platform\Models\Household;
use App\Platform\Models\HouseholdMember;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Transakcija (DATA_MODEL.md §4b): prihod ili rashod. Rashod ima platioca
 * (`paid_by`) i (opciono) učesnike među kojima se dijeli jednako — osnov za
 * "ko duguje kome" (BalanceService). Shareable (privatnost).
 */
#[Fillable([
    'household_id', 'created_by', 'category_id', 'type', 'title', 'amount', 'date', 'paid_by',
])]
class Transaction extends Model
{
    use HasFactory;
    use Shareable;

    protected $table = 'finance_transactions';

    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::created(fn (Transaction $transaction) => TransactionCreated::dispatch($transaction));
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** Ko je platio. */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(HouseholdMember::class, 'paid_by');
    }

    /** Članovi među kojima se trošak dijeli jednako. */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(
            HouseholdMember::class,
            'finance_transaction_participants',
            'transaction_id',
            'household_member_id',
        );
    }
}
