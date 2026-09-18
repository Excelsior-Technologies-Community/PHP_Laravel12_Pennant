<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-2xl">🚩</span>
                    <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                        Feature Flag Management Suite
                    </h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                        Pennant 12
                    </span>
                </div>
                <p class="text-sm text-gray-500 mt-1">
                    Gradual percentage rollouts, emergency kill-switches, A/B testing experimentation, and user persona simulation.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Master Panic Kill All -->
                <form method="POST" action="{{ route('features.panic-kill-all') }}" onsubmit="return confirm('⚠️ DANGER: This will instantly KILL all features across the entire application. Are you sure?');">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white rounded-md text-xs font-bold uppercase tracking-widest hover:bg-red-700 shadow-sm transition">
                        🚨 Panic Kill All
                    </button>
                </form>

                <a href="{{ route('features.export') }}" class="inline-flex items-center justify-center px-4 py-2 bg-emerald-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-emerald-700 shadow-sm transition">
                    📥 Export CSV
                </a>

                <a href="{{ route('features.audit') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700 shadow-sm transition">
                    🔐 Audit History
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span>✨</span>
                        <span class="font-medium text-sm">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 bg-red-50 border border-red-300 text-red-800 rounded-xl shadow-sm">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- SECTION 1: Top Metrics --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Feature Flags</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-2">{{ $totalFeatures }}</p>
                    <span class="text-xs text-gray-400 mt-1 inline-block">Registered in Pennant</span>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Active Rollouts</p>
                    <p class="text-3xl font-extrabold text-indigo-600 mt-2">{{ $activeFeaturesCount }}</p>
                    <span class="text-xs text-emerald-600 font-medium mt-1 inline-block">● Serving traffic</span>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Killed / Emergency Off</p>
                    <p class="text-3xl font-extrabold {{ $killedFeaturesCount > 0 ? 'text-red-600' : 'text-gray-900' }} mt-2">{{ $killedFeaturesCount }}</p>
                    <span class="text-xs {{ $killedFeaturesCount > 0 ? 'text-red-500 font-medium' : 'text-gray-400' }} mt-1 inline-block">
                        {{ $killedFeaturesCount > 0 ? '🚨 Kill Switch Engaged' : 'Zero active emergency kills' }}
                    </span>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Users Base</p>
                    <p class="text-3xl font-extrabold text-gray-900 mt-2">{{ $totalUsersCount }}</p>
                    <span class="text-xs text-gray-400 mt-1 inline-block">Scope resolution targets</span>
                </div>
            </div>

            {{-- SECTION 2: Percentage Rollouts, Emergency Kill-Switch & Maintenance Controls --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                            <span>🎯</span>
                            <span>Traffic Percentage Rollouts & Emergency Controls</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Adjust gradual rollout percentage (0% - 100%), engage 1-click panic kill switches, or toggle maintenance mode.
                        </p>
                    </div>
                </div>

                <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach($allFeatures as $fKey => $meta)
                        @php
                            $r = $rollouts[$fKey] ?? null;
                            $percentage = $r ? $r->percentage : 0;
                            $isKilled = $r && $r->is_killed;
                            $isMaint = $r && $r->is_maintenance;
                        @endphp

                        <div class="border {{ $isKilled ? 'border-red-300 bg-red-50/30' : ($isMaint ? 'border-amber-300 bg-amber-50/30' : 'border-gray-200 bg-gray-50/50') }} rounded-xl p-5 space-y-4 shadow-sm transition">
                            <!-- Feature Header -->
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-gray-900">{{ $meta['name'] }}</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-200 text-gray-700">
                                            {{ $meta['category'] ?? 'Feature' }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">{{ $meta['description'] }}</p>
                                </div>

                                <!-- Status Badges -->
                                <div class="flex flex-col items-end gap-1">
                                    @if($isKilled)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-600 text-white shadow-sm animate-pulse">
                                            🚨 KILLED
                                        </span>
                                    @elseif($isMaint)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500 text-white shadow-sm">
                                            ⚠️ MAINTENANCE
                                        </span>
                                    @elseif($percentage > 0)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-600 text-white shadow-sm">
                                            {{ $percentage }}% ROLLOUT
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-gray-400 text-white">
                                            0% DISABLED
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Traffic Percentage Rollout Slider Form -->
                            <form method="POST" action="{{ route('features.set-percentage') }}" class="bg-white p-3.5 rounded-lg border border-gray-200 space-y-2">
                                @csrf
                                <input type="hidden" name="feature_name" value="{{ $fKey }}">
                                <div class="flex justify-between items-center text-xs font-semibold text-gray-700">
                                    <span>Traffic Allocation</span>
                                    <span class="text-indigo-600 font-bold text-sm" id="pctLabel_{{ $fKey }}">{{ $percentage }}%</span>
                                </div>

                                <input
                                    type="range"
                                    name="percentage"
                                    min="0"
                                    max="100"
                                    value="{{ $percentage }}"
                                    class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-indigo-600"
                                    oninput="document.getElementById('pctLabel_{{ $fKey }}').innerText = this.value + '%'"
                                >

                                <!-- Quick Presets & Submit -->
                                <div class="flex items-center justify-between pt-1">
                                    <div class="flex gap-1">
                                        @foreach([0, 10, 25, 50, 75, 100] as $preset)
                                            <button
                                                type="button"
                                                onclick="this.form.querySelector('input[type=range]').value = {{ $preset }}; document.getElementById('pctLabel_{{ $fKey }}').innerText = '{{ $preset }}%';"
                                                class="px-2 py-0.5 text-[10px] font-semibold rounded bg-gray-100 text-gray-600 hover:bg-indigo-100 hover:text-indigo-700 transition"
                                            >
                                                {{ $preset }}%
                                            </button>
                                        @endforeach
                                    </div>
                                    <button type="submit" class="px-3 py-1 bg-indigo-600 text-white rounded text-xs font-semibold hover:bg-indigo-700 shadow-sm transition">
                                        Save Rollout
                                    </button>
                                </div>
                            </form>

                            <!-- Emergency Kill Switch & Maintenance Mode Buttons -->
                            <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-gray-200/60">
                                <div class="flex items-center gap-2">
                                    <!-- Kill Switch -->
                                    <form method="POST" action="{{ route('features.kill-switch') }}">
                                        @csrf
                                        <input type="hidden" name="feature_name" value="{{ $fKey }}">
                                        <button
                                            type="submit"
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $isKilled ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'bg-red-100 text-red-700 border border-red-300 hover:bg-red-200' }}"
                                        >
                                            {{ $isKilled ? '✅ Restore Feature' : '🚨 Kill Switch' }}
                                        </button>
                                    </form>

                                    <!-- Maintenance Mode -->
                                    <form method="POST" action="{{ route('features.toggle-maintenance') }}">
                                        @csrf
                                        <input type="hidden" name="feature_name" value="{{ $fKey }}">
                                        <button
                                            type="submit"
                                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $isMaint ? 'bg-gray-800 text-white' : 'bg-amber-100 text-amber-800 border border-amber-300 hover:bg-amber-200' }}"
                                        >
                                            {{ $isMaint ? 'Disable Maintenance' : '⚠️ Maintenance Mode' }}
                                        </button>
                                    </form>
                                </div>

                                <!-- Bulk All Users -->
                                <div class="flex items-center gap-1">
                                    <form method="POST" action="{{ route('features.bulk-enable') }}">
                                        @csrf
                                        <input type="hidden" name="feature" value="{{ $fKey }}">
                                        <button class="px-2.5 py-1 text-[11px] font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 rounded">
                                            Bulk All ON
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('features.bulk-disable') }}">
                                        @csrf
                                        <input type="hidden" name="feature" value="{{ $fKey }}">
                                        <button class="px-2.5 py-1 text-[11px] font-semibold bg-gray-100 text-gray-700 hover:bg-gray-200 rounded">
                                            Bulk All OFF
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- SECTION 3: A/B Testing & Persona Simulator Grid --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- A/B Testing Experiments Analytics -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                                <span>🧪</span>
                                <span>A/B Testing Experiments Analytics</span>
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">
                                Experiment: <strong>Checkout Variant Call-To-Action</strong>
                            </p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700">
                            {{ $abStats['total_conversions'] }} Conversions
                        </span>
                    </div>

                    <div class="space-y-3 pt-2">
                        <!-- Control -->
                        <div class="p-3.5 rounded-lg bg-gray-50 border border-gray-200">
                            <div class="flex justify-between items-center text-xs font-semibold mb-1">
                                <span class="text-gray-700">Variant: Control (Standard "Sign Up Free")</span>
                                <span class="text-gray-900 font-bold">{{ $abStats['variants']['control']['conversions'] }} clicks ({{ $abStats['variants']['control']['cr_percent'] }}%)</span>
                            </div>
                            <div class="w-full bg-gray-200 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-gray-500 h-2.5 rounded-full" style="width: {{ $abStats['variants']['control']['cr_percent'] }}%"></div>
                            </div>
                        </div>

                        <!-- Variant A -->
                        <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200">
                            <div class="flex justify-between items-center text-xs font-semibold mb-1">
                                <span class="text-emerald-800">Variant A (Emerald "Start 14-Day Free Trial")</span>
                                <span class="text-emerald-900 font-bold">{{ $abStats['variants']['variant_a']['conversions'] }} clicks ({{ $abStats['variants']['variant_a']['cr_percent'] }}%)</span>
                            </div>
                            <div class="w-full bg-emerald-200 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-emerald-600 h-2.5 rounded-full" style="width: {{ $abStats['variants']['variant_a']['cr_percent'] }}%"></div>
                            </div>
                        </div>

                        <!-- Variant B -->
                        <div class="p-3.5 rounded-lg bg-indigo-50 border border-indigo-200">
                            <div class="flex justify-between items-center text-xs font-semibold mb-1">
                                <span class="text-indigo-800">Variant B (Indigo "Get Instant Pro Access")</span>
                                <span class="text-indigo-900 font-bold">{{ $abStats['variants']['variant_b']['conversions'] }} clicks ({{ $abStats['variants']['variant_b']['cr_percent'] }}%)</span>
                            </div>
                            <div class="w-full bg-indigo-200 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $abStats['variants']['variant_b']['cr_percent'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- "View As User" Persona Simulator -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <span>🔍</span>
                            <span>"View As User" Persona Simulator</span>
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Select any user to simulate and inspect their exact resolved flags and A/B variant in real time.
                        </p>
                    </div>

                    <form method="GET" action="{{ route('features.index') }}" class="flex gap-2">
                        <select name="simulate_user_id" class="text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 flex-grow" onchange="this.form.submit()">
                            <option value="">-- Choose User to Simulate --</option>
                            @foreach($allUsers as $u)
                                <option value="{{ $u->id }}" {{ $simulatedUser && $simulatedUser->id === $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->email }}) {{ $u->is_admin ? '⭐ Admin' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-xs font-semibold hover:bg-indigo-700">
                            Simulate
                        </button>
                    </form>

                    @if($simulatedUser)
                        <div class="p-4 rounded-xl bg-indigo-50/70 border border-indigo-200 space-y-3">
                            <div class="flex items-center justify-between border-b border-indigo-200 pb-2">
                                <div>
                                    <div class="font-bold text-sm text-indigo-900">{{ $simulatedUser->name }}</div>
                                    <div class="text-xs text-indigo-700">{{ $simulatedUser->email }}</div>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $simulatedUser->is_admin ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                                    {{ $simulatedUser->is_admin ? 'ADMIN PERSONA' : 'STANDARD USER' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs">
                                @foreach($simulatedFlags as $sKey => $sData)
                                    <div class="p-2.5 rounded-lg bg-white border border-indigo-100 flex items-center justify-between">
                                        <span class="font-medium text-gray-700">{{ $sData['name'] }}</span>
                                        @if($sKey === 'checkout_variant')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-800">
                                                {{ strtoupper($sData['value']) }}
                                            </span>
                                        @elseif($sData['is_active'])
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                ACTIVE
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">
                                                OFF
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="p-6 text-center text-gray-400 border border-dashed border-gray-200 rounded-xl">
                            Select a user above to run the live persona flag simulator.
                        </div>
                    @endif
                </div>

            </div>

            {{-- SECTION 4: Individual User Overrides Table --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">User-Specific Override Matrix</h3>
                        <p class="text-xs text-gray-500 mt-1">Granularly override feature states for individual users in the database.</p>
                    </div>

                    <form method="GET" action="{{ route('features.index') }}" class="flex gap-2">
                        <input
                            type="text"
                            name="user_search"
                            value="{{ $userSearch }}"
                            placeholder="Search user name or email..."
                            class="text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                        >
                        <button type="submit" class="px-3 py-1.5 bg-gray-800 text-white rounded-lg text-xs font-semibold hover:bg-gray-700">
                            Search
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 font-semibold">
                                <th class="p-4">User</th>
                                @foreach($features as $fKey => $fMeta)
                                    <th class="p-4 text-center">{{ $fMeta['name'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($users as $user)
                                <tr class="hover:bg-gray-50/80 transition">
                                    <td class="p-4">
                                        <div class="font-bold text-gray-900">{{ $user->name }}</div>
                                        <div class="text-gray-500 text-[11px]">{{ $user->email }}</div>
                                        @if($user->is_admin)
                                            <span class="inline-block mt-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-100 text-indigo-700">ADMIN</span>
                                        @endif
                                    </td>

                                    @foreach($features as $fKey => $fMeta)
                                        @php
                                            $val = $userFeatureMatrix[$user->id][$fKey] ?? false;
                                            $isActive = is_bool($val) ? $val : !empty($val);
                                        @endphp
                                        <td class="p-4 text-center">
                                            <div class="flex items-center justify-center gap-1">
                                                @if($isActive)
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                        {{ is_string($val) ? strtoupper($val) : 'ACTIVE' }}
                                                    </span>
                                                    <form method="POST" action="{{ route('features.disable') }}" class="inline">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <input type="hidden" name="feature" value="{{ $fKey }}">
                                                        <button class="px-2 py-0.5 bg-red-100 text-red-700 hover:bg-red-200 rounded text-[10px] font-semibold">
                                                            Turn OFF
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600">OFF</span>
                                                    <form method="POST" action="{{ route('features.enable') }}" class="inline">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <input type="hidden" name="feature" value="{{ $fKey }}">
                                                        <button class="px-2 py-0.5 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 rounded text-[10px] font-semibold">
                                                            Turn ON
                                                        </button>
                                                    </form>
                                                @endif

                                                <form method="POST" action="{{ route('features.reset-override') }}" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                    <input type="hidden" name="feature" value="{{ $fKey }}">
                                                    <button class="px-1.5 py-0.5 text-gray-400 hover:text-gray-700 text-[10px]" title="Reset to default">
                                                        ↺
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-gray-400">No users found matching query.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-100">
                    {{ $users->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>