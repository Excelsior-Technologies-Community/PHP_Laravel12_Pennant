<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use App\Features\NewDashboard;

class DashboardController extends Controller
{
    public function enableFeature(Request $request)
    {
        Feature::for($request->user())
            ->activate(NewDashboard::class);

        return back()->with(
            'success',
            'New Dashboard Enabled!'
        );
    }

    public function disableFeature(Request $request)
    {
        Feature::for($request->user())
            ->deactivate(NewDashboard::class);

        return back()->with(
            'success',
            'New Dashboard Disabled!'
        );
    }
}