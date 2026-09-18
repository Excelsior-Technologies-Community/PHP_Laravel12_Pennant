<?php

namespace App\Features;

use App\Models\FeatureRollout;

class NewReports
{
    /**
     * Determine whether the feature is enabled.
     */
    public function resolve(mixed $scope): mixed
    {
        $rollout = FeatureRollout::where('feature_name', 'new_reports')->first();

        // 1. Emergency Kill Switch Check
        if ($rollout && $rollout->is_killed) {
            return false;
        }

        // 2. Traffic Percentage Rollout (0% - 100%)
        if ($rollout && $rollout->percentage > 0 && isset($scope->id)) {
            $hash = abs(crc32((string) $scope->id . '_new_reports')) % 100;
            if ($hash < $rollout->percentage) {
                return true;
            }
        }

        return false;
    }
}