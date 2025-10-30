<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\AssignOp\Concat;

class BusinessUnitController extends Controller
{
    public function businessUnits()
    {
        $businessUnits = BusinessUnit::orderBy('business_unit', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $businessUnits
        ]);
    }

    public function selectedBu($id)
    {
        $businessUnit = BusinessUnit::findOrFail($id);

        Config::set('database.connections.business_unit', [
            'driver' => 'mysql',
            'dashboard_path' => $businessUnit->dashboard_path,
            'host' => $businessUnit->host,
            'port' => $businessUnit->port,
            'database' => $businessUnit->database,
            'username' => $businessUnit->username,
            'password' => $businessUnit->password,
            'bu_id' => $businessUnit->bu_id,
            'business_unit' => $businessUnit->business_unit,
            'business_unit_code' => $businessUnit->business_unit_code,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ]);

        try {
            DB::connection('business_unit')->getPdo();

            // This only set session if the connection is successfull
            session([
                'dashboard_path' => $businessUnit->dashboard_path,
                'bu_id' => $businessUnit->bu_id,
                'database' => $businessUnit->database,
                'host' => $businessUnit->host,
                'port' => $businessUnit->port,
                'username' => $businessUnit->username,
                'password' => $businessUnit->password,
                'business_unit' => $businessUnit->business_unit,
                'business_unit_code' => $businessUnit->business_unit_code,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully connected to $businessUnit->business_unit - $businessUnit->business_unit_code",
                'BusinessUnit' => $businessUnit->name,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => "Unable to connect $businessUnit->business_unit - $businessUnit->business_unit_code business unit"
            ], 500);
        }
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
}
