<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'status',
        'created_by',
        'pto_submitted_at',
        'supply_submitted_at',
        'pto_comment',
        'supply_comment',
        'pto_approved',
        'supply_approved',
    ];

    protected $casts = [
        'pto_submitted_at' => 'datetime',  // Это преобразует строку в Carbon объект
        'supply_submitted_at' => 'datetime',
        'pto_approved' => 'boolean',
        'supply_approved' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_participants')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectComment::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ProjectStatusLog::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function materialDeliveries(): HasMany
    {
        return $this->hasMany(MaterialDelivery::class);
    }

    public function financialStatusLogs(): HasMany
    {
        return $this->hasMany(FinancialStatusLog::class);
    }

    public function currentFinancialStatus()
    {
        return $this->financialStatusLogs()->latest()->first();
    }

        public function isPTOReady(): bool
    {
        return !is_null($this->pto_submitted_at);
    }

    public function isSupplyReady(): bool
    {
        return !is_null($this->supply_submitted_at);
    }

    public function isBothReady(): bool
    {
        return $this->isPTOReady() && $this->isSupplyReady();
    }

    public function isPTOApproved(): bool
    {
        return $this->pto_approved === true;
    }

    public function isSupplyApproved(): bool
    {
        return $this->supply_approved === true;
    }
}