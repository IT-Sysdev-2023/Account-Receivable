<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Services\BusinessUnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\AssignOp\Concat;

class BusinessUnitController extends Controller
{
    public function businessUnits()
    {
        $businessUnits = BusinessUnit::orderBy('business_unit', 'asc')->get();

        // $result = $businessUnits->map(function ($bu) {

        //     // default status
        //     $status = 'No Connection';

        //     try {
        //         // TEMP DB CONNECTION CHECK
        //         Config::set('database.connections.temp_bu_check', [
        //             'driver' => 'mysql',
        //             'host' => $bu->host,
        //             'port' => $bu->port,
        //             'database' => $bu->database,
        //             'username' => $bu->username,
        //             'password' => $bu->password,
        //             'charset' => 'utf8mb4',
        //             'collation' => 'utf8mb4_unicode_ci',
        //         ]);

        //         DB::purge('temp_bu_check');
        //         DB::reconnect('temp_bu_check');

        //         // check if connection works
        //         DB::connection('temp_bu_check')->getPdo();

        //         // If no exception → connection works
        //         $status = 'Connected';
        //     } catch (\Exception $e) {
        //         $status = 'No Connection';
        //     }

        //     // add the status attribute
        //     $bu->status = $status;

        //     return $bu;
        // });

        return response()->json([
            'success' => true,
            'data' => $businessUnits
        ]);
    }


    public function selectedBu($id, BusinessUnitService $buService)
    {
        $result = $buService->setBusinessUnitSessionAndConnection($id);

        if (isset($result['error']) && $result['error'] === true) {
            return response()->json([
                'error' => true,
                'message' => $result['message'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully connected to {$result['business_unit']->business_unit}"
        ]);
    }


    public function currentDatabase()
    {
        $dbName = session('database');
        $host = session('host');
        $port = session('port');
        $businessUnit = session('business_unit');
        $businessUnitCode = session('business_unit_code');


        return response()->json([
            'success' => true,
            'selected' => $dbName ? true : false,
            'database' => $dbName,
            'host' => $host,
            'port' => $port,
            'business_unit' => ucwords($businessUnit . ' - ' . $businessUnitCode),
        ]);
    }


    // This is the old for selected database manually 
    // public function selectedBu($id)
    // {
    //     $businessUnit = BusinessUnit::findOrFail($id);

    //     // 1. Always forget the old session
    //     session()->forget([
    //         'dashboard_path',
    //         'bu_id',
    //         'database',
    //         'host',
    //         'port',
    //         'username',
    //         'password',
    //         'business_unit',
    //         'business_unit_code'
    //     ]);

    //     // 2. Clear previous DB connection from cache
    //     DB::purge('business_unit');

    //     // 3. Set new config dynamically
    //     Config::set('database.connections.business_unit', [
    //         'driver' => 'mysql',
    //         'dashboard_path' => $businessUnit->dashboard_path,
    //         'host' => $businessUnit->host,
    //         'port' => $businessUnit->port,
    //         'database' => $businessUnit->database,
    //         'username' => $businessUnit->username,
    //         'password' => $businessUnit->password,
    //         'bu_id' => $businessUnit->bu_id,
    //         'business_unit' => $businessUnit->business_unit,
    //         'business_unit_code' => $businessUnit->business_unit_code,
    //         'charset' => 'utf8mb4',
    //         'collation' => 'utf8mb4_unicode_ci',
    //         'prefix' => '',
    //         'strict' => false,
    //         'engine' => null,
    //     ]);

    //     try {
    //         // 4. Force Laravel to reconnect using new config
    //         DB::reconnect('business_unit');

    //         // Test the new connection
    //         DB::connection('business_unit')->getPdo();

    //         // 5. Set new session values after success
    //         session([
    //             'dashboard_path' => $businessUnit->dashboard_path,
    //             'bu_id' => $businessUnit->bu_id,
    //             'database' => $businessUnit->database,
    //             'host' => $businessUnit->host,
    //             'port' => $businessUnit->port,
    //             'username' => $businessUnit->username,
    //             'password' => $businessUnit->password,
    //             'business_unit' => $businessUnit->business_unit,
    //             'business_unit_code' => $businessUnit->business_unit_code,
    //         ]);

    //         return response()->json([
    //             'success' => true,
    //             'message' => "Successfully connected to $businessUnit->business_unit - $businessUnit->business_unit_code",
    //         ]);
    //     } catch (\Exception $e) {
    //         // If connection fails, clear session again
    //         session()->forget([
    //             'dashboard_path',
    //             'bu_id',
    //             'database',
    //             'host',
    //             'port',
    //             'username',
    //             'password',
    //             'business_unit',
    //             'business_unit_code'
    //         ]);

    //         return response()->json([
    //             'error' => "Unable to connect to $businessUnit->business_unit - $businessUnit->business_unit_code business unit",
    //             'details' => $e->getMessage()
    //         ], 500);
    //     }
    // }
}
