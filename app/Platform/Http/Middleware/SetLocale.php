<?php

namespace App\Platform\Http\Middleware;

use App\Platform\Http\LocaleController;
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
 * Redoslijed izvora je namjeran:
 *  1. **prijavljeni korisnik** (`users.locale`) — nosi izbor sa sobom na svaki
 *     uređaj i u email obavještenja,
 *  2. **sesija** — izbor gosta u tekućem pregledu,
 *  3. **kolačić** — isti izbor nakon što odjava uništi sesiju. Bez ovog koraka
 *     je korisnik koji je radio na njemačkom pa se odjavio dobijao stranicu
 *     prijave na bosanskom (prijavljeno kao greška nakon Faze 9c).
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $locale = Locales::sanitize(
            $user?->locale
                ?? $request->session()->get('locale')
                ?? $request->cookie(LocaleController::COOKIE)
        );

        App::setLocale($locale);

        // Carbon nosi svoje prijevode (npr. "prije 2 dana" u tabelama i
        // sanducetu obavještenja) — bez ovoga ostane na engleskom.
        Carbon::setLocale($locale);
        CarbonImmutable::setLocale($locale);

        return $next($request);
    }
}
