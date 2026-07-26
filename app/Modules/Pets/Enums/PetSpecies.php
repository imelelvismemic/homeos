<?php

namespace App\Modules\Pets\Enums;

/**
 * Vrsta ljubimca (DATA_MODEL.md §4d) — služi ikoni i grupisanju, ne mijenja logiku.
 */
enum PetSpecies: string
{
    case Dog = 'dog';
    case Cat = 'cat';
    case Bird = 'bird';
    case Fish = 'fish';
    case Other = 'other';

    public function label(): string
    {
        return __('pets.species.'.$this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::Bird => 'heroicon-o-sparkles',
            self::Fish => 'heroicon-o-beaker',
            default => 'heroicon-o-heart',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
