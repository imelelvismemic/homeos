<?php

namespace App\Modules\LifeAdmin\Models;

use App\Models\User;
use App\Modules\LifeAdmin\Enums\DocumentType;
use App\Modules\LifeAdmin\Events\DocumentCreated;
use App\Platform\Concerns\Shareable;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dokument / garancija / obnova (DATA_MODEL.md §4c). Na kreiranju emituje
 * DocumentCreated → Life admin listener dispatch-uje ReminderRequested (X dana
 * prije isteka), pa se podsjetnik i email dobiju bez koda van Life admina (isti
 * DoD mehanizam kao računi). Shareable (privatnost — skenovi ličnih dokumenata).
 */
#[Fillable([
    'household_id', 'created_by', 'type', 'title', 'expiry_date',
    'remind_days_before', 'file_path', 'file_name', 'notes',
])]
class Document extends Model
{
    use HasFactory;
    use Shareable;

    protected $table = 'life_admin_documents';

    protected function casts(): array
    {
        return [
            'type' => DocumentType::class,
            'expiry_date' => 'date',
            'remind_days_before' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::created(fn (Document $document) => DocumentCreated::dispatch($document));
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasFile(): bool
    {
        return filled($this->file_path);
    }

    /** Datum kad podsjetnik treba okinuti (X dana prije isteka). */
    public function reminderDate(): ?CarbonInterface
    {
        return $this->expiry_date?->copy()->subDays($this->remind_days_before);
    }
}
