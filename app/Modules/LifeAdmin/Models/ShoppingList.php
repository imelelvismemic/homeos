<?php

namespace App\Modules\LifeAdmin\Models;

use App\Models\User;
use App\Platform\Concerns\Shareable;
use App\Platform\Models\Household;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Zajednička lista za kupovinu (DATA_MODEL.md §4c). Dijeljena domaćinstvu po
 * defaultu (Shareable). Kućanski poslovi se NE modeliraju ovdje — idu kroz modul
 * Zadaci (odluka vlasnika).
 */
#[Fillable([
    'household_id', 'created_by', 'name',
])]
class ShoppingList extends Model
{
    use HasFactory;
    use Shareable;

    protected $table = 'life_admin_lists';

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ShoppingItem::class, 'list_id');
    }
}
