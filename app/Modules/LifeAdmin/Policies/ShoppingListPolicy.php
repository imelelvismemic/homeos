<?php

namespace App\Modules\LifeAdmin\Policies;

use App\Models\User;
use App\Modules\LifeAdmin\Models\ShoppingList;

class ShoppingListPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ShoppingList $list): bool
    {
        return $list->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ShoppingList $list): bool
    {
        return $list->isVisibleTo($user);
    }

    public function delete(User $user, ShoppingList $list): bool
    {
        return $list->isVisibleTo($user);
    }
}
