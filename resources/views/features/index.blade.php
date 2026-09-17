<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Feature Flag Management
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Manage Laravel Pennant feature flags and user-specific rollouts.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">

                <a
                    href="{{ route('features.export') }}"
                    class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-green-700"
                >
                    📥 Export CSV
                </a>

                <a
                    href="{{ route('features.audit') }}"
                    class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-gray-700"
                >
                    🔐 Audit History
                </a>

            </div>

        </div>

    </x-slot>


    <div class="py-10">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success --}}

            @if(session('success'))

                <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">

                    {{ session('success') }}

                </div>

            @endif


            {{-- Errors --}}

            @if($errors->any())

                <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg">

                    <ul class="list-disc list-inside">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            @endif


            {{-- Statistics --}}

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">

                <div class="bg-white rounded-lg shadow p-5">

                    <p class="text-sm text-gray-500">
                        Total Features
                    </p>

                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        {{ $totalFeatures }}
                    </p>

                </div>


                <div class="bg-white rounded-lg shadow p-5">

                    <p class="text-sm text-gray-500">
                        Active Features
                    </p>

                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $activeFeatures }}
                    </p>

                </div>


                <div class="bg-white rounded-lg shadow p-5">

                    <p class="text-sm text-gray-500">
                        Inactive Features
                    </p>

                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ $inactiveFeatures }}
                    </p>

                </div>


                <div class="bg-white rounded-lg shadow p-5">

                    <p class="text-sm text-gray-500">
                        User Overrides
                    </p>

                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $userOverrides }}
                    </p>

                </div>


                <div class="bg-white rounded-lg shadow p-5">

                    <p class="text-sm text-gray-500">
                        Audit Activities
                    </p>

                    <p class="text-3xl font-bold text-purple-600 mt-2">
                        {{ $totalAuditActivities }}
                    </p>

                </div>

            </div>


            {{-- Search & Filters --}}

            <div class="bg-white rounded-lg shadow p-6 mb-8">

                <h3 class="text-lg font-semibold text-gray-800 mb-4">
                    🔎 Search & Filters
                </h3>

                <form
                    method="GET"
                    action="{{ route('features.index') }}"
                >

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        {{-- Feature Search --}}

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Feature Search
                            </label>

                            <input
                                type="text"
                                name="feature_search"
                                value="{{ request('feature_search') }}"
                                placeholder="Search feature..."
                                class="w-full rounded-md border-gray-300 shadow-sm"
                            >

                        </div>


                        {{-- User Search --}}

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                User Search
                            </label>

                            <input
                                type="text"
                                name="user_search"
                                value="{{ request('user_search') }}"
                                placeholder="Name or email..."
                                class="w-full rounded-md border-gray-300 shadow-sm"
                            >

                        </div>


                        {{-- Status --}}

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Feature Status
                            </label>

                            <select
                                name="status"
                                class="w-full rounded-md border-gray-300 shadow-sm"
                            >

                                <option value="">
                                    All Features
                                </option>

                                <option
                                    value="enabled"
                                    @selected(request('status') === 'enabled')
                                >
                                    Enabled
                                </option>

                                <option
                                    value="disabled"
                                    @selected(request('status') === 'disabled')
                                >
                                    Disabled
                                </option>

                            </select>

                        </div>

                    </div>


                    <div class="flex gap-2 mt-4">

                        <button
                            type="submit"
                            class="px-5 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                        >
                            Apply Filters
                        </button>

                        <a
                            href="{{ route('features.index') }}"
                            class="px-5 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                        >
                            Reset
                        </a>

                    </div>

                </form>

            </div>


            {{-- Available Features --}}

            <div class="bg-white rounded-lg shadow overflow-hidden mb-8">

                <div class="p-6 border-b border-gray-200">

                    <h3 class="text-lg font-semibold text-gray-800">
                        Available Feature Flags
                    </h3>

                </div>


                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Feature
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Description
                                </th>

                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                    Status
                                </th>

                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                    Active Users
                                </th>

                            </tr>

                        </thead>


                        <tbody class="bg-white divide-y divide-gray-200">

                            @forelse($features as $feature)

                                <tr>

                                    <td class="px-6 py-4">

                                        <div class="font-semibold text-gray-800">
                                            {{ $feature['name'] }}
                                        </div>

                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $feature['key'] }}
                                        </div>

                                    </td>


                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        {{ $feature['description'] }}
                                    </td>


                                    <td class="px-6 py-4 text-center">

                                        @if($feature['active'])

                                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Enabled
                                            </span>

                                        @else

                                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                Disabled
                                            </span>

                                        @endif

                                    </td>


                                    <td class="px-6 py-4 text-center">

                                        <span class="font-bold text-indigo-600">
                                            {{ $feature['active_user_count'] }}
                                        </span>

                                        <span class="text-gray-400">
                                            /
                                            {{ $feature['total_user_count'] }}
                                        </span>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="4"
                                        class="px-6 py-8 text-center text-gray-500"
                                    >
                                        No features found.
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>


            {{-- Bulk Operations --}}

            <div class="bg-white rounded-lg shadow p-6 mb-8">

                <h3 class="text-lg font-semibold text-gray-800">
                    ⚡ Bulk Feature Operations
                </h3>

                <p class="text-sm text-gray-500 mt-1 mb-5">
                    Select a feature and multiple users.
                </p>


                <form
                    method="POST"
                    id="bulkFeatureForm"
                >

                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        {{-- Feature --}}

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Feature
                            </label>

                            <select
                                name="feature"
                                required
                                class="w-full rounded-md border-gray-300 shadow-sm"
                            >

                                @foreach($features as $feature)

                                    <option value="{{ $feature['key'] }}">
                                        {{ $feature['name'] }}
                                    </option>

                                @endforeach

                            </select>

                        </div>


                        {{-- Users --}}

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Users
                            </label>

                            <div class="border rounded-md max-h-48 overflow-y-auto p-3">

                                @forelse($users as $user)

                                    <label class="flex items-center gap-2 py-2">

                                        <input
                                            type="checkbox"
                                            name="user_ids[]"
                                            value="{{ $user->id }}"
                                            class="rounded border-gray-300 user-checkbox"
                                        >

                                        <span class="text-sm text-gray-700">

                                            {{ $user->name }}

                                            <span class="text-gray-400">
                                                ({{ $user->email }})
                                            </span>

                                        </span>

                                    </label>

                                @empty

                                    <p class="text-sm text-gray-500">
                                        No users found.
                                    </p>

                                @endforelse

                            </div>

                        </div>

                    </div>


                    <div class="flex flex-wrap gap-3 mt-5">

                        <button
                            type="submit"
                            formaction="{{ route('features.bulk-enable') }}"
                            class="px-5 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                        >
                            ✅ Bulk Enable
                        </button>

                        <button
                            type="submit"
                            formaction="{{ route('features.bulk-disable') }}"
                            class="px-5 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                        >
                            🚫 Bulk Disable
                        </button>

                    </div>

                </form>

            </div>


            {{-- User Targeting --}}

            <div class="bg-white rounded-lg shadow overflow-hidden">

                <div class="p-6 border-b border-gray-200">

                    <h3 class="text-lg font-semibold text-gray-800">
                        👤 User-Specific Feature Targeting
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Enable, disable or reset individual user overrides.
                    </p>

                </div>


                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    User
                                </th>

                                @foreach($features as $feature)

                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                        {{ $feature['name'] }}
                                    </th>

                                @endforeach

                            </tr>

                        </thead>


                        <tbody class="bg-white divide-y divide-gray-200">

                            @forelse($users as $user)

                                <tr>

                                    <td class="px-6 py-4">

                                        <div class="font-medium text-gray-800">
                                            {{ $user->name }}
                                        </div>

                                        <div class="text-sm text-gray-500">
                                            {{ $user->email }}
                                        </div>

                                        @if($user->is_admin)

                                            <span class="inline-flex mt-1 px-2 py-1 text-xs rounded bg-purple-100 text-purple-800">
                                                Admin
                                            </span>

                                        @else

                                            <span class="inline-flex mt-1 px-2 py-1 text-xs rounded bg-gray-100 text-gray-700">
                                                User
                                            </span>

                                        @endif

                                    </td>


                                    @foreach($features as $feature)

                                        @php

                                            $isActive =
                                                Laravel\Pennant\Feature::for($user)
                                                    ->active($feature['class']);

                                        @endphp


                                        <td class="px-6 py-4">

                                            <div class="flex flex-col items-center gap-2">

                                                @if($isActive)

                                                    <span class="text-xs font-semibold text-green-600">
                                                        Enabled
                                                    </span>

                                                @else

                                                    <span class="text-xs font-semibold text-red-600">
                                                        Disabled
                                                    </span>

                                                @endif


                                                <div class="flex flex-wrap justify-center gap-2">

                                                    {{-- Enable --}}

                                                    <form
                                                        method="POST"
                                                        action="{{ route('features.enable') }}"
                                                    >

                                                        @csrf

                                                        <input
                                                            type="hidden"
                                                            name="feature"
                                                            value="{{ $feature['key'] }}"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="user_id"
                                                            value="{{ $user->id }}"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="px-3 py-1 text-xs rounded bg-green-600 text-white hover:bg-green-700"
                                                        >
                                                            Enable
                                                        </button>

                                                    </form>


                                                    {{-- Disable --}}

                                                    <form
                                                        method="POST"
                                                        action="{{ route('features.disable') }}"
                                                    >

                                                        @csrf

                                                        <input
                                                            type="hidden"
                                                            name="feature"
                                                            value="{{ $feature['key'] }}"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="user_id"
                                                            value="{{ $user->id }}"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="px-3 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700"
                                                        >
                                                            Disable
                                                        </button>

                                                    </form>


                                                    {{-- Reset --}}

                                                    <form
                                                        method="POST"
                                                        action="{{ route('features.reset-override') }}"
                                                    >

                                                        @csrf

                                                        <input
                                                            type="hidden"
                                                            name="feature"
                                                            value="{{ $feature['key'] }}"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="user_id"
                                                            value="{{ $user->id }}"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="px-3 py-1 text-xs rounded bg-gray-700 text-white hover:bg-gray-800"
                                                        >
                                                            Reset
                                                        </button>

                                                    </form>

                                                </div>

                                            </div>

                                        </td>

                                    @endforeach

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="{{ count($features) + 1 }}"
                                        class="px-6 py-8 text-center text-gray-500"
                                    >
                                        No users found.
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>