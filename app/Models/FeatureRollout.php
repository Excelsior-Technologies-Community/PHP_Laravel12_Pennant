<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeatureRollout extends Model
{
    use HasFactory;

    protected $fillable = [
        'feature_name',
        'percentage',
        'is_killed',
        'is_maintenance',
        'maintenance_message',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'integer',
            'is_killed' => 'boolean',
            'is_maintenance' => 'boolean',
        ];
    }

    /**
     * Get or create default rollout settings for a feature.
     */
    public static function forFeature(string $featureName): self
    {
        return static::firstOrCreate(
            ['feature_name' => $featureName],
            [
                'percentage' => 0,
                'is_killed' => false,
                'is_maintenance' => false,
                'maintenance_message' => 'This module is temporarily undergoing maintenance.',
            ]
        );
    }
}
