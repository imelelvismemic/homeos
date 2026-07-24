<?php

namespace Tests\Fixtures;

use App\Platform\Concerns\Shareable;
use Illuminate\Database\Eloquent\Model;

/**
 * Throwaway model za testiranje Shareable traita u Fazi 1 (nema još pravog
 * modul entiteta — Task dolazi u Fazi 3). Tabela se pravi u testu, ne u
 * pravim migracijama.
 */
class ShareableThing extends Model
{
    use Shareable;

    protected $table = 'shareable_things';

    protected $guarded = [];
}
