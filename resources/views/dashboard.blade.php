<x-app-layout>

    <x-slot name="header">

        <div class="flex items-center justify-between">

            <div>

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Dashboard') }}
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Laravel Pennant Feature Toggle Demo
                </p>

            </div>

            @if(Auth::user()->is_admin)

                <a
                    href="{{ route('features.index') }}"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                >
                    Manage Features
                </a>

            @endif

        </div>

    </x-slot>

    <div class="py-12">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))

                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>

            @endif

            {{-- Existing New Dashboard Feature --}}

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">

                <div class="p-6 text-gray-900">

                    <h3 class="text-lg font-bold mb-4">
                        New Dashboard Feature
                    </h3>

                    @if(Auth::user()->is_admin)

                        <div class="mb-4">

                            <form
                                method="POST"
                                action="{{ route('feature.on') }}"
                                class="inline"
                            >

                                @csrf

                                <button
                                    class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
                                >
                                    Enable New Dashboard
                                </button>

                            </form>

                            <form
                                method="POST"
                                action="{{ route('feature.off') }}"
                                class="inline ml-2"
                            >

                                @csrf

                                <button
                                    class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700"
                                >
                                    Disable New Dashboard
                                </button>

                            </form>

                        </div>

                    @endif

                    <hr class="my-6">

                    @feature(App\Features\NewDashboard::class)

                        <div class="p-4 bg-green-100 border border-green-400 rounded-lg">

                            <div class="text-green-800 font-bold">
                                🆕 NEW DASHBOARD UI ENABLED
                            </div>

                            <p class="text-sm text-green-700 mt-1">
                                This dashboard experience is controlled using Laravel Pennant.
                            </p>

                        </div>

                    @else

                        <div class="p-4 bg-red-100 border border-red-400 rounded-lg">

                            <div class="text-red-800 font-bold">
                                📊 OLD DASHBOARD UI
                            </div>

                            <p class="text-sm text-red-700 mt-1">
                                The new dashboard feature is currently disabled.
                            </p>

                        </div>

                    @endfeature

                </div>

            </div>


            {{-- New Reports --}}

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">

                <div class="p-6">

                    <h3 class="text-lg font-bold mb-4">
                        Reports Feature
                    </h3>

                    @feature(App\Features\NewReports::class)

                        <div class="p-4 bg-blue-100 border border-blue-400 rounded-lg">

                            <div class="font-bold text-blue-800">
                                📈 NEW REPORTS ENABLED
                            </div>

                            <p class="text-sm text-blue-700 mt-1">
                                The new reports and analytics module is available.
                            </p>

                        </div>

                    @else

                        <div class="p-4 bg-gray-100 border border-gray-300 rounded-lg">

                            <div class="font-bold text-gray-700">
                                📊 REPORTS FEATURE DISABLED
                            </div>

                            <p class="text-sm text-gray-500 mt-1">
                                Ask an administrator to enable the new reports feature.
                            </p>

                        </div>

                    @endfeature

                </div>

            </div>


            {{-- Beta Profile --}}

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6">

                    <h3 class="text-lg font-bold mb-4">
                        Beta Profile Feature
                    </h3>

                    @feature(App\Features\BetaProfile::class)

                        <div class="p-4 bg-purple-100 border border-purple-400 rounded-lg">

                            <div class="font-bold text-purple-800">
                                🧪 BETA PROFILE ENABLED
                            </div>

                            <p class="text-sm text-purple-700 mt-1">
                                You have access to the experimental profile experience.
                            </p>

                        </div>

                    @else

                        <div class="p-4 bg-gray-100 border border-gray-300 rounded-lg">

                            <div class="font-bold text-gray-700">
                                👤 STANDARD PROFILE
                            </div>

                            <p class="text-sm text-gray-500 mt-1">
                                The beta profile feature is not enabled for this user.
                            </p>

                        </div>

                    @endfeature

                </div>

            </div>

        </div>

    </div>

</x-app-layout>