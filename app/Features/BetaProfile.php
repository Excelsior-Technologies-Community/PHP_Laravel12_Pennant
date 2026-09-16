<?php

namespace App\Features;

class BetaProfile
{
    /**
     * Determine whether the feature is enabled.
     */
    public function resolve(mixed $scope): mixed
    {
        // Disabled by default.
        // Admins can enable it for selected users using Pennant.
        return false;
    }
}