<?php

namespace App\Platform\Support;

use App\Platform\Models\Household;
use Filament\Facades\Filament;

/**
 * Valuta domaćinstva (ROADMAP Faza 7c). Iznosi se NIGDJE ne ispisuju s
 * hardkodiranom valutom — format ide kroz ovaj helper, koji čita `households.currency`
 * (docs/PRAVILA.md §11).
 *
 * Prikaz je namjerno jednostavan i isti za sve valute — `1,234.56 €` — umjesto
 * Intl formata po lokalu ("BAM 700.00"), da tabele i widgeti ostanu poravnati i
 * čitljivi. Simbol ide iza iznosa jer je tako uobičajeno u BiH i regionu.
 */
class Currency
{
    public const DEFAULT = 'EUR';

    /**
     * Podržane valute: kod => simbol (ili kod, ako valuta nema kratak simbol).
     * Lista je namjerno kratka i praktična — svjetske valute koje domaćinstvo
     * realno može koristiti, ne cijeli ISO 4217 spisak.
     *
     * @var array<string, string>
     */
    public const CODES = [
        'EUR' => '€',
        'BAM' => 'KM',
        'RSD' => 'din',
        'HRK' => 'kn',
        'MKD' => 'ден',
        'ALL' => 'L',
        'CHF' => 'CHF',
        'GBP' => '£',
        'USD' => '$',
        'CAD' => 'CA$',
        'AUD' => 'A$',
        'SEK' => 'kr',
        'NOK' => 'kr',
        'DKK' => 'kr',
        'PLN' => 'zł',
        'CZK' => 'Kč',
        'HUF' => 'Ft',
        'RON' => 'lei',
        'BGN' => 'лв',
        'TRY' => '₺',
        'RUB' => '₽',
        'UAH' => '₴',
        'AED' => 'AED',
        'SAR' => 'SAR',
        'JPY' => '¥',
        'CNY' => '¥',
        'INR' => '₹',
        'ZAR' => 'R',
        'BRL' => 'R$',
    ];

    /** Ispravan kod (fallback na default ako je nepoznat/prazan). */
    public static function code(?string $code = null): string
    {
        $code = strtoupper((string) ($code ?: static::currentCode()));

        return isset(self::CODES[$code]) ? $code : self::DEFAULT;
    }

    public static function symbol(?string $code = null): string
    {
        return self::CODES[static::code($code)];
    }

    /** Iznos s valutom, npr. "1,234.56 €". */
    public static function format(float|string|null $amount, ?string $code = null): string
    {
        return number_format((float) $amount, 2).' '.static::symbol($code);
    }

    /**
     * Opcije za Filament Select — "EUR — €" (kod je ono što se pamti).
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::CODES)
            ->map(fn (string $symbol, string $code) => $symbol === $code ? $code : "{$code} — {$symbol}")
            ->all();
    }

    /** Valuta trenutno odabranog domaćinstva. */
    private static function currentCode(): string
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Household ? (string) $tenant->currency : self::DEFAULT;
    }
}
