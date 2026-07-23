<?php

namespace App\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'owner_id'])]
class Household extends Model
{
    use HasFactory;

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(HouseholdMember::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'household_members')
            ->using(HouseholdMember::class)
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }
}
