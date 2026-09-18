<?php

namespace App\Features;

use App\Models\FeatureRollout;

class NewDashboard
{
    /**
     * Determine whether the feature is enabled by default or via percentage rollout.
     */
    public function resolve(mixed $scope): mixed
    {
        $rollout = FeatureRollout::where('feature_name', 'new_dashboard')->first();

        // 1. Emergency Kill Switch Check
        if ($rollout && $rollout->is_killed) {
            return false;
        }

        // 2. Admin Default
        if ($scope?->is_admin === true) {
            return true;
        }

        // 3. Traffic Percentage Rollout (0% - 100%)
        if ($rollout && $rollout->percentage > 0 && isset($scope->id)) {
            $hash = abs(crc32((string) $scope->id . '_new_dashboard')) % 100;
            if ($hash < $rollout->percentage) {
                return true;
            }
        }

        return false;
    }
}