<?php

namespace App\Modules\Reminders\Listeners;

use App\Modules\Reminders\Events\ReminderFired;
use App\Modules\Reminders\Notifications\ReminderDue;
use App\Platform\Models\HouseholdMember;

/**
 * Auto-discoveran listener: kad podsjetnik okine, obavijesti člana koji ga je
 * kreirao (in-app uvijek, email po preferenci — kroz HouseholdNotification).
 */
class NotifyReminderRecipient
{
    public function handle(ReminderFired $event): void
    {
        $reminder = $event->reminder;

        $member = HouseholdMember::query()
            ->where('household_id', $reminder->household_id)
            ->where('user_id', $reminder->created_by)
            ->first();

        $member?->notify(new ReminderDue($reminder));
    }
}
