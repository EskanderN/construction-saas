<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialDelivery extends Model
{
    protected $fillable = [
        'company_id',
        'project_id',
        'supply_user_id',
        'site_manager_user_id',
        'material_name',
        'quantity',
        'unit',
        'delivery_date',
        'confirmed_date',
        'photo_path',
        'status',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'confirmed_date' => 'date',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function supplyUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supply_user_id');
    }

    public function siteManagerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'site_manager_user_id');
    }
}