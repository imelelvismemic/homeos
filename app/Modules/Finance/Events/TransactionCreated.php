<?php

namespace App\Modules\Finance\Events;

use App\Modules\Finance\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;

class TransactionCreated
{
    use Dispatchable;

    public function __construct(public Transaction $transaction) {}
}
