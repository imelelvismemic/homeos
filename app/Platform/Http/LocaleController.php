<?php

namespace App\Platform\Http;

use App\Platform\Localization\Locales;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Promjena jezika iz prekidača u traci (i na prijavi) — ROADMAP Faza 9b.
 *
 * Ruta je javna jer prekidač stoji i na login stranici; jedini efekat je
 * izbor jezika, pa nema šta štititi osim raspona vrijednosti.
 */
class LocaleController
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        abort_unless(Locales::isSupported($locale), 404);

        $request->session()->put('locale', $locale);

        // Prijavljenom korisniku izbor ostaje i na drugom uređaju, i email
        // obavještenja idu na tom jeziku (User::preferredLocale).
        $request->user()?->forceFill(['locale' => $locale])->save();

        return back();
    }
}
