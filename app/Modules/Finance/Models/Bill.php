<?php

namespace App\Modules\Finance\Models;

use App\Models\User;
use App\Modules\Finance\Events\BillCreated;
use App\Modules\Finance\Events\BillPaid;
use App\Platform\Concerns\Shareable;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Račun / pretplata (DATA_MODEL.md §4b). Na kreiranju emituje BillCreated →
 * Finance listener dispatch-uje ReminderRequested (X dana prije dospijeća), pa se
 * podsjetnik i email dobiju bez koda van Finansija (DoD Faze 5). Ponavljajući
 * račun spawn-a sljedeću instancu kad se plati. Shareable (privatnost).
 */
#[Fillable([
    'household_id', 'created_by', 'category_id', 'title', 'amount',
    'due_date', 'recurrence_rule', 'remind_days_before', 'paid_at',
])]
class Bill extends Model
{
    use HasFactory;
    use Shareable;

    protected $table = 'finance_bills';

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
            'remind_days_before' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::created(fn (Bill $bill) => BillCreated::dispatch($bill));

        static::updated(function (Bill $bill) {
            if ($bill->wasChanged('paid_at') && $bill->paid_at !== null) {
                BillPaid::dispatch($bill);
            }
        });
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

    public function isRecurring(): bool
    {
        return filled($this->recurrence_rule);
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    /** Datum kad podsjetnik treba okinuti (X dana prije dospijeća). */
    public function reminderDate(): CarbonInterface
    {
        return $this->due_date->copy()->subDays($this->remind_days_before);
    }
}
