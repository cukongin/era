<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();

        // 1. Admin Override
        if ($user->role === 'admin') {
            return $next($request);
        }

        // 2. Parsed Roles Handling (Robust)
        // Check if roles were passed as a single string "role1,role2" due to some config
        $parsedRoles = [];
        foreach ($roles as $role) {
            if (str_contains($role, ',')) {
                $exploded = explode(',', $role);
                $parsedRoles = array_merge($parsedRoles, $exploded);
            } else {
                $parsedRoles[] = $role;
            }
        }
        
        // Clean up
        $parsedRoles = array_map('trim', $parsedRoles);

        if (in_array($user->role, $parsedRoles)) {
            return $next($request);
        }

        \Log::warning("Unauthorized Access: User {$user->email} (Role: {$user->role}) tried to access " . $request->path() . " requiring " . implode(',', $parsedRoles));

        return abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
    }
}
