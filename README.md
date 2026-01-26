#  PHP_Laravel12_Pennant

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-red" />
  <img src="https://img.shields.io/badge/PHP-8.2-blue" />
  <img src="https://img.shields.io/badge/Feature%20Flags-Laravel%20Pennant-green" />
  <img src="https://img.shields.io/badge/Auth-Laravel%20Breeze-orange" />
</p>

---

##  Overview

This project demonstrates how to implement a **Feature Toggle System** using **Laravel 12** and **Laravel Pennant**.

Feature flags allow you to enable or disable features in your application **without redeploying code**.

### In this project:

*  Admin users can enable/disable a **New Dashboard UI**
*  Normal users always see the **Old Dashboard UI**
*  The system is powered by **Laravel Pennant**

---

##  Features

* Laravel 12 Application Setup
* Authentication with Laravel Breeze
* Feature Flags using Laravel Pennant
* Admin-based feature control
* Real-time UI switching (no redeploy required)
* Database-driven feature overrides

---

##  Project Folder Structure

```
app/
 ├── Features/
 │    └── NewDashboard.php
 │
 ├── Http/
 │    └── Controllers/
 │         └── DashboardController.php
 │
resources/
 └── views/
      └── dashboard.blade.php

routes/
 └── web.php

database/
 └── migrations/
      └── add_is_admin_to_users_table.php
```

---

#  Installation Guide

##  Install Laravel Project

```bash
composer create-project laravel/laravel pennant-demo
```

### Configure Database in `.env`

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

Run:

```bash
php artisan migrate
```

---

##  Install Authentication (Laravel Breeze)

```bash
composer require laravel/breeze --dev

php artisan breeze:install

npm install

npm run dev

php artisan migrate
```

---

##  Install Laravel Pennant

```bash
composer require laravel/pennant
php artisan vendor:publish --tag=pennant-migrations
php artisan migrate
```

This creates the **features** table.

---

##  Add Admin Column to Users Table

```bash
php artisan make:migration add_is_admin_to_users_table
```

**database/migrations/add_is_admin_to_users_table.php**

```php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_admin')->default(false);
});
```

Run migration:

```bash
php artisan migrate
```

Make a user admin:

```bash
php artisan tinker
```

```php
$user = App\Models\User::first();
$user->is_admin = 1;
$user->save();
```

---

##  Create Feature Class

```bash
php artisan pennant:feature NewDashboard
```

**app/Features/NewDashboard.php**

```php
<?php

namespace App\Features;

class NewDashboard
{
    public function resolve(mixed $scope): mixed
    {
        // Default logic: Only admins see new dashboard
        return $scope?->is_admin === 1;
    }
}

```

---

##  Dashboard Controller

```bash
php artisan make:controller DashboardController
```

**app/Http/Controllers/DashboardController.php**

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use App\Features\NewDashboard;

class DashboardController extends Controller
{
    public function enableFeature(Request $request)
    {
        Feature::for($request->user())->activate(NewDashboard::class);
        return back()->with('success', 'New Dashboard Enabled!');
    }

    public function disableFeature(Request $request)
    {
        Feature::for($request->user())->deactivate(NewDashboard::class);
        return back()->with('success', 'New Dashboard Disabled!');
    }
}

```

---

##  Routes

**routes/web.php**

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::post('/feature/on', [DashboardController::class, 'enableFeature'])->name('feature.on');
    Route::post('/feature/off', [DashboardController::class, 'disableFeature'])->name('feature.off');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', function () {
        return view('profile.edit');
    })->name('profile.edit');
});
require __DIR__.'/auth.php';

```

---

##  Dashboard View

**resources/views/dashboard.blade.php**

```blade
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

```

---

##  Clear Old Feature Cache

```bash
php artisan pennant:clear
php artisan optimize:clear
```

---

##  Run the Project

```bash
php artisan serve
```

Open in browser:

```
http://127.0.0.1:8000/dashboard
```

---

##  Output

 Enable Dashboard:-

 <img width="1294" height="581" alt="Screenshot 2026-01-26 120332" src="https://github.com/user-attachments/assets/ae7414db-6e27-409d-a474-0949e0f631b9" />

 Disable Dashboard:-

 <img width="1325" height="563" alt="Screenshot 2026-01-26 120344" src="https://github.com/user-attachments/assets/4586dc00-0e0d-41fe-9b1a-fa6426b37205" />


---



