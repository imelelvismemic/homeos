<?php

namespace App\Modules\Finance\Policies;

use App\Models\User;
use App\Modules\Finance\Models\Bill;

class BillPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Bill $bill): bool
    {
        return $bill->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Bill $bill): bool
    {
        return $bill->isVisibleTo($user);
    }

    public function delete(User $user, Bill $bill): bool
    {
        return $bill->isVisibleTo($user);
    }
}
