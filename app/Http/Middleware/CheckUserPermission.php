<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckUserPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $roleId, $ability)
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $permission = $user->permissions()->where('role_id', $roleId)->first();

        if (!$permission) {
            abort(403, 'Forbidden: No permission record found.');
        }

        $field = 'can_' . $ability;

        if (!$permission->$field) {
            abort(403, "Forbidden: You do not have [$field] access.");
        }

        return $next($request);
    }
}
