<?php

namespace App\Features;

class NewDashboard
{
    public function resolve(mixed $scope): mixed
    {
        // Default logic: Only admins see new dashboard
        return $scope?->is_admin === 1;
    }
}
