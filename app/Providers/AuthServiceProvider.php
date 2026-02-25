<?php

namespace App\Providers;

use App\Models\Project;
use App\Models\Task;
use App\Models\MaterialDelivery;
use App\Policies\ProjectPolicy;
use App\Policies\TaskPolicy;
use App\Policies\MaterialDeliveryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Project::class => ProjectPolicy::class,
        Task::class => TaskPolicy::class,
        MaterialDelivery::class => MaterialDeliveryPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}