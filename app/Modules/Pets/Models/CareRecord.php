<?php

namespace App\Modules\Pets\Models;

use App\Models\User;
use App\Modules\Pets\Enums\CareType;
use App\Modules\Pets\Events\CareScheduled;
use App\Platform\Concerns\Shareable;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Njega ljubimca s datumom (DATA_MODEL.md §4d): vakcina, pregled, terapija…
 *
 * Na kreiranju emituje `CareScheduled` → modul-vlastiti listener dispatch-uje
 * platformski `ReminderRequested` (X dana ranije), pa podsjetnik i email nastaju
 * BEZ ijedne linije koda van ovog modula — isti mehanizam kao računi i dokumenti.
 */
#[Fillable([
    'household_id', 'created_by', 'pet_id', 'type', 'due_date',
    'remind_days_before', 'completed_at', 'notes',
])]
class CareRecord extends Model
{
    use HasFactory;
    use Shareable;

    protected $table = 'pets_care_records';

    protected function casts(): array
    {
        return [
            'type' => CareType::class,
            'due_date' => 'datetime',
            'completed_at' => 'datetime',
            'remind_days_before' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::created(fn (CareRecord $record) => CareScheduled::dispatch($record));
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /** Naslov za prikaz — "Vakcina · Luna". */
    public function displayTitle(): string
    {
        return __('pets.care.display_title', [
            'type' => $this->type->label(),
            'pet' => $this->pet?->name ?? '—',
        ]);
    }

    /** Kad podsjetnik treba okinuti (X dana prije termina). */
    public function reminderDate(): CarbonInterface
    {
        return $this->due_date->copy()->subDays($this->remind_days_before);
    }

    public function isDone(): bool
    {
        return $this->completed_at !== null;
    }
}
