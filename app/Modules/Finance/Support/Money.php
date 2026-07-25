<?php

namespace App\Modules\Finance\Support;

/**
 * Jedinstveni prikaz iznosa u KM (npr. "700.00 KM", "1,234.56 KM") — koristi se
 * u svim Finance kolonama, widgetima, kalendaru i pregledu, da format bude
 * dosljedan (umjesto Intl "BAM 700.00").
 */
class Money
{
    public static function km(float|string|null $amount): string
    {
        return number_format((float) $amount, 2).' KM';
    }
}
