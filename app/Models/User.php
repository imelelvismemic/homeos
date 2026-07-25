<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Platform\Models\Household;
use App\Platform\Models\HouseholdMember;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'password', 'avatar_path', 'timezone', 'locale', 'current_household_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasAvatar, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function ownedHouseholds(): HasMany
    {
        return $this->hasMany(Household::class, 'owner_id');
    }

    public function households(): BelongsToMany
    {
        return $this->belongsToMany(Household::class, 'household_members')
            ->using(HouseholdMember::class)
            ->withPivot(['role', 'joined_at'])
            ->withTimestamps();
    }

    public function currentHousehold(): BelongsTo
    {
        return $this->belongsTo(Household::class, 'current_household_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    /**
     * Profilna slika za Filament (korisnički meni, avatar u panelu). Fajl je na
     * privatnom disku, pa vraćamo autentikovanu rutu, ne direktan URL; `v` je
     * cache-buster da se nova slika vidi odmah nakon zamjene.
     */
    public function getFilamentAvatarUrl(): ?string
    {
        if (blank($this->avatar_path)) {
            return null;
        }

        return route('filament.app.avatar', [
            'user' => $this->getKey(),
            'v' => $this->updated_at?->timestamp,
        ]);
    }

    public function getTenants(Panel $panel): Collection
    {
        return $this->households;
    }

    public function canAccessTenant(Model $tenant): bool
    {
        return $this->households()->whereKey($tenant->getKey())->exists();
    }
}
