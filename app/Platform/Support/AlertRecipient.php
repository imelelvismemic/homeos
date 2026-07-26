<?php

namespace App\Platform\Support;

use App\Platform\Models\Household;

/**
 * Kome idu TEHNIČKA upozorenja (neuspio backup i slično). To nije korisničko
 * obavještenje, pa ne prolazi kroz postavke po članu (CLAUDE.md §10 se odnosi na
 * korisnička obavještenja).
 *
 * Prvo `HOMEOS_ALERT_EMAIL`; ako nije postavljen, vlasnik prvog domaćinstva —
 * da poruka ipak nekome stigne umjesto da se izgubi.
 */
class AlertRecipient
{
    public static function email(): string
    {
        $configured = config('homeos.alert_email');

        if (filled($configured)) {
            return (string) $configured;
        }

        $owner = Household::query()->with('owner')->orderBy('id')->first()?->owner;

        return $owner?->email ?? (string) config('mail.from.address');
    }
}
