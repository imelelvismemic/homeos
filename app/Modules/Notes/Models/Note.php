<?php

namespace App\Modules\Notes\Models;

use App\Models\User;
use App\Modules\Notes\Events\NoteCreated;
use App\Platform\Concerns\Shareable;
use App\Platform\Concerns\Taggable;
use App\Platform\Models\Household;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

/**
 * Bilješka (DATA_MODEL.md §4a). Koristi Shareable (privatnost) i platform tagove
 * (Taggable). Opcioni `journal_date` čini je dnevničkim unosom; opciona polimorfna
 * veza `notable` je veže za bilo koji entitet. Tijelo je HTML (Filament RichEditor).
 */
#[Fillable([
    'household_id', 'created_by', 'title', 'body', 'journal_date',
    'notable_type', 'notable_id',
])]
class Note extends Model
{
    use HasFactory;
    use Shareable;
    use Taggable;

    protected $table = 'notes_notes';

    protected function casts(): array
    {
        return [
            'journal_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::created(fn (Note $note) => NoteCreated::dispatch($note));
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Opciona veza na bilo koji entitet (Task, …). */
    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    /** Naslov za prikaz — ako nema title-a, izvod iz tijela (bez HTML-a). */
    public function displayTitle(): string
    {
        if (filled($this->title)) {
            return $this->title;
        }

        return Str::limit(trim(strip_tags($this->body)), 60) ?: __('notes.untitled');
    }
}
