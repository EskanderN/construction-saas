<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;

class TaskPolicy
{
    public function update(User $user, Task $task): bool
    {
        return $user->company_id === $task->company_id && (
            $user->id === $task->assigned_to ||
            $user->id === $task->created_by ||
            $user->canManageProjects()
        );
    }
}