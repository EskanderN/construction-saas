<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectStatusLog;
use App\Models\User;
use App\Enums\ProjectStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProjectService
{
    public function changeStatus(Project $project, string $newStatus, ?string $comment = null): Project
    {
        $oldStatus = $project->status;
        
        DB::transaction(function () use ($project, $oldStatus, $newStatus, $comment) {
            $project->update(['status' => $newStatus]);
            
            ProjectStatusLog::create([
                'company_id' => $project->company_id,
                'project_id' => $project->id,
                'user_id' => Auth::id(),
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'comment' => $comment,
            ]);
        });

        return $project->fresh();
    }

    public function approve(Project $project, string $comment): Project
    {
        if ($project->status !== ProjectStatus::ON_APPROVAL->value) {
            throw new \Exception('Проект не на согласовании');
        }

        return $this->changeStatus($project, ProjectStatus::APPROVED->value, $comment);
    }

    public function reject(Project $project, string $comment): Project
    {
        if ($project->status !== ProjectStatus::ON_APPROVAL->value) {
            throw new \Exception('Проект не на согласовании');
        }

        return $this->changeStatus($project, ProjectStatus::ON_REVISION->value, $comment);
    }

    public function sendToApproval(Project $project): Project
    {
        if (!in_array($project->status, [ProjectStatus::IN_CALCULATION->value, ProjectStatus::ON_REVISION->value])) {
            throw new \Exception('Проект не может быть отправлен на согласование');
        }

        return $this->changeStatus($project, ProjectStatus::ON_APPROVAL->value);
    }

    public function startImplementation(Project $project): Project
    {
        if ($project->status !== ProjectStatus::APPROVED->value) {
            throw new \Exception('Проект должен быть утвержден');
        }

        return $this->changeStatus($project, ProjectStatus::IN_PROGRESS->value);
    }

    public function addParticipant(Project $project, User $user, string $role): void
    {
        $project->participants()->attach($user->id, [
            'company_id' => $project->company_id,
            'role' => $role,
        ]);
    }

    public function removeParticipant(Project $project, User $user): void
    {
        if ($user->isDeputyDirector()) {
            throw new \Exception('Нельзя удалить заместителя директора');
        }

        $project->participants()->detach($user->id);
    }

    public function canManageParticipants(User $user): bool
    {
        return $user->isDirector() || $user->isDeputyDirector();
    }
}