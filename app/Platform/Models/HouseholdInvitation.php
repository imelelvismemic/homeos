<?php

namespace App\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pozivnica u domaćinstvo za osobu koja još nema nalog (DATA_MODEL.md §1).
 * U bazi stoji samo HASH tokena — sam token postoji jedino u linku koji je
 * poslan emailom.
 */
#[Fillable(['household_id', 'invited_by', 'email', 'role', 'token', 'expires_at', 'accepted_at'])]
class HouseholdInvitation extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /** Pozivnice koje se još mogu iskoristiti. */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('accepted_at')->where('expires_at', '>', now());
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isFuture();
    }
}
