<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'company_id',
        'project_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'status',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(TaskReport::class);
    }

    // Добавьте отношение
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    // Добавьте в $with для eager loading
    protected $with = ['assignee', 'creator', 'comments.user'];
}