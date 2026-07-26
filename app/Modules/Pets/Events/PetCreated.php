<?php

namespace App\Modules\Pets\Events;

use App\Modules\Pets\Models\Pet;
use Illuminate\Foundation\Events\Dispatchable;

class PetCreated
{
    use Dispatchable;

    public function __construct(public Pet $pet) {}
}
