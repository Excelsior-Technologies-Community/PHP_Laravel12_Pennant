<?php

namespace App\Http\Controllers;

use App\Features\BetaProfile;
use App\Features\CheckoutVariant;
use App\Features\NewDashboard;
use App\Features\NewReports;
use App\Models\ExperimentMetric;
use App\Models\FeatureAudit;
use App\Models\FeatureRollout;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FeatureController extends Controller
{
    /**
     * Available feature flags.
     */
    private function features(): array
    {
        return [
            'new_dashboard' => [
                'name' => 'New Dashboard',
                'description' => 'Modern visual dashboard layout with quick telemetry metrics.',
                'class' => NewDashboard::class,
                'category' => 'UI / Frontend',
            ],

            'new_reports' => [
                'name' => 'New Reports',
                'description' => 'Interactive analytics reporting and data visualization module.',
                'class' => NewReports::class,
                'category' => 'Analytics',
            ],

            'beta_profile' => [
                'name' => 'Beta Profile',
                'description' => 'Experimental user profile customization and settings.',
                'class' => BetaProfile::class,
                'category' => 'User Experience',
            ],

            'checkout_variant' => [
                'name' => 'A/B Checkout Experiment',
                'description' => 'Multi-variant A/B experiment for CTA buttons and checkout conversion.',
                'class' => CheckoutVariant::class,
                'category' => 'A/B Experimentation',
            ],
        ];
    }

    /**
     * Feature management dashboard.
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $featureDefinitions = $this->features();

        // 1. Feature Search
        $featureSearch = strtolower(trim($request->input('feature_search', '')));
        if ($featureSearch !== '') {
            $featureDefinitions = collect($featureDefinitions)
                ->filter(function ($feature) use ($featureSearch) {
                    return str_contains(strtolower($feature['name']), $featureSearch)
                        || str_contains(strtolower($feature['description']), $featureSearch)
                        || str_contains(strtolower($feature['category'] ?? ''), $featureSearch);
                })
                ->all();
        }

        // 2. Load Rollouts & Kill-Switch state for each feature
        $rollouts = [];
        foreach (array_keys($this->features()) as $fKey) {
            $rollouts[$fKey] = FeatureRollout::forFeature($fKey);
        }

        // 3. User Search & Pagination
        $userQuery = User::query()->orderBy('name');
        if ($request->filled('user_search')) {
            $search = trim($request->input('user_search'));
            $userQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $allUsers = User::orderBy('name')->get();
        $users = $userQuery->paginate(10)->withQueryString();

        // 4. Resolve status for each user
        $userFeatureMatrix = [];
        foreach ($users as $user) {
            $userFeatureMatrix[$user->id] = [];
            foreach ($featureDefinitions as $key => $meta) {
                $userFeatureMatrix[$user->id][$key] = Feature::for($user)->value($meta['class']);
            }
        }

        // 5. Global Stats
        $totalFeatures = count($this->features());
        $activeFeaturesCount = 0;
        $killedFeaturesCount = 0;

        foreach ($this->features() as $fKey => $fMeta) {
            $r = $rollouts[$fKey] ?? null;
            if ($r && $r->is_killed) {
                $killedFeaturesCount++;
            } elseif ($r && $r->percentage > 0) {
                $activeFeaturesCount++;
            }
        }

        // 6. A/B Testing Experiment Analytics
        $abStats = $this->getExperimentMetrics('checkout_ab');

        // 7. User Persona Simulator Data
        $simulatedUser = null;
        $simulatedFlags = [];
        if ($request->filled('simulate_user_id')) {
            $simUser = User::find($request->input('simulate_user_id'));
            if ($simUser) {
                $simulatedUser = $simUser;
                foreach ($this->features() as $key => $meta) {
                    $simulatedFlags[$key] = [
                        'name' => $meta['name'],
                        'value' => Feature::for($simUser)->value($meta['class']),
                        'is_active' => (bool) Feature::for($simUser)->active($meta['class']),
                    ];
                }
            }
        }

        return view('features.index', [
            'features' => $featureDefinitions,
            'allFeatures' => $this->features(),
            'rollouts' => $rollouts,
            'users' => $users,
            'allUsers' => $allUsers,
            'userFeatureMatrix' => $userFeatureMatrix,
            'totalFeatures' => $totalFeatures,
            'activeFeaturesCount' => $activeFeaturesCount,
            'killedFeaturesCount' => $killedFeaturesCount,
            'totalUsersCount' => User::count(),
            'abStats' => $abStats,
            'simulatedUser' => $simulatedUser,
            'simulatedFlags' => $simulatedFlags,
            'featureSearch' => $request->input('feature_search', ''),
            'userSearch' => $request->input('user_search', ''),
        ]);
    }

    /**
     * Update Traffic Rollout Percentage (0% to 100%).
     */
    public function setPercentage(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'feature_name' => 'required|string',
            'percentage' => 'required|integer|min:0|max:100',
        ]);

        $featureName = $request->input('feature_name');
        $percentage = (int) $request->input('percentage');

        $rollout = FeatureRollout::forFeature($featureName);
        $oldPercentage = $rollout->percentage;
        $rollout->percentage = $percentage;

        // If increasing percentage from 0, auto-unkill
        if ($percentage > 0 && $rollout->is_killed) {
            $rollout->is_killed = false;
        }
        $rollout->save();

        Feature::flushCache();

        FeatureAudit::logAction(
            featureName: $featureName,
            action: 'ROLLOUT_PERCENTAGE',
            targetType: 'ALL_USERS',
            targetId: null,
            targetName: "Traffic Rollout: {$oldPercentage}% → {$percentage}%",
            details: "Updated rollout traffic slider to {$percentage}%"
        );

        return redirect()->route('features.index')
            ->with('success', "Rollout percentage for '{$featureName}' set to {$percentage}%.");
    }

    /**
     * Emergency Kill Switch (1-Click Panic Kill).
     */
    public function killSwitch(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'feature_name' => 'required|string',
        ]);

        $featureName = $request->input('feature_name');
        $rollout = FeatureRollout::forFeature($featureName);
        $rollout->is_killed = !$rollout->is_killed;
        $rollout->save();

        Feature::flushCache();

        $action = $rollout->is_killed ? 'EMERGENCY_KILL' : 'RESTORE_FEATURE';

        FeatureAudit::logAction(
            featureName: $featureName,
            action: $action,
            targetType: 'ALL_USERS',
            targetId: null,
            targetName: $rollout->is_killed ? 'KILL SWITCH ACTIVE' : 'RESTORED',
            details: $rollout->is_killed ? 'Emergency kill switch triggered.' : 'Feature restored from kill switch.'
        );

        $msg = $rollout->is_killed
            ? "🚨 Kill Switch Triggered! '{$featureName}' has been instantly disabled globally."
            : "✅ Feature '{$featureName}' restored from kill switch.";

        return redirect()->route('features.index')->with('success', $msg);
    }

    /**
     * Master 1-Click Panic: Kill All Features.
     */
    public function panicKillAll(): RedirectResponse
    {
        $this->ensureAdmin();

        foreach (array_keys($this->features()) as $fKey) {
            $rollout = FeatureRollout::forFeature($fKey);
            $rollout->is_killed = true;
            $rollout->save();
        }

        Feature::flushCache();

        FeatureAudit::logAction(
            featureName: 'ALL_FEATURES',
            action: 'PANIC_KILL_ALL',
            targetType: 'ALL_USERS',
            targetId: null,
            targetName: 'GLOBAL PANIC KILL',
            details: 'Master panic switch triggered: all active features instantly disabled.'
        );

        return redirect()->route('features.index')
            ->with('success', "🚨 Master Panic Kill Executed! All feature flags have been killed across the application.");
    }

    /**
     * Toggle Maintenance Banner Mode.
     */
    public function toggleMaintenance(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'feature_name' => 'required|string',
            'message' => 'nullable|string|max:255',
        ]);

        $featureName = $request->input('feature_name');
        $rollout = FeatureRollout::forFeature($featureName);
        $rollout->is_maintenance = !$rollout->is_maintenance;
        if ($request->filled('message')) {
            $rollout->maintenance_message = $request->input('message');
        }
        $rollout->save();

        FeatureAudit::logAction(
            featureName: $featureName,
            action: $rollout->is_maintenance ? 'MAINTENANCE_ON' : 'MAINTENANCE_OFF',
            targetType: 'ALL_USERS',
            targetId: null,
            targetName: $rollout->is_maintenance ? 'Maintenance Active' : 'Maintenance Cleared',
            details: $rollout->maintenance_message
        );

        return redirect()->route('features.index')
            ->with('success', "Maintenance mode for '{$featureName}' updated.");
    }

    /**
     * Track A/B Testing Conversion Event.
     */
    public function trackConversion(Request $request): RedirectResponse
    {
        $variant = $request->input('variant', 'control');
        $user = Auth::user();

        ExperimentMetric::create([
            'experiment_name' => 'checkout_ab',
            'variant' => $variant,
            'user_id' => $user?->id,
            'converted' => true,
            'ip_address' => $request->ip(),
        ]);

        return redirect()->route('dashboard')
            ->with('success', "🎉 A/B Conversion Recorded for [{$variant}]! Thank you for testing.");
    }

    /**
     * Enable feature for a single user.
     */
    public function enable(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'feature' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);
        $featureClass = $this->resolveFeatureClass($request->feature);

        Feature::for($user)->activate($featureClass);

        FeatureAudit::logAction(
            featureName: $request->feature,
            action: 'ENABLE',
            targetType: 'USER',
            targetId: $user->id,
            targetName: $user->name,
            details: "Explicit override activated."
        );

        return back()->with('success', "Feature enabled for {$user->name}.");
    }

    /**
     * Disable feature for a single user.
     */
    public function disable(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'feature' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);
        $featureClass = $this->resolveFeatureClass($request->feature);

        Feature::for($user)->deactivate($featureClass);

        FeatureAudit::logAction(
            featureName: $request->feature,
            action: 'DISABLE',
            targetType: 'USER',
            targetId: $user->id,
            targetName: $user->name,
            details: "Explicit override deactivated."
        );

        return back()->with('success', "Feature disabled for {$user->name}.");
    }

    /**
     * Reset individual user override to default.
     */
    public function resetOverride(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'feature' => 'required|string',
        ]);

        $user = User::findOrFail($request->user_id);
        $featureClass = $this->resolveFeatureClass($request->feature);

        Feature::for($user)->forget($featureClass);

        FeatureAudit::logAction(
            featureName: $request->feature,
            action: 'RESET_OVERRIDE',
            targetType: 'USER',
            targetId: $user->id,
            targetName: $user->name,
            details: "Reset to default feature resolution."
        );

        return back()->with('success', "Override cleared for {$user->name}.");
    }

    /**
     * Bulk enable feature for all users.
     */
    public function bulkEnable(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'feature' => 'required|string',
        ]);

        $featureClass = $this->resolveFeatureClass($request->feature);
        $users = User::all();

        foreach ($users as $user) {
            Feature::for($user)->activate($featureClass);
        }

        FeatureAudit::logAction(
            featureName: $request->feature,
            action: 'BULK_ENABLE',
            targetType: 'ALL_USERS',
            targetId: null,
            targetName: "All Users (" . $users->count() . ")",
            details: "Bulk enabled across all database users."
        );

        return back()->with('success', "Feature enabled for all {$users->count()} users.");
    }

    /**
     * Bulk disable feature for all users.
     */
    public function bulkDisable(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $request->validate([
            'feature' => 'required|string',
        ]);

        $featureClass = $this->resolveFeatureClass($request->feature);
        $users = User::all();

        foreach ($users as $user) {
            Feature::for($user)->deactivate($featureClass);
        }

        FeatureAudit::logAction(
            featureName: $request->feature,
            action: 'BULK_DISABLE',
            targetType: 'ALL_USERS',
            targetId: null,
            targetName: "All Users (" . $users->count() . ")",
            details: "Bulk disabled across all database users."
        );

        return back()->with('success', "Feature disabled for all {$users->count()} users.");
    }

    /**
     * Export feature matrix as CSV.
     */
    public function export(): StreamedResponse
    {
        $this->ensureAdmin();

        $features = $this->features();
        $users = User::orderBy('name')->get();

        $filename = 'feature_flags_' . now()->format('Y_m_d_H_i_s') . '.csv';

        return response()->streamDownload(function () use ($users, $features) {
            $handle = fopen('php://output', 'w');

            $headers = ['User ID', 'Name', 'Email', 'Is Admin'];
            foreach ($features as $f) {
                $headers[] = $f['name'];
            }
            fputcsv($handle, $headers);

            foreach ($users as $user) {
                $row = [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->is_admin ? 'Yes' : 'No',
                ];

                foreach ($features as $f) {
                    $row[] = Feature::for($user)->active($f['class']) ? 'Active' : 'Inactive';
                }

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * Compute A/B Testing statistics.
     */
    private function getExperimentMetrics(string $experimentName): array
    {
        $variants = ['control', 'variant_a', 'variant_b'];
        $stats = [];
        $totalConversions = 0;

        foreach ($variants as $v) {
            $conversions = ExperimentMetric::where('experiment_name', $experimentName)
                ->where('variant', $v)
                ->where('converted', true)
                ->count();

            $totalConversions += $conversions;
            $stats[$v] = [
                'conversions' => $conversions,
            ];
        }

        foreach ($variants as $v) {
            $stats[$v]['cr_percent'] = $totalConversions > 0
                ? round(($stats[$v]['conversions'] / $totalConversions) * 100, 1)
                : 0;
        }

        return [
            'variants' => $stats,
            'total_conversions' => $totalConversions,
        ];
    }

    private function resolveFeatureClass(string $key): string
    {
        $features = $this->features();
        if (!isset($features[$key])) {
            abort(404, 'Feature not found.');
        }
        return $features[$key]['class'];
    }

    private function ensureAdmin(): void
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403, 'Unauthorized access to Feature Management.');
        }
    }
}