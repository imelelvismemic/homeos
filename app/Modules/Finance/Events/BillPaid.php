<?php

namespace App\Modules\Finance\Events;

use App\Modules\Finance\Models\Bill;
use Illuminate\Foundation\Events\Dispatchable;

class BillPaid
{
    use Dispatchable;

    public function __construct(public Bill $bill) {}
}
