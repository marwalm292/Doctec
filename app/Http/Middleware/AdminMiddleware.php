<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('login');
        }
        
        $user = Auth::user();
        $userRole = $user->role;
        
        // Log the check for debugging
        Log::info("AdminMiddleware checking role for user: " . $user->email);
        
        // Check for admin role - handles both string and enum values
        $isAdmin = false;
        if (is_object($userRole)) {
            $isAdmin = $userRole->value === 'admin';
            Log::info("Role is object with value: " . $userRole->value);
        } else {
            $isAdmin = $userRole === 'admin';
            Log::info("Role is string: " . $userRole);
        }
        
        if ($isAdmin) {
            Log::info("Admin user confirmed, proceeding to admin route");
            return $next($request);
        }
        
        // Not an admin, redirect to user dashboard
        Log::info("Non-admin user redirected to user dashboard");
        return redirect()->route('dashboard');
    }
}