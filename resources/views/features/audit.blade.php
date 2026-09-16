<x-app-layout>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                🔐 Feature Flag Audit History
            </h2>

            <a
                href="{{ route('features.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
            >
                ← Feature Management
            </a>
        </div>
    </x-slot>

    <div class="py-8">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Success Message --}}
            @if (session('success'))
                <div class="mb-6 rounded-lg bg-green-100 border border-green-300 text-green-800 px-5 py-4">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Statistics --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm text-gray-500">
                            Total Activities
                        </p>

                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            {{ $totalActivities }}
                        </p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm text-gray-500">
                            Features Enabled
                        </p>

                        <p class="mt-2 text-3xl font-bold text-green-600">
                            {{ $enabledActivities }}
                        </p>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <p class="text-sm text-gray-500">
                            Features Disabled
                        </p>

                        <p class="mt-2 text-3xl font-bold text-red-600">
                            {{ $disabledActivities }}
                        </p>
                    </div>
                </div>

            </div>

            {{-- Filters --}}
            <div class="bg-white shadow-sm sm:rounded-lg mb-6">

                <div class="p-6">

                    <form
                        method="GET"
                        action="{{ route('features.audit') }}"
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4"
                    >

                        {{-- Search --}}
                        <div class="lg:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Search
                            </label>

                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="User, admin, feature, IP..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        {{-- Feature --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Feature
                            </label>

                            <select
                                name="feature"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">All Features</option>

                                @foreach ($features as $feature)
                                    <option
                                        value="{{ $feature }}"
                                        @selected(request('feature') === $feature)
                                    >
                                        {{ $feature }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Action --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Action
                            </label>

                            <select
                                name="action"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">All Actions</option>

                                <option
                                    value="enabled"
                                    @selected(request('action') === 'enabled')
                                >
                                    Enabled
                                </option>

                                <option
                                    value="disabled"
                                    @selected(request('action') === 'disabled')
                                >
                                    Disabled
                                </option>
                            </select>
                        </div>

                        {{-- From Date --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                From
                            </label>

                            <input
                                type="date"
                                name="date_from"
                                value="{{ request('date_from') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        {{-- To Date --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                To
                            </label>

                            <input
                                type="date"
                                name="date_to"
                                value="{{ request('date_to') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                        </div>

                        {{-- Buttons --}}
                        <div class="lg:col-span-6 flex gap-3">

                            <button
                                type="submit"
                                class="inline-flex items-center px-5 py-2.5 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                            >
                                🔎 Apply Filters
                            </button>

                            <a
                                href="{{ route('features.audit') }}"
                                class="inline-flex items-center px-5 py-2.5 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300"
                            >
                                Reset
                            </a>

                        </div>

                    </form>

                </div>

            </div>

            {{-- Audit Table --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">

                <div class="p-6 border-b border-gray-200">

                    <h3 class="text-lg font-semibold text-gray-900">
                        Feature Activity
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        Track all feature enable and disable activities.
                    </p>

                </div>

                @if ($audits->count())

                    <div class="overflow-x-auto">

                        <table class="min-w-full divide-y divide-gray-200">

                            <thead class="bg-gray-50">

                                <tr>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Admin
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Target User
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Feature
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Action
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        IP Address
                                    </th>

                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date & Time
                                    </th>

                                </tr>

                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200">

                                @foreach ($audits as $audit)

                                    <tr class="hover:bg-gray-50">

                                        {{-- Admin --}}
                                        <td class="px-6 py-4 whitespace-nowrap">

                                            @if ($audit->admin)

                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $audit->admin->name }}
                                                </div>

                                                <div class="text-sm text-gray-500">
                                                    {{ $audit->admin->email }}
                                                </div>

                                            @else

                                                <span class="text-gray-400">
                                                    Deleted Admin
                                                </span>

                                            @endif

                                        </td>

                                        {{-- Target User --}}
                                        <td class="px-6 py-4 whitespace-nowrap">

                                            @if ($audit->user)

                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $audit->user->name }}
                                                </div>

                                                <div class="text-sm text-gray-500">
                                                    {{ $audit->user->email }}
                                                </div>

                                            @else

                                                <span class="text-gray-400">
                                                    Deleted User
                                                </span>

                                            @endif

                                        </td>

                                        {{-- Feature --}}
                                        <td class="px-6 py-4 whitespace-nowrap">

                                            <span class="font-medium text-gray-900">
                                                {{ $audit->feature }}
                                            </span>

                                        </td>

                                        {{-- Action --}}
                                        <td class="px-6 py-4 whitespace-nowrap">

                                            @if ($audit->action === 'enabled')

                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    🟢 Enabled
                                                </span>

                                            @else

                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    🔴 Disabled
                                                </span>

                                            @endif

                                        </td>

                                        {{-- IP --}}
                                        <td class="px-6 py-4 whitespace-nowrap">

                                            <span class="text-sm text-gray-600">
                                                {{ $audit->ip_address ?? 'N/A' }}
                                            </span>

                                        </td>

                                        {{-- Date --}}
                                        <td class="px-6 py-4 whitespace-nowrap">

                                            <div class="text-sm text-gray-900">
                                                {{ $audit->created_at->format('d M Y') }}
                                            </div>

                                            <div class="text-sm text-gray-500">
                                                {{ $audit->created_at->format('h:i A') }}
                                            </div>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                    {{-- Pagination --}}
                    <div class="p-6">
                        {{ $audits->links() }}
                    </div>

                @else

                    <div class="p-10 text-center">

                        <div class="text-5xl mb-4">
                            🔐
                        </div>

                        <h3 class="text-lg font-semibold text-gray-900">
                            No Audit Activity Found
                        </h3>

                        <p class="mt-2 text-sm text-gray-500">
                            Feature enable and disable activities will appear here.
                        </p>

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>