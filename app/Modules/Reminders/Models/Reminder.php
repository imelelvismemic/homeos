<?php

namespace App\Modules\Reminders\Models;

use App\Models\User;
use App\Modules\Reminders\Events\ReminderCreated;
use App\Platform\Concerns\Shareable;
use App\Platform\Models\Household;
use App\Platform\Models\HouseholdMember;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Podsjetnik (DATA_MODEL.md §4a). Samostalan ILI (opciono) vezan polimorfno za
 * bilo koji entitet (remindable). Okida ga centralni scheduler na `due_date`
 * (isto ime po §3), koristi Shareable za privatnost i dijeljeni RecurrenceService
 * za ponavljanje.
 */
#[Fillable([
    'household_id', 'created_by', 'assigned_to', 'title', 'description', 'due_date',
    'completed_at', 'recurrence_rule', 'remindable_type', 'remindable_id',
])]
class Reminder extends Model
{
    use HasFactory;
    use Shareable;

    protected $table = 'reminders_reminders';

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(fn (Reminder $reminder) => ReminderCreated::dispatch($reminder));
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Odgovorna osoba (član domaćinstva) kojoj je podsjetnik namijenjen. */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(HouseholdMember::class, 'assigned_to');
    }

    /** Opciona veza na bilo koji entitet (Task, kasnije Bill). */
    public function remindable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isRecurring(): bool
    {
        return filled($this->recurrence_rule);
    }
}
