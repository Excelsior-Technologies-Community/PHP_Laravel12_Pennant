<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Dashboard') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Laravel 12 Pennant Feature Management & Experimentation Suite
                </p>
            </div>

            @if(Auth::user()->is_admin)
                <a
                    href="{{ route('features.index') }}"
                    class="px-4 py-2 bg-indigo-600 text-white font-semibold text-xs uppercase tracking-wider rounded-lg hover:bg-indigo-700 shadow-sm transition"
                >
                    ⚙️ Manage Feature Flags
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash Success Message --}}
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span>🎉</span>
                        <span class="font-medium text-sm">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            {{-- 1. Maintenance Mode Banners (if any feature is under maintenance) --}}
            @php
                $activeMaintenances = \App\Models\FeatureRollout::where('is_maintenance', true)->get();
            @endphp

            @foreach($activeMaintenances as $maint)
                <div class="p-4 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">⚠️</span>
                        <div>
                            <div class="font-bold text-sm text-amber-900">
                                Maintenance Notice: {{ ucwords(str_replace('_', ' ', $maint->feature_name)) }}
                            </div>
                            <div class="text-xs text-amber-700 mt-0.5">
                                {{ $maint->maintenance_message ?? 'This module is temporarily undergoing maintenance.' }}
                            </div>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-200 text-amber-900">
                        SCHEDULED MAINTENANCE
                    </span>
                </div>
            @endforeach

            {{-- 2. Interactive A/B Testing Experiment Widget --}}
            @php
                $variant = \Laravel\Pennant\Feature::value(App\Features\CheckoutVariant::class);
            @endphp

            <div class="bg-white rounded-xl shadow-sm border border-purple-100 overflow-hidden">
                <div class="bg-gradient-to-r from-purple-50 via-indigo-50 to-white p-5 border-b border-purple-100 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg">🧪</span>
                            <h3 class="font-bold text-gray-900 text-base">Live A/B Testing Experiment: Call-To-Action Variant</h3>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Your user account is automatically assigned to a multi-variant testing bucket via Pennant hashing.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500">Your Assigned Variant:</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-600 text-white shadow-sm">
                            {{ strtoupper($variant) }}
                        </span>
                    </div>
                </div>

                <div class="p-6 flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="space-y-1">
                        <h4 class="font-bold text-gray-800 text-sm">Experience the Multi-Variant Conversion Flow</h4>
                        <p class="text-xs text-gray-500 max-w-xl">
                            Click the dynamic button below to simulate a real conversion event. Conversions are tracked in the database and displayed in real time on the Feature Analytics dashboard.
                        </p>
                    </div>

                    <div>
                        <form method="POST" action="{{ route('features.track-conversion') }}">
                            @csrf
                            <input type="hidden" name="variant" value="{{ $variant }}">

                            @if($variant === 'variant_a')
                                <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-sm shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2">
                                    <span>🚀</span>
                                    <span>Start 14-Day Free Trial (Variant A)</span>
                                </button>
                            @elseif($variant === 'variant_b')
                                <button type="submit" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-sm shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2">
                                    <span>⚡</span>
                                    <span>Get Instant Pro Access (Variant B)</span>
                                </button>
                            @else
                                <button type="submit" class="px-6 py-3 bg-gray-700 hover:bg-gray-800 text-white rounded-xl font-bold text-sm shadow-md hover:shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2">
                                    <span>Sign Up Free (Control)</span>
                                </button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            {{-- 3. Core Feature Flag Toggles Demo --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- 01: New Dashboard Feature -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">UI Experience</span>
                            @if(\Laravel\Pennant\Feature::active(App\Features\NewDashboard::class))
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">RESOLVED: ON</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">RESOLVED: OFF</span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-gray-900">New Dashboard Interface</h3>
                        <p class="text-xs text-gray-500 mt-1">Modern UI layout with dynamic widgets and real-time state.</p>
                    </div>

                    @if(\Laravel\Pennant\Feature::active(App\Features\NewDashboard::class))
                        <div class="p-3.5 bg-emerald-50 border border-emerald-200 rounded-lg text-xs text-emerald-800">
                            <strong>🆕 Active New Layout:</strong> You have full access to the modern 2026 UI dashboard.
                        </div>
                    @else
                        <div class="p-3.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600">
                            <strong>📊 Classic View:</strong> Currently rendering the stable legacy dashboard view.
                        </div>
                    @endif
                </div>

                <!-- 02: New Reports Feature -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Analytics</span>
                            @if(\Laravel\Pennant\Feature::active(App\Features\NewReports::class))
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">RESOLVED: ON</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">RESOLVED: OFF</span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-gray-900">Advanced Reports Module</h3>
                        <p class="text-xs text-gray-500 mt-1">Automated PDF downloads and conversion funnels.</p>
                    </div>

                    @if(\Laravel\Pennant\Feature::active(App\Features\NewReports::class))
                        <div class="p-3.5 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-800">
                            <strong>📈 Reports Enabled:</strong> You can export analytics and custom telemetry graphs.
                        </div>
                    @else
                        <div class="p-3.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600">
                            <strong>🔒 Feature Gated:</strong> Available to users inside the active rollout cohort.
                        </div>
                    @endif
                </div>

                <!-- 03: Beta Profile Feature -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">User Settings</span>
                            @if(\Laravel\Pennant\Feature::active(App\Features\BetaProfile::class))
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">RESOLVED: ON</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">RESOLVED: OFF</span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-gray-900">Beta Profile Experience</h3>
                        <p class="text-xs text-gray-500 mt-1">Experimental avatar customization and security key passkeys.</p>
                    </div>

                    @if(\Laravel\Pennant\Feature::active(App\Features\BetaProfile::class))
                        <div class="p-3.5 bg-purple-50 border border-purple-200 rounded-lg text-xs text-purple-800">
                            <strong>🧪 Beta Active:</strong> Enjoy early experimental settings before global release.
                        </div>
                    @else
                        <div class="p-3.5 bg-gray-50 border border-gray-200 rounded-lg text-xs text-gray-600">
                            <strong>👤 Standard Profile:</strong> Running standard profile management.
                        </div>
                    @endif
                </div>

            </div>

            {{-- 4. Pennant Blade Directives Playground Showcase --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span>💻</span>
                            <span>Pennant Blade Directives Playground</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Live demonstrator of Laravel Pennant native conditionals evaluated for your current session.
                        </p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-700">Blade Directives</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-mono">
                    {{-- Directives 1: Feature check --}}
                    <div class="p-4 rounded-lg bg-gray-900 text-gray-200 space-y-2">
                        <div class="text-indigo-400 font-bold">1. &#64;feature directive</div>
                        <div class="text-gray-400 text-[11px]">Feature::active(NewDashboard)</div>
                        <div class="p-2 bg-gray-800 rounded font-sans text-xs {{ \Laravel\Pennant\Feature::active(App\Features\NewDashboard::class) ? 'text-emerald-400' : 'text-red-400' }}">
                            @if(\Laravel\Pennant\Feature::active(App\Features\NewDashboard::class))
                                ✓ Condition Met (Feature Active)
                            @else
                                ✗ Condition Failed (Feature Inactive)
                            @endif
                        </div>
                    </div>

                    {{-- Directives 2: Inverse check --}}
                    <div class="p-4 rounded-lg bg-gray-900 text-gray-200 space-y-2">
                        <div class="text-indigo-400 font-bold">2. Inverse Guard</div>
                        <div class="text-gray-400 text-[11px]">! Feature::active(NewReports)</div>
                        <div class="p-2 bg-gray-800 rounded font-sans text-xs {{ ! \Laravel\Pennant\Feature::active(App\Features\NewReports::class) ? 'text-amber-400' : 'text-emerald-400' }}">
                            @if(! \Laravel\Pennant\Feature::active(App\Features\NewReports::class))
                                ✓ Inactive Guard Triggered (Feature Inactive)
                            @else
                                ✗ Inactive Guard Bypassed (Feature Active)
                            @endif
                        </div>
                    </div>

                    {{-- Directives 3: Feature::someAreActive --}}
                    <div class="p-4 rounded-lg bg-gray-900 text-gray-200 space-y-2">
                        <div class="text-indigo-400 font-bold">3. &#64;featureany directive</div>
                        <div class="text-gray-400 text-[11px]">Feature::someAreActive([NewDashboard, BetaProfile])</div>
                        <div class="p-2 bg-gray-800 rounded font-sans text-xs {{ \Laravel\Pennant\Feature::someAreActive([App\Features\NewDashboard::class, App\Features\BetaProfile::class]) ? 'text-sky-400' : 'text-gray-400' }}">
                            @if(\Laravel\Pennant\Feature::someAreActive([App\Features\NewDashboard::class, App\Features\BetaProfile::class]))
                                ✓ Any Clause Met (At least 1 Active)
                            @else
                                ✗ Any Clause Failed (None Active)
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>