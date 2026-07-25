<?php

namespace App\Modules\Reminders\Digest;

use App\Models\User;
use App\Modules\Reminders\Models\Reminder;
use App\Platform\Contracts\DigestSourceContract;
use App\Platform\Digest\DigestSection;
use App\Platform\Models\Household;
use Carbon\CarbonInterface;

/**
 * Doprinos Podsjetnika digestu (Faza 6): aktivni podsjetnici koji se okidaju u
 * periodu, vidljivi članu.
 */
class ReminderDigestSource implements DigestSourceContract
{
    public function digestSection(Household $household, User $user, CarbonInterface $from, CarbonInterface $to): ?DigestSection
    {
        $reminders = Reminder::query()
            ->where('household_id', $household->id)
            ->visibleTo($user)
            ->whereNull('completed_at')
            ->whereBetween('due_date', [$from, $to])
            ->orderBy('due_date')
            ->get();

        if ($reminders->isEmpty()) {
            return null;
        }

        return new DigestSection(
            __('reminders.plural_label'),
            $reminders->map(fn (Reminder $r) => '• '.$r->title.' — '.$r->due_date->translatedFormat('d.m. H:i'))->all(),
        );
    }
}
