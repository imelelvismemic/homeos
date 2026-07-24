<?php

namespace App\Modules\Tasks\Events;

use App\Modules\Tasks\Models\Task;
use Illuminate\Foundation\Events\Dispatchable;

class TaskAssigned
{
    use Dispatchable;

    public function __construct(public Task $task) {}
}
