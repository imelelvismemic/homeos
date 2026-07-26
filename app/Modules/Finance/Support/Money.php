<?php

namespace App\Modules\Finance\Support;

use App\Platform\Support\Currency;

/**
 * Jedinstven prikaz iznosa u Finansijama. Valuta NIJE hardkodirana — dolazi iz
 * postavki domaćinstva (App\Platform\Support\Currency, docs/RULES.md §11).
 * Metoda je namjerno ostala tanka: modul ne pravi svoju logiku formatiranja.
 */
class Money
{
    public static function format(float|string|null $amount): string
    {
        return Currency::format($amount);
    }
}
