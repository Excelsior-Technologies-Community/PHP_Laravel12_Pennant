<?php

namespace App\Features;

class NewDashboard
{
    /**
     * Determine whether the feature is enabled by default.
     */
    public function resolve(mixed $scope): mixed
    {
        return $scope?->is_admin === true;
    }
}