<?php

namespace App\Modules\Pets\Enums;

/**
 * Vrsta njege ljubimca (DATA_MODEL.md §4d). Svi tipovi dijele istu logiku
 * "datum → podsjetnik + kalendar"; tip služi prikazu i filtriranju.
 */
enum CareType: string
{
    case Vaccination = 'vaccination';
    case VetVisit = 'vet_visit';
    case Treatment = 'treatment';
    case Grooming = 'grooming';
    case Other = 'other';

    public function label(): string
    {
        return __('pets.care.types.'.$this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::Vaccination => 'heroicon-o-shield-check',
            self::VetVisit => 'heroicon-o-building-office-2',
            self::Treatment => 'heroicon-o-beaker',
            self::Grooming => 'heroicon-o-scissors',
            self::Other => 'heroicon-o-heart',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Vaccination => 'success',
            self::VetVisit => 'info',
            self::Treatment => 'warning',
            default => 'gray',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $t) => [$t->value => $t->label()])
            ->all();
    }
}
