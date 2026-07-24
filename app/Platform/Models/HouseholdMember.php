<?php

namespace App\Platform\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Notifications\Notifiable;

/**
 * Pivot: user ↔ household (DATA_MODEL.md §1). Has its own `id` (not a
 * composite-key-only pivot), so it doubles as a directly queryable model.
 *
 * Notifiable je NAMJERNO član (ne User): in-app obavještenja su per
 * domaćinstvo, a email preferencije po kategoriji vežu se za člana
 * (CLAUDE.md §10, DATA_MODEL.md §1). Email se rutira na korisnikov email.
 */
#[Fillable(['household_id', 'user_id', 'role', 'joined_at'])]
class HouseholdMember extends Pivot
{
    use Notifiable;

    public $incrementing = true;

    protected $table = 'household_members';

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notificationPreferences(): HasMany
    {
        // FK je eksplicitan: HouseholdMember nasljeđuje Pivot, čiji getForeignKey()
        // vraća pivot ključ (prazan u samostalnoj upotrebi), pa auto-inferencija
        // hasMany-ja ne bi radila.
        return $this->hasMany(NotificationPreference::class, 'household_member_id');
    }

    /** Email kanal ide na korisnikovu adresu. */
    public function routeNotificationForMail(): string
    {
        return $this->user->email;
    }
}
