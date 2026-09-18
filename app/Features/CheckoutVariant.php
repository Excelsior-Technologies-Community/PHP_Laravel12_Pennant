<?php

namespace App\Features;

use App\Models\FeatureRollout;

class CheckoutVariant
{
    /**
     * Determine the multi-variant value for A/B testing:
     * - 'control'   : Standard Gray Action Button ("Sign Up Free")
     * - 'variant_a' : High-Converting Emerald Pulse ("Start 14-Day Free Trial")
     * - 'variant_b' : Modern Indigo Cyber Glow ("Get Instant Pro Access")
     */
    public function resolve(mixed $scope): string
    {
        $rollout = FeatureRollout::where('feature_name', 'checkout_variant')->first();

        // If killed -> revert to control
        if ($rollout && $rollout->is_killed) {
            return 'control';
        }

        if (!$scope || !isset($scope->id)) {
            return 'control';
        }

        // Deterministic partition: 33% Control, 33% Variant A, 34% Variant B
        $hash = abs(crc32((string) $scope->id . '_checkout_ab')) % 100;

        if ($hash < 33) {
            return 'control';
        } elseif ($hash < 66) {
            return 'variant_a';
        } else {
            return 'variant_b';
        }
    }
}
