<?php

namespace App\Platform\Localization;

/**
 * Jedini izvor istine o podržanim jezicima (ROADMAP Faza 9b).
 *
 * Nazivi jezika su NAMJERNO na samom jeziku (endonimi) i ne prevode se — tako
 * osoba koja je slučajno završila na jeziku koji ne razumije i dalje prepozna
 * svoj u listi. Dodavanje četvrtog jezika = jedan unos ovdje + `lang/<kod>/`.
 */
final class Locales
{
    public const DEFAULT = 'bs';

    /** @var array<string, string> kod => naziv jezika na tom jeziku */
    public const SUPPORTED = [
        'bs' => 'Bosanski',
        'en' => 'English',
        'de' => 'Deutsch',
    ];

    /** @return array<int, string> */
    public static function codes(): array
    {
        return array_keys(self::SUPPORTED);
    }

    public static function isSupported(?string $locale): bool
    {
        return $locale !== null && array_key_exists($locale, self::SUPPORTED);
    }

    /** Naziv jezika za prikaz; nepoznat kod ne ruši UI. */
    public static function label(?string $locale): string
    {
        return self::SUPPORTED[$locale] ?? self::SUPPORTED[self::DEFAULT];
    }

    /** Kod koji se smije poslati u App::setLocale(). */
    public static function sanitize(?string $locale): string
    {
        return self::isSupported($locale) ? $locale : self::DEFAULT;
    }
}
