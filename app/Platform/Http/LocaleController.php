<?php

namespace App\Platform\Http;

use App\Platform\Localization\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

/**
 * Promjena jezika iz prekidača u traci (i na prijavi) — ROADMAP Faza 9b.
 *
 * Ruta je javna jer prekidač stoji i na login stranici; jedini efekat je
 * izbor jezika, pa nema šta štititi osim raspona vrijednosti.
 *
 * Izbor se pamti na TRI mjesta, jer sva tri rješavaju različit slučaj:
 *  - sesija — trenutni pregled, i za gosta i za prijavljenog,
 *  - kolačić (godina) — **preživi odjavu**, koja uništi sesiju. Bez njega je
 *    korisnik koji je radio na njemačkom pa se odjavio dobijao login stranicu
 *    na bosanskom,
 *  - `users.locale` — prati korisnika na drugi uređaj i određuje jezik emailova.
 *
 * `locale_chosen` u sesiji bilježi da je izbor bio EKSPLICITAN (klik), pa ga
 * prijava može prenijeti na nalog (`AdoptChosenLocale`). Bez te razlike bi puko
 * postojanje kolačića na zajedničkom računaru mijenjalo jezik tuđeg naloga.
 */
class LocaleController
{
    /** Koliko dugo kolačić pamti izbor (minuta) — godina. */
    public const COOKIE = 'homeos_locale';

    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(Locales::isSupported($locale), 404);

        $request->session()->put('locale', $locale);
        $request->session()->put('locale_chosen', true);

        $request->user()?->forceFill(['locale' => $locale])->save();

        Cookie::queue(self::COOKIE, $locale, 60 * 24 * 365);

        return back();
    }
}
