<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if(session('success'))
                        <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    <h3 class="text-lg font-bold mb-4">Laravel Pennant Feature Toggle</h3>

                    <form method="POST" action="{{ route('feature.on') }}" class="inline">
                        @csrf
                        <button class="bg-green-600 text-white px-4 py-2 rounded">
                            Enable New Dashboard
                        </button>
                    </form>

                    <form method="POST" action="{{ route('feature.off') }}" class="inline ml-2">
                        @csrf
                        <button class="bg-red-600 text-white px-4 py-2 rounded">
                            Disable New Dashboard
                        </button>
                    </form>

                    <hr class="my-6">

                    @feature(App\Features\NewDashboard::class)
                        <div class="p-4 bg-green-100 border border-green-400 rounded">
                            🆕 <strong>NEW DASHBOARD UI ENABLED</strong>
                            <p>This section is controlled using Laravel Pennant.</p>
                        </div>
                    @endfeature

                    @unlessfeature(App\Features\NewDashboard::class)
                        <div class="p-4 bg-red-100 border border-red-400 rounded">
                            📊 <strong>OLD DASHBOARD UI</strong>
                            <p>The feature is currently disabled.</p>
                        </div>
                    @endfeature


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
