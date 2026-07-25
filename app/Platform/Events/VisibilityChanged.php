<?php

namespace App\Platform\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Vidljivost Shareable objekta je promijenjena (CLAUDE.md §9/§11). Moduli koji drže
 * IZVEDENE zapise vezane za taj objekat (npr. podsjetnik/bilješka za račun) slušaju
 * ovaj event i usklađuju vidljivost svojih zapisa — bez da Platform zna za te module.
 */
class VisibilityChanged
{
    use Dispatchable;

    public function __construct(public Model $shareable) {}
}
