<?php

namespace App\Http\Controllers;

use App\Models\FeatureAudit;
use Illuminate\Http\Request;

class FeatureAuditController extends Controller
{
    /**
     * Display feature flag audit history.
     */
    public function index(Request $request)
    {
        abort_unless(
            auth()->check() && auth()->user()->is_admin,
            403
        );

        $query = FeatureAudit::with([
            'admin',
            'user',
        ])->latest();

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('feature', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('admin', function ($adminQuery) use ($search) {
                        $adminQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Feature Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('feature')) {
            $query->where('feature', $request->feature);
        }

        /*
        |--------------------------------------------------------------------------
        | Action Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('date_from')) {
            $query->whereDate(
                'created_at',
                '>=',
                $request->date_from
            );
        }

        if ($request->filled('date_to')) {
            $query->whereDate(
                'created_at',
                '<=',
                $request->date_to
            );
        }

        $audits = $query
            ->paginate(10)
            ->withQueryString();

        $features = FeatureAudit::query()
            ->select('feature')
            ->distinct()
            ->orderBy('feature')
            ->pluck('feature');

        $totalActivities = FeatureAudit::count();

        $enabledActivities = FeatureAudit::where(
            'action',
            'enabled'
        )->count();

        $disabledActivities = FeatureAudit::where(
            'action',
            'disabled'
        )->count();

        return view('features.audit', compact(
            'audits',
            'features',
            'totalActivities',
            'enabledActivities',
            'disabledActivities'
        ));
    }
}