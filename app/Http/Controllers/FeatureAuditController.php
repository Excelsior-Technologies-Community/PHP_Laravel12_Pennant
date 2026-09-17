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
            auth()->check() &&
            auth()->user()->is_admin,
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
            $search = trim(
                $request->input('search')
            );

            $query->where(function ($q) use ($search) {

                $q->where(
                    'feature',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'action',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'ip_address',
                    'like',
                    "%{$search}%"
                )

                ->orWhereHas(
                    'admin',
                    function ($adminQuery) use ($search) {

                        $adminQuery
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'email',
                                'like',
                                "%{$search}%"
                            );
                    }
                )

                ->orWhereHas(
                    'user',
                    function ($userQuery) use ($search) {

                        $userQuery
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'email',
                                'like',
                                "%{$search}%"
                            );
                    }
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Feature Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('feature')) {
            $query->where(
                'feature',
                $request->input('feature')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Action Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('action')) {
            $query->where(
                'action',
                $request->input('action')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Date Presets
        |--------------------------------------------------------------------------
        */

        $datePreset = $request->input(
            'date_preset'
        );

        $dateFrom = $request->input(
            'date_from'
        );

        $dateTo = $request->input(
            'date_to'
        );

        if ($datePreset === 'today') {

            $dateFrom = now()
                ->startOfDay()
                ->toDateString();

            $dateTo = now()
                ->endOfDay()
                ->toDateString();
        }

        if ($datePreset === '7_days') {

            $dateFrom = now()
                ->subDays(6)
                ->startOfDay()
                ->toDateString();

            $dateTo = now()
                ->endOfDay()
                ->toDateString();
        }

        if ($datePreset === '30_days') {

            $dateFrom = now()
                ->subDays(29)
                ->startOfDay()
                ->toDateString();

            $dateTo = now()
                ->endOfDay()
                ->toDateString();
        }

        /*
        |--------------------------------------------------------------------------
        | Date Filter
        |--------------------------------------------------------------------------
        */

        if ($dateFrom) {
            $query->whereDate(
                'created_at',
                '>=',
                $dateFrom
            );
        }

        if ($dateTo) {
            $query->whereDate(
                'created_at',
                '<=',
                $dateTo
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $audits = $query
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Feature List
        |--------------------------------------------------------------------------
        */

        $features = FeatureAudit::query()
            ->select('feature')
            ->distinct()
            ->orderBy('feature')
            ->pluck('feature');

        /*
        |--------------------------------------------------------------------------
        | Filtered Statistics
        |--------------------------------------------------------------------------
        */

        $statisticsQuery = clone $query;

        $totalActivities =
            $statisticsQuery->count();

        $enabledActivities =
            (clone $query)
                ->where('action', 'enabled')
                ->count();

        $disabledActivities =
            (clone $query)
                ->where('action', 'disabled')
                ->count();

        $resetActivities =
            (clone $query)
                ->where('action', 'reset')
                ->count();

        return view(
            'features.audit',
            compact(
                'audits',
                'features',
                'totalActivities',
                'enabledActivities',
                'disabledActivities',
                'resetActivities',
                'datePreset',
                'dateFrom',
                'dateTo'
            )
        );
    }
}