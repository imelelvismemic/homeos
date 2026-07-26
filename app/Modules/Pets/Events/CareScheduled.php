<?php

namespace App\Modules\Pets\Events;

use App\Modules\Pets\Models\CareRecord;
use Illuminate\Foundation\Events\Dispatchable;

class CareScheduled
{
    use Dispatchable;

    public function __construct(public CareRecord $careRecord) {}
}
