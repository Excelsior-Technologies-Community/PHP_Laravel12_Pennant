<?php

namespace App\Features;

class NewReports
{
    /**
     * Determine whether the feature is enabled.
     */
    public function resolve(mixed $scope): mixed
    {
        // Disabled by default.
        // Admins can enable it for specific users using Pennant.
        return false;
    }
}