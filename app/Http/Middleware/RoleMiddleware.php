<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next, $role)
    {
        if (! Auth::check()) {
            Log::warning('Unauthorized access attempt: User not authenticated', [
                'ip' => $request->ip(),
                'url' => $request->url(),
            ]);
            abort(403, 'Unauthorized: User not authenticated');
        }

        $user = Auth::user();

        $user->load('roles');

        if ($user->roles->isEmpty()) {
            Log::warning("User {$user->id} ({$user->email}) has no roles assigned, redirecting to login", [
                'requested_role' => $role,
            ]);
            Auth::logout();
            abort(403, "Unauthorized: User does not have the required '{$role}' role. Please contact administrator.");
        }

        if (! $user->hasRole($role)) {
            Log::warning("User {$user->id} ({$user->email}) tried to access '{$role}' but doesn't have permission", [
                'user_roles' => $user->roles->pluck('slug')->toArray(),
                'requested_role' => $role,
                'ip' => $request->ip(),
            ]);
            abort(403, "Unauthorized: User does not have '{$role}' role");
        }

        return $next($request);
    }
}
