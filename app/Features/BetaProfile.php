<?php

namespace App\Features;

class BetaProfile
{
    /**
     * Determine whether the feature is enabled.
     */
    public function resolve(mixed $scope): mixed
    {
        return false;
    }
}