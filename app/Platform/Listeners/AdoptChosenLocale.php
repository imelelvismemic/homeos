<?php

namespace App\Platform\Listeners;

use App\Models\User;
use App\Platform\Localization\Locales;
use Illuminate\Auth\Events\Login;

/**
 * Jezik izabran na stranici prijave prelazi na nalog (ispravka nakon Faze 9c).
 *
 * Prije ovoga je vrijedilo samo `users.locale`, pa je korisnik koji na prijavi
 * prebaci na njemački nakon prijave ponovo dobijao stari jezik — izbor je izgledao
 * kao da se "nije zapamtio".
 *
 * Uslov je da je izbor bio EKSPLICITAN (`locale_chosen` postavlja
 * `LocaleController` na klik). Zato puko postojanje kolačića s prošlog rada na
 * zajedničkom računaru ne mijenja jezik tuđeg naloga — mijenja ga samo osoba koja
 * je upravo kliknula na zastavicu, a to je ista osoba koja se prijavljuje.
 */
class AdoptChosenLocale
{
    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        if (! session('locale_chosen')) {
            return;
        }

        session()->forget('locale_chosen');

        $chosen = Locales::sanitize(session('locale'));

        if ($chosen === $event->user->locale) {
            return;
        }

        $event->user->forceFill(['locale' => $chosen])->save();
    }
}
