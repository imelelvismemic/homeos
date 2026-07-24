<?php

namespace App\Platform\Concerns;

use App\Models\User;
use App\Platform\Enums\Visibility;
use App\Platform\Events\Shared;
use App\Platform\Models\HouseholdMember;
use App\Platform\Models\Share;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Generički sharing/privatnost mehanizam (CLAUDE.md §11, DATA_MODEL.md §2).
 * Model koji ga koristi (`use Shareable;`) mora imati `household_id` i
 * `created_by` (DATA_MODEL.md §3). Migracija tog modela NE dodaje svoje
 * is_private/visibility kolone — sve ide kroz `shares` tabelu.
 *
 * Autorizacija (Policy klase, CLAUDE.md §11) interno zove isVisibleTo().
 */
trait Shareable
{
    /** Podrazumijevana vidljivost pri kreiranju — modul može pregaziti. */
    protected function defaultVisibility(): Visibility
    {
        return Visibility::Household;
    }

    public static function bootShareable(): void
    {
        // Objekat je po defaultu dijeljen sa cijelim domaćinstvom (ORIGINAL_SPEC:
        // "dijeljeno po defaultu tamo gdje ima smisla, privatno kad treba").
        static::created(function ($model) {
            $model->share()->create([
                'household_id' => $model->household_id,
                'owner_id' => $model->created_by ?? auth()->id(),
                'visibility' => $model->defaultVisibility(),
            ]);
        });

        // shares tabela nema FK na polimorfni objekat, pa čistimo ručno.
        static::deleting(function ($model) {
            $model->share()->delete();
        });
    }

    public function share(): MorphOne
    {
        return $this->morphOne(Share::class, 'shareable');
    }

    public function makePrivate(): void
    {
        $this->share->recipients()->delete();
        $this->share()->update(['visibility' => Visibility::Private]);
        $this->unsetRelation('share');
    }

    public function shareWithHousehold(): void
    {
        $this->share->recipients()->delete();
        $this->share()->update(['visibility' => Visibility::Household]);
        $this->unsetRelation('share');
    }

    /**
     * Dijeli objekat sa određenim članovima domaćinstva i emituje Shared event
     * (na koji platform šalje `shared_with_you` notifikaciju).
     *
     * @param  iterable<HouseholdMember|int>  $members  članovi ili njihovi id-evi
     */
    public function shareWith(iterable $members): void
    {
        $memberIds = collect($members)
            ->map(fn ($m) => $m instanceof HouseholdMember ? $m->getKey() : $m)
            ->all();

        $share = $this->share;
        $share->update(['visibility' => Visibility::Specific]);

        $existing = $share->recipients()->pluck('household_member_id')->all();
        $new = array_values(array_diff($memberIds, $existing));

        foreach ($new as $id) {
            $share->recipients()->create(['household_member_id' => $id]);
        }

        $this->unsetRelation('share');

        if ($new !== []) {
            $recipients = HouseholdMember::whereIn('id', $new)->get();
            Shared::dispatch($this, $recipients);
        }
    }

    public function isVisibleTo(User $user): bool
    {
        $share = $this->share;

        if ($share === null) {
            return false;
        }

        if ($share->owner_id === $user->id) {
            return true;
        }

        return match ($share->visibility) {
            Visibility::Private => false,
            Visibility::Household => $user->households()->whereKey($share->household_id)->exists(),
            Visibility::Specific => $share->recipients()
                ->whereHas('householdMember', fn ($q) => $q->where('user_id', $user->id))
                ->exists(),
        };
    }

    /**
     * Ograničava upit na objekte vidljive datom korisniku. Household-scoping
     * (koje domaćinstvo) i dalje radi Filament tenancy — ovo filtrira vidljivost
     * unutar domaćinstva (privatno tuđe se ne vidi).
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->whereHas('share', function (Builder $q) use ($user) {
            $q->where('visibility', Visibility::Household->value)
                ->orWhere('owner_id', $user->id)
                ->orWhere(function (Builder $q) use ($user) {
                    $q->where('visibility', Visibility::Specific->value)
                        ->whereHas('recipients', function (Builder $q) use ($user) {
                            $q->whereHas('householdMember', fn (Builder $q) => $q->where('user_id', $user->id));
                        });
                });
        });
    }
}
