<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MaterialDelivery;

class MaterialDeliveryPolicy
{
    public function createMaterial(User $user, $project): bool
    {
        return $user->company_id === $project->company_id && $user->isSupply();
    }

    public function confirmDelivery(User $user, MaterialDelivery $delivery): bool
    {
        return $user->company_id === $delivery->company_id && $user->isSiteManager();
    }
}