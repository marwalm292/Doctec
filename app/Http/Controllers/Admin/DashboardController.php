<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        // Check if user is admin
        $user = Auth::user();
        if (!$user) {
            Log::warning("No authenticated user found");
            return redirect()->route('login');
        }
        
        $userRole = $user->role;
        
        // Handle both enum and string role types
        $isAdmin = false;
        if (is_object($userRole)) {
            $isAdmin = $userRole->value === 'admin';
            Log::info("Admin check via enum: " . $userRole->value . " = " . ($isAdmin ? 'true' : 'false'));
        } else {
            $isAdmin = $userRole === 'admin';
            Log::info("Admin check via string: " . $userRole . " = " . ($isAdmin ? 'true' : 'false'));
        }
        
        if (!$isAdmin) {
            Log::warning("Non-admin user attempted to access admin dashboard: " . $user->email);
            return redirect()->route('dashboard');
        }
        
        Log::info("Admin user accessing admin dashboard: " . $user->email);
        return Inertia::render('Admin/Dashboard');
    }
}
