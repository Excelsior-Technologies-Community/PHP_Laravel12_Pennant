<?php

namespace App\Features;

use App\Models\FeatureRollout;

class BetaProfile
{
    /**
     * Determine whether the feature is enabled.
     */
    public function resolve(mixed $scope): mixed
    {
        $rollout = FeatureRollout::where('feature_name', 'beta_profile')->first();

        // 1. Emergency Kill Switch Check
        if ($rollout && $rollout->is_killed) {
            return false;
        }

        // 2. Traffic Percentage Rollout (0% - 100%)
        if ($rollout && $rollout->percentage > 0 && isset($scope->id)) {
            $hash = abs(crc32((string) $scope->id . '_beta_profile')) % 100;
            if ($hash < $rollout->percentage) {
                return true;
            }
        }

        return false;
    }
}