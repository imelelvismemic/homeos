<?php

namespace App\Modules\Reminders\Events;

use App\Modules\Reminders\Models\Reminder;
use Illuminate\Foundation\Events\Dispatchable;

class ReminderFired
{
    use Dispatchable;

    public function __construct(public Reminder $reminder) {}
}
