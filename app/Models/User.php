<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @method bool canManageProjects()
 * @method bool isDirector()
 * @method bool isDeputyDirector()
 * @method bool isPTO()
 * @method bool isSupply()
 * @method bool isProjectManager()
 * @method bool isSiteManager()
 * @method bool isAccountant()
 */
class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_participants')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function isProjectManager(): bool
    {
        return $this->role === 'project_manager';
    }

    public function isSiteManager(): bool
    {
        return $this->role === 'site_manager';
    }

    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }

    public function canManageParticipants(): bool
    {
        return $this->canManageProjects();
    }

    public function canApproveProjects(): bool
    {
        return $this->canManageProjects();
    }

    public function canViewFinancial(): bool
    {
        return $this->isAccountant() || $this->canManageProjects();
    }

    public function canUpdateFinancial(): bool
    {
        return $this->isAccountant() || $this->canManageProjects();
    }

        public function isPTO(): bool
    {
        return $this->role === 'pto';
    }

    public function isSupply(): bool
    {
        return $this->role === 'supply';
    }

    public function isDirector(): bool
    {
        return $this->role === 'director';
    }

    public function isDeputyDirector(): bool
    {
        return $this->role === 'deputy_director';
    }

    public function canManageProjects(): bool
    {
        return in_array($this->role, ['director', 'deputy_director']);    }
        
        /**
     * Проверка может ли пользователь удалить файл
     */
    public function canDeleteFile(Project $project, ProjectFile $file): bool
    {
        // Директор может удалять любые файлы
        if ($this->canManageProjects()) {
            return true;
        }
        
        // Проверяем что файл принадлежит пользователю
        if ($file->user_id !== $this->id) {
            return false;
        }
        
        // Проверяем статус проекта
        if (!in_array($project->status, ['in_calculation', 'on_revision'])) {
            return false;
        }
        
        // Проверяем что файл из отдела пользователя
        $userSection = $this->role === 'pto' ? 'pto' : 'supply';
        if ($file->section !== $userSection) {
            return false;
        }
        
        // Проверяем что отдел не утвержден
        $isApproved = $this->role === 'pto' ? $project->pto_approved === true : $project->supply_approved === true;
        if ($isApproved) {
            return false;
        }
        
        return true;
    }
}