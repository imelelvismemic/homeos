<?php

namespace App\Modules\LifeAdmin\Events;

use App\Modules\LifeAdmin\Models\Document;
use Illuminate\Foundation\Events\Dispatchable;

class DocumentCreated
{
    use Dispatchable;

    public function __construct(public Document $document) {}
}
