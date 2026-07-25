<?php

namespace App\Modules\LifeAdmin\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Stavka liste za kupovinu (DATA_MODEL.md §4c) — štiklira se kad se kupi. Nije
 * zasebno Shareable: vidljivost nasljeđuje od liste (ShoppingList).
 */
#[Fillable([
    'list_id', 'name', 'is_done',
])]
class ShoppingItem extends Model
{
    use HasFactory;

    protected $table = 'life_admin_list_items';

    /** Nova stavka je po defaultu "za kupiti" (nije štiklirana). */
    protected $attributes = [
        'is_done' => false,
    ];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
        ];
    }

    public function list(): BelongsTo
    {
        return $this->belongsTo(ShoppingList::class, 'list_id');
    }
}
