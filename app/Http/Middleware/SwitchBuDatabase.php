<?php

namespace App\Http\Middleware;

use App\Models\BusinessUnit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class SwitchBuDatabase
{
    public function handle(Request $request, Closure $next): Response
    {
        $connectionName = 'business_unit';
        
        // API mode: dynamic database per BU (based on dashboard_path in the URL)
        if ($request->is('api/*')) {

            // First, try route parameter, then query parameter
            $buPath = $request->route('bu') ?? $request->query('bu');

            if ($buPath) {
                // Find BU by its ID or path
                $businessUnit = BusinessUnit::where('bu_id', $buPath)->first();

                if (!$businessUnit) {
                    return response()->json([
                        'error' => true,
                        'message' => "Unknown business unit '$buPath'.",
                    ], 404);
                }

                // Set up dynamic connection
                Config::set("database.connections.$connectionName", [
                    'driver' => 'mysql',
                    'host' => $businessUnit->host,
                    'port' => $businessUnit->port,
                    'database' => $businessUnit->database,
                    'username' => $businessUnit->username,
                    'password' => $businessUnit->password,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => false,
                    'engine' => null,
                ]);

                // Try connecting to verify it's valid
                try {
                    DB::connection($connectionName)->getPdo();
                    config(['database.default' => $connectionName]);
                } catch (Throwable $e) {
                    return response()->json([
                        'error' => true,
                        'message' => "Failed to connect to database '{$businessUnit->database}'.",
                        'details' => $e->getMessage(),
                    ], 500);
                }
            } else {
                return response()->json([
                    'error' => true,
                    'message' => "BU parameter is required for this API.",
                ], 400);
            }
        }

        // 🔹 Web mode: uses session-based database (for authenticated web users)
        elseif (session()->has('database')) {
            $connectionName = 'business_unit';

            Config::set("database.connections.$connectionName", [
                'driver' => 'mysql',
                'host' => session('host'),
                'port' => session('port'),
                'database' => session('database'),
                'username' => session('username'),
                'password' => session('password'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => false,
                'engine' => null,
            ]);

            config(['database.default' => $connectionName]);
        }

        return $next($request);
    }
}
