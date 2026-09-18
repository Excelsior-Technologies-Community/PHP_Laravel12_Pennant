<?php

namespace Database\Seeders;

use App\Models\ExperimentMetric;
use App\Models\FeatureAudit;
use App\Models\FeatureRollout;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create or Update Primary Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Also ensure test@example.com is an Admin for quick testing
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Create Sample Test Users with Different Roles & IDs
        $demoUsers = [
            ['name' => 'Alex Johnson (Beta Tester)', 'email' => 'alex@example.com', 'is_admin' => false],
            ['name' => 'Sophia Williams (Product Lead)', 'email' => 'sophia@example.com', 'is_admin' => false],
            ['name' => 'David Miller (QA Engineer)', 'email' => 'david@example.com', 'is_admin' => false],
            ['name' => 'Emma Davis (Enterprise Client)', 'email' => 'emma@example.com', 'is_admin' => false],
            ['name' => 'Liam Wilson (Marketing Manager)', 'email' => 'liam@example.com', 'is_admin' => false],
            ['name' => 'Olivia Brown (Standard User)', 'email' => 'olivia@example.com', 'is_admin' => false],
            ['name' => 'Noah Garcia (Power User)', 'email' => 'noah@example.com', 'is_admin' => false],
            ['name' => 'Ava Martinez (Early Adopter)', 'email' => 'ava@example.com', 'is_admin' => false],
        ];

        foreach ($demoUsers as $u) {
            User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => Hash::make('password'),
                    'is_admin' => $u['is_admin'],
                    'email_verified_at' => now(),
                ]
            );
        }

        // 4. Seed Feature Rollouts Defaults
        $rollouts = [
            [
                'feature_name' => 'new_dashboard',
                'percentage' => 50,
                'is_killed' => false,
                'is_maintenance' => false,
                'maintenance_message' => 'New Dashboard layout is undergoing temporary maintenance.',
            ],
            [
                'feature_name' => 'new_reports',
                'percentage' => 25,
                'is_killed' => false,
                'is_maintenance' => false,
                'maintenance_message' => 'Reporting data pipelines are currently syncing.',
            ],
            [
                'feature_name' => 'beta_profile',
                'percentage' => 75,
                'is_killed' => false,
                'is_maintenance' => false,
                'maintenance_message' => 'Profile settings upgrade in progress.',
            ],
            [
                'feature_name' => 'checkout_variant',
                'percentage' => 100,
                'is_killed' => false,
                'is_maintenance' => false,
                'maintenance_message' => 'A/B Experiment is running actively.',
            ],
        ];

        foreach ($rollouts as $r) {
            FeatureRollout::updateOrCreate(
                ['feature_name' => $r['feature_name']],
                $r
            );
        }

        // 5. Seed A/B Experiment Conversion Metrics (for checkout_variant)
        ExperimentMetric::truncate();
        $variants = [
            'control' => 14,
            'variant_a' => 38,
            'variant_b' => 52,
        ];

        $allUsers = User::all();

        foreach ($variants as $variant => $count) {
            for ($i = 0; $i < $count; $i++) {
                $randomUser = $allUsers->random();
                ExperimentMetric::create([
                    'experiment_name' => 'checkout_variant',
                    'variant' => $variant,
                    'user_id' => $randomUser->id,
                    'converted' => true,
                    'ip_address' => '127.0.0.1',
                ]);
            }
        }

        // 6. Seed Feature Audit History
        FeatureAudit::truncate();
        $auditActions = [
            ['feature' => 'new_dashboard', 'action' => 'percentage_rollout_set_50%'],
            ['feature' => 'new_reports', 'action' => 'percentage_rollout_set_25%'],
            ['feature' => 'beta_profile', 'action' => 'percentage_rollout_set_75%'],
            ['feature' => 'checkout_variant', 'action' => 'percentage_rollout_set_100%'],
        ];

        foreach ($auditActions as $audit) {
            FeatureAudit::create([
                'admin_id' => $admin->id,
                'user_id' => null,
                'feature' => $audit['feature'],
                'action' => $audit['action'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0',
            ]);
        }
    }
}

