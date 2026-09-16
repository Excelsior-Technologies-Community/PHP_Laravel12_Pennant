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
     */
public function index()
{
    $this->ensureAdmin();

    $featureDefinitions = $this->features();

    $users = User::query()
        ->orderBy('name')
        ->get();

    $features = [];

    foreach ($featureDefinitions as $key => $feature) {

        $active = false;

        foreach ($users as $user) {
            if (Feature::for($user)->active($feature['class'])) {
                $active = true;
                break;
            }
        }

        $features[] = [
            'key' => $key,
            'name' => $feature['name'],
            'description' => $feature['description'],
            'class' => $feature['class'],
            'active' => $active,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    $totalFeatures = count($features);

    $activeFeatures = collect($features)
        ->where('active', true)
        ->count();

    $inactiveFeatures = $totalFeatures - $activeFeatures;

    $userOverrides = DB::table('features')->count();

    return view('features.index', compact(
        'features',
        'users',
        'totalFeatures',
        'activeFeatures',
        'inactiveFeatures',
        'userOverrides'
    ));
}

    /**
     * Enable a feature for a specific user.
     */
    public function enable(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'feature' => ['required', 'string'],
        ]);

        $feature = $this->getFeature($validated['feature']);
        $user = User::findOrFail($validated['user_id']);

        Feature::for($user)->activate($feature['class']);

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
     * Disable a feature for a specific user.
     */
    public function disable(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'feature' => ['required', 'string'],
        ]);

        $feature = $this->getFeature($validated['feature']);
        $user = User::findOrFail($validated['user_id']);

        Feature::for($user)->deactivate($feature['class']);

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
     * Make sure the logged-in user is an administrator.
     */
    private function ensureAdmin(): void
    {
        abort_unless(
            auth()->check() && auth()->user()->is_admin,
            403
        );
    }

    /**
     * Get a feature definition by key.
     */
    private function getFeature(string $key): array
    {
        $features = $this->features();

        abort_unless(isset($features[$key]), 404);

        return $features[$key];
    }
}