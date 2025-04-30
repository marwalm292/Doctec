<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

const PROFILE_PATH = '/profile';

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Debug route to check user role
Route::get('/debug-role', function () {
    if (Auth::check()) {
        $user = Auth::user();
        $role = $user->role;
        $isAdmin = $role === 'admin';
        
        if (is_object($role)) {
            $roleValue = $role->value;
            $isAdmin = $roleValue === 'admin';
            return [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $roleValue,
                'role_type' => get_class($role),
                'is_admin' => $isAdmin,
                'role_comparison' => $roleValue === 'admin' ? 'true' : 'false'
            ];
        }
        
        return [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role,
            'is_admin' => $isAdmin,
            'role_comparison' => $role === 'admin' ? 'true' : 'false'
        ];
    }
    
    return ['error' => 'Not logged in'];
})->middleware('auth');

// Admin routes with admin middleware
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
});

Route::get('/dashboard', function () {
    return Inertia::render('User/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get(PROFILE_PATH, [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch(PROFILE_PATH, [ProfileController::class, 'update'])->name('profile.update');
    Route::delete(PROFILE_PATH, [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require_once __DIR__.'/auth.php';
