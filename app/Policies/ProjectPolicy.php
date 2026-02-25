<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Все пользователи компании могут видеть список проектов
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        // Проверяем что пользователь из той же компании
        if ($user->company_id !== $project->company_id) {
            return false;
        }

        // Директор и замдиректора могут видеть все проекты компании
        if ($user->canManageProjects()) {
            return true;
        }

        // Остальные пользователи могут видеть только проекты, в которых они участвуют
        return $project->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canManageProjects(); // Только директор и замдиректора
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id && $user->canManageProjects();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id && $user->canManageProjects();
    }

    /**
     * Determine whether the user can manage participants.
     */
    public function manageParticipants(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id && $user->canManageProjects();
    }

    /**
     * Determine whether the user can approve the project.
     */
    public function approve(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id && $user->canManageProjects();
    }

    /**
     * Determine whether the user can create tasks.
     */
    public function createTask(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id && $user->isProjectManager();
    }

    /**
     * Determine whether the user can view financial status.
     */
    public function viewFinancial(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id && (
            $user->isAccountant() || $user->canManageProjects()
        );
    }

    /**
     * Determine whether the user can update financial status.
     */
    public function updateFinancial(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id && (
            $user->isAccountant() || $user->canManageProjects()
        );
    }

    /**
     * Determine whether the user can create materials.
     */
    public function createMaterial(User $user, Project $project): bool
    {
        return $user->company_id === $project->company_id && $user->isSupply();
    }

    /**
     * Determine whether the user can upload files to PTO section.
     */
    public function uploadPTOFiles(User $user, Project $project): bool
    {
        // Только ПТО может загружать файлы в свой раздел
        if (!$user->isPTO()) {
            return false;
        }
        
        // Проверяем что пользователь участник проекта
        $isParticipant = $project->participants()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'pto')
            ->exists();
            
        if (!$isParticipant) {
            return false;
        }
        
        // Можно загружать если отдел не утвержден
        return $project->pto_approved !== true;
    }

    /**
     * Determine whether the user can upload files to Supply section.
     */
    public function uploadSupplyFiles(User $user, Project $project): bool
    {
        if (!$user->isSupply()) {
            return false;
        }
        
        $isParticipant = $project->participants()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'supply')
            ->exists();
            
        if (!$isParticipant) {
            return false;
        }
        
        return $project->supply_approved !== true;
    }

    /**
     * Determine whether the user can submit PTO calculations.
     */
    public function submitPTO(User $user, Project $project): bool
    {
        // Только ПТО может отправлять расчеты
        if (!$user->isPTO()) {
            return false;
        }
        
        // Проверяем что пользователь участник проекта
        $isParticipant = $project->participants()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'pto')
            ->exists();
            
        if (!$isParticipant) {
            return false;
        }
        
        // Можно отправлять если есть файлы и отдел не утвержден
        $hasFiles = $project->files()
            ->where('section', 'pto')
            ->where('user_id', $user->id)
            ->exists();
            
        return $hasFiles && $project->pto_approved !== true;
    }

    /**
     * Determine whether the user can submit Supply calculations.
     */
    public function submitSupply(User $user, Project $project): bool
    {
        if (!$user->isSupply()) {
            return false;
        }
        
        $isParticipant = $project->participants()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'supply')
            ->exists();
            
        if (!$isParticipant) {
            return false;
        }
        
        $hasFiles = $project->files()
            ->where('section', 'supply')
            ->where('user_id', $user->id)
            ->exists();
            
        return $hasFiles && $project->supply_approved !== true;
    }

    /**
     * Determine whether the user can view PTO section
     */
    public function viewPTOSection(User $user, Project $project): bool
    {
        return ($user->isPTO() || $user->isDirector() || $user->isDeputyDirector()) &&
            $user->company_id === $project->company_id;
    }

    /**
     * Determine whether the user can view Supply section
     */
    public function viewSupplySection(User $user, Project $project): bool
    {
        return ($user->isSupply() || $user->isDirector() || $user->isDeputyDirector()) &&
            $user->company_id === $project->company_id;
    }
}