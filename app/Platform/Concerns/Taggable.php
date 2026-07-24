<?php

namespace App\Platform\Concerns;

use App\Platform\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Generičke oznake (DATA_MODEL.md §9, CLAUDE.md princip "ne duplirati").
 * Model koji ga koristi (`use Taggable;`) mora imati `household_id` — oznake su
 * po domaćinstvu i dijele se između svih taggable objekata tog domaćinstva.
 */
trait Taggable
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * @param  array<int, string>|string  $names
     */
    public function tag(array|string $names): void
    {
        $this->tags()->syncWithoutDetaching($this->resolveTagIds($names));
    }

    /**
     * @param  array<int, string>|string  $names
     */
    public function untag(array|string $names): void
    {
        $ids = Tag::query()
            ->where('household_id', $this->household_id)
            ->whereIn('name', $this->normalizeNames($names))
            ->pluck('id');

        $this->tags()->detach($ids);
    }

    /**
     * Postavlja tačno dati skup oznaka (uklanja ostale).
     *
     * @param  array<int, string>  $names
     */
    public function syncTags(array $names): void
    {
        $this->tags()->sync($this->resolveTagIds($names));
    }

    /**
     * @return array<int, string>
     */
    public function tagNames(): array
    {
        return $this->tags->pluck('name')->all();
    }

    /**
     * @param  array<int, string>|string  $names
     */
    public function scopeTagged(Builder $query, array|string $names): Builder
    {
        $names = $this->normalizeNames($names);

        return $query->whereHas('tags', fn (Builder $q) => $q->whereIn('name', $names));
    }

    /**
     * @param  array<int, string>|string  $names
     * @return array<int, int>
     */
    protected function resolveTagIds(array|string $names): array
    {
        return collect($this->normalizeNames($names))
            ->map(fn (string $name) => Tag::firstOrCreate([
                'household_id' => $this->household_id,
                'name' => $name,
            ])->id)
            ->all();
    }

    /**
     * @param  array<int, string>|string  $names
     * @return array<int, string>
     */
    protected function normalizeNames(array|string $names): array
    {
        return collect(is_array($names) ? $names : [$names])
            ->map(fn ($n) => trim((string) $n))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
