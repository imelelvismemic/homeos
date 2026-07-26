<?php

namespace App\Modules\Pets\Listeners;

use App\Modules\Pets\Events\CareScheduled;
use App\Platform\Events\ReminderRequested;

/**
 * Dokaz proširivosti (ROADMAP Faza 7b): zakazana njega TRAŽI podsjetnik preko
 * platformskog eventa (X dana ranije). Podsjetnici ga kreiraju, scheduler okine,
 * član dobije obavještenje i email — a modul Ljubimci ne importuje nijednu klasu
 * iz Podsjetnika, niti je u Podsjetnicima mijenjana ijedna linija.
 */
class RequestCareReminder
{
    public function handle(CareScheduled $event): void
    {
        $care = $event->careRecord;

        ReminderRequested::dispatch(
            $care,
            $care->reminderDate(),
            __('pets.care.reminder', [
                'type' => $care->type->label(),
                'pet' => $care->pet?->name ?? '—',
            ]),
        );
    }
}
