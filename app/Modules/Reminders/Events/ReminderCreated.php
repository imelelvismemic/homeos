<?php

namespace App\Modules\Reminders\Events;

use App\Modules\Reminders\Models\Reminder;
use Illuminate\Foundation\Events\Dispatchable;

class ReminderCreated
{
    use Dispatchable;

    public function __construct(public Reminder $reminder) {}
}
