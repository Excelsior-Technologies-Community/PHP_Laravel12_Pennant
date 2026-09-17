<?php

namespace App\Http\Controllers;

use App\Features\BetaProfile;
use App\Features\NewDashboard;
use App\Features\NewReports;
use App\Models\FeatureAudit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Pennant\Feature;

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
                'description' => 'Enable the new dashboard interface.',
                'class' => NewDashboard::class,
            ],

            'new_reports' => [
                'name' => 'New Reports',
                'description' => 'Enable the new reports functionality.',
                'class' => NewReports::class,
            ],

            'beta_profile' => [
                'name' => 'Beta Profile',
                'description' => 'Enable the beta profile experience.',
                'class' => BetaProfile::class,
            ],
        ];
    }

    /**
     * Feature management dashboard.
     *
     * New:
     * 1. Feature search
     * 2. User search
     * 3. Status filter
     * 4. Active-user count
     */
    public function index(Request $request)
    {
        $this->ensureAdmin();

        $featureDefinitions = $this->features();

        /*
        |--------------------------------------------------------------------------
        | Feature Search
        |--------------------------------------------------------------------------
        */

        $featureSearch = strtolower(
            trim($request->input('feature_search', ''))
        );

        if ($featureSearch !== '') {
            $featureDefinitions = collect($featureDefinitions)
                ->filter(function ($feature) use ($featureSearch) {
                    return str_contains(
                        strtolower($feature['name']),
                        $featureSearch
                    )
                    ||
                    str_contains(
                        strtolower($feature['description']),
                        $featureSearch
                    );
                })
                ->all();
        }

        /*
        |--------------------------------------------------------------------------
        | User Search
        |--------------------------------------------------------------------------
        */

        $userQuery = User::query()
            ->orderBy('name');

        if ($request->filled('user_search')) {
            $search = trim($request->input('user_search'));

            $userQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $userQuery->get();

        /*
        |--------------------------------------------------------------------------
        | Feature Information
        |--------------------------------------------------------------------------
        */

        $features = [];

        foreach ($featureDefinitions as $key => $feature) {
            $activeUserCount = 0;

            foreach ($users as $user) {
                if (
                    Feature::for($user)
                        ->active($feature['class'])
                ) {
                    $activeUserCount++;
                }
            }

            $features[] = [
                'key' => $key,
                'name' => $feature['name'],
                'description' => $feature['description'],
                'class' => $feature['class'],
                'active' => $activeUserCount > 0,
                'active_user_count' => $activeUserCount,
                'total_user_count' => $users->count(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        $status = $request->input('status');

        if ($status === 'enabled') {
            $features = array_values(
                array_filter(
                    $features,
                    fn ($feature) => $feature['active']
                )
            );
        }

        if ($status === 'disabled') {
            $features = array_values(
                array_filter(
                    $features,
                    fn ($feature) => !$feature['active']
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalFeatures = count($this->features());

        $allFeatureData = [];

        foreach ($this->features() as $key => $feature) {
            $activeUsers = 0;

            foreach ($users as $user) {
                if (
                    Feature::for($user)
                        ->active($feature['class'])
                ) {
                    $activeUsers++;
                }
            }

            $allFeatureData[] = [
                'key' => $key,
                'active' => $activeUsers > 0,
                'active_users' => $activeUsers,
            ];
        }

        $activeFeatures = collect($allFeatureData)
            ->where('active', true)
            ->count();

        $inactiveFeatures = $totalFeatures - $activeFeatures;

        $userOverrides = DB::table('features')->count();

        $totalUsers = User::count();

        $totalAuditActivities = FeatureAudit::count();

        return view(
            'features.index',
            compact(
                'features',
                'users',
                'totalFeatures',
                'activeFeatures',
                'inactiveFeatures',
                'userOverrides',
                'totalUsers',
                'totalAuditActivities'
            )
        );
    }

    /**
     * Enable a feature for one user.
     */
    public function enable(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'feature' => [
                'required',
                'string',
            ],
        ]);

        $feature = $this->getFeature(
            $validated['feature']
        );

        $user = User::findOrFail(
            $validated['user_id']
        );

        Feature::for($user)
            ->activate($feature['class']);

        $this->createAudit(
            admin: $request->user(),
            user: $user,
            feature: $feature['name'],
            action: 'enabled',
            request: $request
        );

        return back()->with(
            'success',
            "{$feature['name']} enabled for {$user->name}."
        );
    }

    /**
     * Disable a feature for one user.
     */
    public function disable(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'feature' => [
                'required',
                'string',
            ],
        ]);

        $feature = $this->getFeature(
            $validated['feature']
        );

        $user = User::findOrFail(
            $validated['user_id']
        );

        Feature::for($user)
            ->deactivate($feature['class']);

        $this->createAudit(
            admin: $request->user(),
            user: $user,
            feature: $feature['name'],
            action: 'disabled',
            request: $request
        );

        return back()->with(
            'success',
            "{$feature['name']} disabled for {$user->name}."
        );
    }

    /**
     * Bulk enable a feature.
     *
     * New functionality #5
     */
    public function bulkEnable(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'feature' => [
                'required',
                'string',
            ],

            'user_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'user_ids.*' => [
                'integer',
                'exists:users,id',
            ],
        ]);

        $feature = $this->getFeature(
            $validated['feature']
        );

        $count = 0;

        foreach ($validated['user_ids'] as $userId) {
            $user = User::findOrFail($userId);

            Feature::for($user)
                ->activate($feature['class']);

            $this->createAudit(
                admin: $request->user(),
                user: $user,
                feature: $feature['name'],
                action: 'enabled',
                request: $request
            );

            $count++;
        }

        return back()->with(
            'success',
            "{$feature['name']} enabled for {$count} selected user(s)."
        );
    }

    /**
     * Bulk disable a feature.
     *
     * New functionality #6
     */
    public function bulkDisable(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'feature' => [
                'required',
                'string',
            ],

            'user_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'user_ids.*' => [
                'integer',
                'exists:users,id',
            ],
        ]);

        $feature = $this->getFeature(
            $validated['feature']
        );

        $count = 0;

        foreach ($validated['user_ids'] as $userId) {
            $user = User::findOrFail($userId);

            Feature::for($user)
                ->deactivate($feature['class']);

            $this->createAudit(
                admin: $request->user(),
                user: $user,
                feature: $feature['name'],
                action: 'disabled',
                request: $request
            );

            $count++;
        }

        return back()->with(
            'success',
            "{$feature['name']} disabled for {$count} selected user(s)."
        );
    }

    /**
     * Forget/reset a user-specific Pennant override.
     *
     * New functionality #7
     */
    public function resetOverride(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'feature' => [
                'required',
                'string',
            ],
        ]);

        $feature = $this->getFeature(
            $validated['feature']
        );

        $user = User::findOrFail(
            $validated['user_id']
        );

        Feature::for($user)
            ->forget($feature['class']);

        $this->createAudit(
            admin: $request->user(),
            user: $user,
            feature: $feature['name'],
            action: 'reset',
            request: $request
        );

        return back()->with(
            'success',
            "{$feature['name']} override reset for {$user->name}."
        );
    }

    /**
     * Export feature/user information as CSV.
     *
     * New functionality #8
     */
    public function export(Request $request)
    {
        $this->ensureAdmin();

        $featureDefinitions = $this->features();

        $filename =
            'feature_flags_' .
            now()->format('Y_m_d_H_i_s') .
            '.csv';

        return response()->streamDownload(
            function () use ($featureDefinitions) {

                $handle = fopen('php://output', 'w');

                fputcsv($handle, [
                    'User ID',
                    'User Name',
                    'Email',
                    'Feature',
                    'Status',
                ]);

                User::query()
                    ->orderBy('id')
                    ->chunk(100, function ($users) use (
                        $handle,
                        $featureDefinitions
                    ) {

                        foreach ($users as $user) {

                            foreach (
                                $featureDefinitions
                                as $feature
                            ) {

                                $active =
                                    Feature::for($user)
                                        ->active($feature['class']);

                                fputcsv($handle, [
                                    $user->id,
                                    $user->name,
                                    $user->email,
                                    $feature['name'],
                                    $active
                                        ? 'Enabled'
                                        : 'Disabled',
                                ]);
                            }
                        }
                    });

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',
            ]
        );
    }

    /**
     * Store feature activity in audit history.
     */
    private function createAudit(
        User $admin,
        User $user,
        string $feature,
        string $action,
        Request $request
    ): void {
        FeatureAudit::create([
            'admin_id' => $admin->id,
            'user_id' => $user->id,
            'feature' => $feature,
            'action' => $action,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Make sure logged-in user is administrator.
     */
    private function ensureAdmin(): void
    {
        abort_unless(
            auth()->check() &&
            auth()->user()->is_admin,
            403
        );
    }

    /**
     * Get feature definition.
     */
    private function getFeature(string $key): array
    {
        $features = $this->features();

        abort_unless(
            isset($features[$key]),
            404
        );

        return $features[$key];
    }
}