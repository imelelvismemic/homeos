<?php

namespace App\Platform\Events;

use App\Platform\Models\HouseholdMember;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Generički platform event: objekat je podijeljen sa određenim članovima
 * domaćinstva (CLAUDE.md §9). Emituje ga Shareable::shareWith(). Platform
 * listener (SendSharedNotification) na osnovu ovoga šalje `shared_with_you`
 * notifikaciju — bilo koji modul dobija to ponašanje besplatno, bez svog koda.
 *
 * Event nosi samo model + primaoce; listener sam dohvata ostatak.
 */
class Shared
{
    use Dispatchable;

    /**
     * @param  Collection<int, HouseholdMember>  $recipients
     *                                                        novi članovi sa kojima je objekat upravo podijeljen
     */
    public function __construct(
        public Model $shareable,
        public Collection $recipients,
    ) {}
}
