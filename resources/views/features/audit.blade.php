<x-app-layout>

    <x-slot name="header">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div>

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Feature Audit History
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Track feature flag changes and administrator activity.
                </p>

            </div>


            <a
                href="{{ route('features.index') }}"
                class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 text-white rounded-md text-xs font-semibold uppercase tracking-widest hover:bg-indigo-700"
            >
                🎛️ Feature Management
            </a>

        </div>

    </x-slot>


    <div class="py-10">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">


            {{-- Statistics --}}

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

                <div class="bg-white rounded-lg shadow p-5">

                    <p class="text-sm text-gray-500">
                        Total Activities
                    </p>

                    <p class="text-3xl font-bold text-gray-800 mt-2">
                        {{ $totalActivities }}
                    </p>

                </div>


                <div class="bg-white rounded-lg shadow p-5">

                    <p class="text-sm text-gray-500">
                        Enabled
                    </p>

                    <p class="text-3xl font-bold text-green-600 mt-2">
                        {{ $enabledActivities }}
                    </p>

                </div>


                <div class="bg-white rounded-lg shadow p-5">

                    <p class="text-sm text-gray-500">
                        Disabled
                    </p>

                    <p class="text-3xl font-bold text-red-600 mt-2">
                        {{ $disabledActivities }}
                    </p>

                </div>


                <div class="bg-white rounded-lg shadow p-5">

                    <p class="text-sm text-gray-500">
                        Reset
                    </p>

                    <p class="text-3xl font-bold text-blue-600 mt-2">
                        {{ $resetActivities }}
                    </p>

                </div>

            </div>


            {{-- Filters --}}

            <div class="bg-white rounded-lg shadow p-6 mb-8">

                <h3 class="text-lg font-semibold text-gray-800 mb-5">
                    🔎 Audit Filters
                </h3>


                <form
                    method="GET"
                    action="{{ route('features.audit') }}"
                >

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                        {{-- Search --}}

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Search
                            </label>

                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Feature, user, IP..."
                                class="w-full rounded-md border-gray-300 shadow-sm"
                            >

                        </div>


                        {{-- Feature --}}

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Feature
                            </label>

                            <select
                                name="feature"
                                class="w-full rounded-md border-gray-300 shadow-sm"
                            >

                                <option value="">
                                    All Features
                                </option>

                                @foreach($features as $feature)

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
                                class="w-full rounded-md border-gray-300 shadow-sm"
                            >

                                <option value="">
                                    All Actions
                                </option>

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

                                <option
                                    value="reset"
                                    @selected(request('action') === 'reset')
                                >
                                    Reset
                                </option>

                            </select>

                        </div>


                        {{-- Date Preset --}}

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Date Range
                            </label>

                            <select
                                name="date_preset"
                                class="w-full rounded-md border-gray-300 shadow-sm"
                            >

                                <option value="">
                                    All Time
                                </option>

                                <option
                                    value="today"
                                    @selected($datePreset === 'today')
                                >
                                    Today
                                </option>

                                <option
                                    value="7_days"
                                    @selected($datePreset === '7_days')
                                >
                                    Last 7 Days
                                </option>

                                <option
                                    value="30_days"
                                    @selected($datePreset === '30_days')
                                >
                                    Last 30 Days
                                </option>

                            </select>

                        </div>

                    </div>


                    {{-- Custom Dates --}}

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">

                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                From
                            </label>

                            <input
                                type="date"
                                name="date_from"
                                value="{{ $dateFrom }}"
                                class="w-full rounded-md border-gray-300 shadow-sm"
                            >

                        </div>


                        <div>

                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                To
                            </label>

                            <input
                                type="date"
                                name="date_to"
                                value="{{ $dateTo }}"
                                class="w-full rounded-md border-gray-300 shadow-sm"
                            >

                        </div>

                    </div>


                    <div class="flex gap-2 mt-5">

                        <button
                            type="submit"
                            class="px-5 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                        >
                            Apply Filters
                        </button>

                        <a
                            href="{{ route('features.audit') }}"
                            class="px-5 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300"
                        >
                            Reset
                        </a>

                    </div>

                </form>

            </div>


            {{-- Audit Table --}}

            <div class="bg-white rounded-lg shadow overflow-hidden">

                <div class="p-6 border-b border-gray-200">

                    <h3 class="text-lg font-semibold text-gray-800">
                        Activity History
                    </h3>

                </div>


                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200">

                        <thead class="bg-gray-50">

                            <tr>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Date
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Admin
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    User
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Feature
                                </th>

                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                    Action
                                </th>

                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    IP Address
                                </th>

                            </tr>

                        </thead>


                        <tbody class="bg-white divide-y divide-gray-200">

                            @forelse($audits as $audit)

                                <tr>

                                    <td class="px-6 py-4 text-sm text-gray-600">

                                        {{ $audit->created_at?->format('d M Y H:i:s') }}

                                    </td>


                                    <td class="px-6 py-4">

                                        <div class="font-medium text-gray-800">
                                            {{ $audit->admin?->name ?? 'Deleted User' }}
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            {{ $audit->admin?->email ?? '-' }}
                                        </div>

                                    </td>


                                    <td class="px-6 py-4">

                                        <div class="font-medium text-gray-800">
                                            {{ $audit->user?->name ?? 'Deleted User' }}
                                        </div>

                                        <div class="text-xs text-gray-500">
                                            {{ $audit->user?->email ?? '-' }}
                                        </div>

                                    </td>


                                    <td class="px-6 py-4 text-sm font-medium text-gray-800">

                                        {{ $audit->feature }}

                                    </td>


                                    <td class="px-6 py-4 text-center">

                                        @if($audit->action === 'enabled')

                                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Enabled
                                            </span>

                                        @elseif($audit->action === 'disabled')

                                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                Disabled
                                            </span>

                                        @else

                                            <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Reset
                                            </span>

                                        @endif

                                    </td>


                                    <td class="px-6 py-4 text-sm text-gray-600">

                                        {{ $audit->ip_address ?? '-' }}

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="6"
                                        class="px-6 py-10 text-center text-gray-500"
                                    >
                                        No audit activities found.
                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>


                {{-- Numeric Pagination --}}

                @if($audits->hasPages())

                    <div class="p-5 border-t border-gray-200 flex flex-wrap gap-2">

                        @for($page = 1; $page <= $audits->lastPage(); $page++)

                            @if($page == $audits->currentPage())

                                <span class="px-3 py-2 bg-indigo-600 text-white rounded-md text-sm">
                                    {{ $page }}
                                </span>

                            @else

                                <a
                                    href="{{ $audits->url($page) }}"
                                    class="px-3 py-2 bg-gray-100 text-gray-700 rounded-md text-sm hover:bg-gray-200"
                                >
                                    {{ $page }}
                                </a>

                            @endif

                        @endfor

                    </div>

                @endif

            </div>

        </div>

    </div>

</x-app-layout>