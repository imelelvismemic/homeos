<?php

namespace App\Modules\LifeAdmin\Enums;

/**
 * Vrsta zapisa u evidenciji (DATA_MODEL.md §4c). Svi tipovi dijele istu logiku
 * isteka → podsjetnik; tip služi grupisanju/filtriranju i ikoni.
 */
enum DocumentType: string
{
    case IdDocument = 'id_document';
    case Warranty = 'warranty';
    case Renewal = 'renewal';
    case Contract = 'contract';
    case Other = 'other';

    /** Prijevod za prikaz (labela u UI). */
    public function label(): string
    {
        return __('lifeadmin.documents.types.'.$this->value);
    }

    public function icon(): string
    {
        return match ($this) {
            self::IdDocument => 'heroicon-o-identification',
            self::Warranty => 'heroicon-o-shield-check',
            self::Renewal => 'heroicon-o-arrow-path',
            self::Contract => 'heroicon-o-document-text',
            self::Other => 'heroicon-o-folder',
        };
    }

    /** Opcije za Filament Select (value => label). */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $t) => [$t->value => $t->label()])
            ->all();
    }
}
