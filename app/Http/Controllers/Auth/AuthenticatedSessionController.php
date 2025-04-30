<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        Session::regenerate();

        // Get the authenticated user using Auth facade
        $user = Auth::user();
        
        if ($user) {
            $userRole = $user->role;
            $adminRole = 'admin';
            
            // Log user role for debugging
            Log::info("User authenticated: " . $user->email);
            Log::info("User role: " . (is_object($userRole) ? $userRole->value : $userRole));
            
            // Handle enum value if needed
            $isAdmin = false;
            if (is_object($userRole)) {
                $isAdmin = $userRole->value === $adminRole;
                Log::info("Enum role check: " . $userRole->value . " === " . $adminRole . " = " . ($isAdmin ? 'true' : 'false'));
            } else {
                $isAdmin = $userRole === $adminRole;
                Log::info("String role check: " . $userRole . " === " . $adminRole . " = " . ($isAdmin ? 'true' : 'false'));
            }
            
            if ($isAdmin) {
                Log::info("Redirecting to admin dashboard");
                return redirect()->intended(route('admin.dashboard', absolute: false));
            }
        }
        
        Log::info("Redirecting to user dashboard");
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
