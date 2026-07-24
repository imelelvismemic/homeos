<?php

namespace App\Platform\Models;

use App\Models\User;
use App\Platform\Enums\Visibility;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Jedan zapis dijeljenja po objektu (vidi Shareable trait). Ne pravi se ručno —
 * upravlja se kroz trait (shareWithHousehold/shareWith/makePrivate).
 */
#[Fillable(['shareable_type', 'shareable_id', 'household_id', 'visibility', 'owner_id'])]
class Share extends Model
{
    protected function casts(): array
    {
        return [
            'visibility' => Visibility::class,
        ];
    }

    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(ShareRecipient::class);
    }
}
