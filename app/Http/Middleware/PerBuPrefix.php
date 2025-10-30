<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PerBuPrefix
{
    public function handle(Request $request, Closure $next)
    {
        // Get BU path from session (e.g. 'gpjagna', 'hatchery', etc.)
        $dashboardPath = session('dashboard_path');

        // If no dashboard_path in session, just continue
        if (!$dashboardPath) {
            return $next($request);
        }

        // Normalize both paths
        $currentPath = trim($request->path(), '/');
        $dashboardPath = trim($dashboardPath, '/');

        // ⚙️ Skip redirect for:
        // - Already prefixed URLs
        // - Static assets (js, css, images, etc.)
        // - API or login/logout routes
        $excludedPrefixes = ['api', 'storage', 'js', 'css', 'images', 'login', 'logout', 'sanctum', 'broadcasting', 'pusher'];

        foreach ($excludedPrefixes as $prefix) {
            if (str_starts_with($currentPath, $prefix)) {
                return $next($request);
            }
        }

        // If current URL doesn’t start with the BU path, redirect to prefixed one
        // if (!str_starts_with($currentPath, $dashboardPath)) {
        //     $newPath = $dashboardPath . '/' . $currentPath;
        //     return redirect($newPath);
        // }

        return $next($request);
    }
}
