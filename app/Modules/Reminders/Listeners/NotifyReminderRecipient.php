<?php

namespace App\Modules\Reminders\Listeners;

use App\Modules\Reminders\Events\ReminderFired;
use App\Modules\Reminders\Notifications\ReminderDue;
use App\Platform\Models\HouseholdMember;
use Illuminate\Support\Facades\Log;

/**
 * Auto-discoveran listener: kad podsjetnik okine, obavijesti člana koji ga je
 * kreirao (in-app uvijek, email po preferenci — kroz HouseholdNotification).
 */
class NotifyReminderRecipient
{
    public function handle(ReminderFired $event): void
    {
        $reminder = $event->reminder;

        // Namijenjen članu (spec) → njemu; inače kreatoru podsjetnika.
        $member = $reminder->assignee ?? HouseholdMember::query()
            ->where('household_id', $reminder->household_id)
            ->where('user_id', $reminder->created_by)
            ->first();

        if ($member === null) {
            // Tiho ništa je najgori ishod: podsjetnik okine, a niko ne dobije
            // obavijest. Dešava se ako autor više nije član tog domaćinstva.
            Log::warning('Podsjetnik nema kome poslati obavijest', [
                'reminder_id' => $reminder->getKey(),
                'household_id' => $reminder->household_id,
                'created_by' => $reminder->created_by,
            ]);

            return;
        }

        $member->notify(new ReminderDue($reminder));
    }
}
