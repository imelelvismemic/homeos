<?php

namespace App\Modules\Tasks\Models;

use App\Models\User;
use App\Platform\Models\Household;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kanban tabla (DATA_MODEL.md §4). Više tabli po domaćinstvu za različita
 * područja ("Kućni poslovi", "Renoviranje"...).
 */
#[Fillable(['household_id', 'created_by', 'name', 'position'])]
class Board extends Model
{
    use HasFactory;

    protected $table = 'tasks_boards';

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'board_id');
    }
}
