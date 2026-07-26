<?php

namespace App\Modules\Pets\Models;

use App\Models\User;
use App\Modules\Pets\Enums\PetSpecies;
use App\Modules\Pets\Events\PetCreated;
use App\Platform\Concerns\Shareable;
use App\Platform\Models\Household;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kućni ljubimac (DATA_MODEL.md §4d). Shareable — ljubimac može biti privatan
 * (npr. dječiji hrčak koji ne zanima ostatak domaćinstva) ili dijeljen.
 */
#[Fillable(['household_id', 'created_by', 'name', 'species', 'birth_date', 'notes'])]
class Pet extends Model
{
    use HasFactory;
    use Shareable;

    protected $table = 'pets_pets';

    protected function casts(): array
    {
        return [
            'species' => PetSpecies::class,
            'birth_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::created(fn (Pet $pet) => PetCreated::dispatch($pet));
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function careRecords(): HasMany
    {
        return $this->hasMany(CareRecord::class);
    }
}
