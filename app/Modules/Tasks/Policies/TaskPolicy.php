<?php

namespace App\Modules\Tasks\Policies;

use App\Models\User;
use App\Modules\Tasks\Models\Task;

/**
 * Autorizacija ide kroz Policy koja interno zove Shareable mehanizam
 * (CLAUDE.md §11) — nikad ručne if provjere po Resource-ima. Domaćinstvo je
 * kolaborativno: ko vidi zadatak, može ga i mijenjati (brief: "izmjene koje
 * napravi jedan član vidljive su svima"). Privatni zadatak vidi/mijenja samo
 * vlasnik (to Shareable::isVisibleTo već rješava).
 */
class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // lista je dodatno ograničena tenancy-jem + visibleTo scope-om
    }

    public function view(User $user, Task $task): bool
    {
        return $task->isVisibleTo($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Task $task): bool
    {
        return $task->isVisibleTo($user);
    }

    public function delete(User $user, Task $task): bool
    {
        return $task->isVisibleTo($user);
    }
}
