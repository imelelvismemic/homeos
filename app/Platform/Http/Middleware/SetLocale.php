<?php

namespace App\Platform\Http\Middleware;

use App\Platform\Localization\Locales;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Postavlja jezik zahtjeva (ROADMAP Faza 9b).
 *
 * Redoslijed izvora je namjeran: prijavljeni korisnik nosi svoj izbor sa
 * sobom (users.locale, bilo koji uređaj), a gost ima samo sesiju — zato
 * prijava ne smije "pregaziti" korisnikov trajni izbor jezikom koji je na
 * login stranici bio postavljen slučajno.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $locale = Locales::sanitize(
            $user?->locale ?? $request->session()->get('locale')
        );

        App::setLocale($locale);

        // Carbon nosi svoje prijevode (npr. "prije 2 dana" u tabelama i
        // sanducetu obavještenja) — bez ovoga ostane na engleskom.
        Carbon::setLocale($locale);
        CarbonImmutable::setLocale($locale);

        return $next($request);
    }
}
