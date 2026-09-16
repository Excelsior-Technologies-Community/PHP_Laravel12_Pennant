<x-app-layout>

<x-slot name="header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Feature Flag Management
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Manage Laravel Pennant feature flags and user-specific rollouts.
            </p>
        </div>

        <a
            href="{{ route('features.audit') }}"
            class="inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
        >
            🔐 Audit History
        </a>

    </div>
</x-slot>

    <div class="py-10">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-300 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-100 border border-red-300 text-red-800 rounded-lg">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Statistics --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">
                        Total Features
                    </p>

                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        {{ $totalFeatures }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">
                        Active Features
                    </p>

                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $activeFeatures }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">
                        Inactive Features
                    </p>

                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ $inactiveFeatures }}
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">
                        User Overrides
                    </p>

                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $userOverrides }}
                    </p>
                </div>

            </div>

            {{-- Feature Management --}}
            <div class="bg-white rounded-lg shadow overflow-hidden mb-8">

                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Available Feature Flags
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Control application features using Laravel Pennant.
                    </p>
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
                                    Current Status
                                </th>

                            </tr>

                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">

                            @foreach($features as $feature)

                                <tr>

                                    <td class="px-6 py-4 whitespace-nowrap">

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

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

            {{-- User Feature Targeting --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">

                <div class="p-6 border-b border-gray-200">

                    <h3 class="text-lg font-semibold text-gray-800">
                        User-Specific Feature Targeting
                    </h3>

                    <p class="text-sm text-gray-500 mt-1">
                        Enable or disable individual features for specific users.
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

                            @foreach($users as $user)

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
                                            $isActive = Laravel\Pennant\Feature::for($user)->active($feature['class']);
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

                                                <div class="flex gap-2">

                                                    <form method="POST"
                                                          action="{{ route('features.enable') }}">

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

                                                    <form method="POST"
                                                          action="{{ route('features.disable') }}">

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

                                                </div>

                                            </div>

                                        </td>

                                    @endforeach

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</x-app-layout>