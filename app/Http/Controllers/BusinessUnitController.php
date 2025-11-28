<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\MasterfileModels\User;
use App\Services\BusinessUnitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Exists;
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

    public function getBusinessUnitList(Request $request)
    {
        $request->validate([
            'username' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => "$request->username username not found"
            ]);
        }

        $response = [
            'success' => true,
            'message' => "$request->username username found"
        ];

        $buIds = $user->bu_assign;

        if (is_string($buIds)) {
            $buIds = json_decode($buIds, true);
        }

        if (!is_array($buIds)) {
            $response = [
                'error' => true,
                'message' => 'Invalid BU assignment format'
            ];
        }

        $businessUnits = BusinessUnit::whereIn('bu_id', $buIds)->get();

        return response()->json($response + [
            'data' => $businessUnits
        ]);
    }

    public function setSelectedDatabase(Request $request)
    {
        $request->validate([
            'business_unit' => 'required|integer',
        ]);

        // 1️⃣ Session keys for BU info
        $sessionKeys = [
            'dashboard_path',
            'bu_id',
            'database',
            'host',
            'port',
            'username',
            'password',
            'business_unit',
            'business_unit_code',
        ];

        // 2️⃣ ALWAYS clear old BU session first
        session()->forget($sessionKeys);

        // 3️⃣ Prepare BU config based on switch-case
        switch ($request->business_unit) {
            case 17:
                $businessUnit = [
                    'bu_id' => 17,
                    'dashboard_path' => 'dressingplant',
                    'name' => 'Dressing Plant AR',
                    'database' => 'dressing_plant_ar',
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'username' => 'root',
                    'password' => '',
                    'business_unit' => 'DRESSING PLANT',
                    'business_unit_code' => 'DRSP UBAY-AG007',
                ];
                break;

            case 18:
                $businessUnit = [
                    'bu_id' => 18,
                    'dashboard_path' => 'rendering',
                    'name' => 'Rendering AR',
                    'database' => 'rendering_ar',
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'username' => 'root',
                    'password' => '',
                    'business_unit' => 'RENDERING',
                    'business_unit_code' => 'REND UBAY-AG008',
                ];
                break;

            case 19:
                $businessUnit = [
                    'bu_id' => 19,
                    'dashboard_path' => 'feedmill',
                    'name' => 'Feedmill AR',
                    'database' => 'feedmill_ar',
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'username' => 'root',
                    'password' => '',
                    'business_unit' => 'FEEDMILL',
                    'business_unit_code' => 'FDML UBAY-AG009',
                ];
                break;

            case 20:
                $businessUnit = [
                    'bu_id' => 20,
                    'dashboard_path' => 'growout',
                    'name' => 'Growout AR',
                    'database' => 'growout_ar',
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'username' => 'root',
                    'password' => '',
                    'business_unit' => 'GROWOUT',
                    'business_unit_code' => 'GRWT UBAY-AG010',
                ];
                break;

            case 21:
                $businessUnit = [
                    'bu_id' => 21,
                    'dashboard_path' => 'demofarm',
                    'name' => 'Demo Farm AR',
                    'database' => 'demofarm_ar',
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'username' => 'root',
                    'password' => '',
                    'business_unit' => 'DEMO FARM',
                    'business_unit_code' => 'DMO UBAY-AG011',
                ];
                break;

            case 22:
                $businessUnit = [
                    'bu_id' => 22,
                    'dashboard_path' => 'mfimanurefertilizerubay',
                    'name' => 'Mfi Manure Fertilizer Ubay AR',
                    'database' => 'mfi_manure_fertilizer_ubay_ar',
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'username' => 'root',
                    'password' => '',
                    'business_unit' => 'MFI MANURE FERTILIZER UBAY',
                    'business_unit_code' => 'FERP UBAY-AG012',
                ];
                break;

            case 46:
                $businessUnit = [
                    'bu_id' => 46,
                    'dashboard_path' => 'meatprocessingplant',
                    'name' => 'Meat Processing Plant AR',
                    'database' => 'meat_processing_plant_ar',
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'username' => 'root',
                    'password' => '',
                    'business_unit' => 'MEAT PROCESSING PLANT',
                    'business_unit_code' => 'MPP-UBAY-AG017',
                ];
                break;

            case 23:
                $businessUnit = [
                    'bu_id' => 23,
                    'dashboard_path' => 'piggeryuntaga',
                    'name' => 'Piggery Untaga AR',
                    'database' => 'piggery_untaga_ar',
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'username' => 'root',
                    'password' => '',
                    'business_unit' => 'PIGGERY UNTAGA',
                    'business_unit_code' => 'PGRY ALC-AG013',
                ];
                break;

            case 41:
                $businessUnit = [
                    'bu_id' => 41,
                    'dashboard_path' => 'farmersmarket',
                    'name' => 'Farmers Market AR',
                    'database' => 'farmers_market_ar',
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'username' => 'root',
                    'password' => '',
                    'business_unit' => 'FARMERS MARKET',
                    'business_unit_code' => 'FARMERS MARKET',
                ];
                break;

            case 42:
                $businessUnit = [
                    'bu_id' => 42,
                    'dashboard_path' => 'mfifertilizercortes',
                    'name' => 'MFI Fertilizer Cortes AR',
                    'database' => 'mfi_fertilizer_cortes_ar',
                    'host' => '127.0.0.1',
                    'port' => '3306',
                    'username' => 'root',
                    'password' => '',
                    'business_unit' => 'MFI FERTILIZER CORTES',
                    'business_unit_code' => 'FERP CORTES-AG015',
                ];
                break;

            default:
                return response()->json([
                    'error' => true,
                    'message' => 'Business unit not found.',
                ], 404);
        }

        // 4️⃣ Prepare temporary DB config
        $tempConfig = [
            'driver'    => 'mysql',
            'host'      => $businessUnit['host'],
            'port'      => $businessUnit['port'],
            'database'  => $businessUnit['database'],
            'username'  => $businessUnit['username'],
            'password'  => $businessUnit['password'],
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];

        Config::set('database.connections.test_bu', $tempConfig);

        // 5️⃣ Test the connection
        try {
            DB::purge('test_bu');
            DB::connection('test_bu')->getPdo();
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => "Connection failed to {$businessUnit['business_unit']} ({$businessUnit['business_unit_code']})",
                'exception' => $e->getMessage(),
            ], 500);
        }

        // 6️⃣ Set session on success
        session($businessUnit);

        // 7️⃣ Apply real connection
        Config::set('database.connections.business_unit', $tempConfig);
        DB::purge('business_unit');
        DB::reconnect('business_unit');

        return response()->json([
            'success' => true,
            'message' => "Connected to {$businessUnit['business_unit']} successfully",
        ]);
    }

    // This setup is for authenticating user using the main db regardless of what bu db selected 
    // public function getBusinessUnitConfig(int $businessUnitsId): array
    // {
    //     $businessUnits = [
    //         17 => [
    //             'bu_id' => 17,
    //             'dashboard_path' => 'dressingplant',
    //             'name' => 'Dressing Plant AR',
    //             'database' => 'dressing_plant_ar',
    //             'host' => '127.0.0.1',
    //             'port' => '3306',
    //             'username' => 'root',
    //             'password' => '',
    //             'business_unit' => 'DRESSING PLANT',
    //             'business_unit_code' => 'DRSP UBAY-AG007',
    //         ],
    //         18 => [
    //             'bu_id' => 18,
    //             'dashboard_path' => 'rendering',
    //             'name' => 'Rendering AR',
    //             'database' => 'rendering_ar',
    //             'host' => '127.0.0.1',
    //             'port' => '3306',
    //             'username' => 'root',
    //             'password' => '',
    //             'business_unit' => 'RENDERING',
    //             'business_unit_code' => 'REND UBAY-AG008',
    //         ],


    //         19 => [
    //             'bu_id' => 19,
    //             'dashboard_path' => 'feedmill',
    //             'name' => 'Feedmill AR',
    //             'database' => 'feedmill_ar',
    //             'host' => '127.0.0.1',
    //             'port' => '3306',
    //             'username' => 'root',
    //             'password' => '',
    //             'business_unit' => 'FEEDMILL',
    //             'business_unit_code' => 'FDML UBAY-AG009',
    //         ],

    //         20 => [
    //             'bu_id' => 20,
    //             'dashboard_path' => 'growout',
    //             'name' => 'Growout AR',
    //             'database' => 'growout_ar',
    //             'host' => '127.0.0.1',
    //             'port' => '3306',
    //             'username' => 'root',
    //             'password' => '',
    //             'business_unit' => 'GROWOUT',
    //             'business_unit_code' => 'GRWT UBAY-AG010',
    //         ],


    //         21 => [
    //             'bu_id' => 21,
    //             'dashboard_path' => 'demofarm',
    //             'name' => 'Demo Farm AR',
    //             'database' => 'demofarm_ar',
    //             'host' => '127.0.0.1',
    //             'port' => '3306',
    //             'username' => 'root',
    //             'password' => '',
    //             'business_unit' => 'DEMO FARM',
    //             'business_unit_code' => 'DMO UBAY-AG011',
    //         ],

    //         22 => [
    //             'bu_id' => 22,
    //             'dashboard_path' => 'mfimanurefertilizerubay',
    //             'name' => 'Mfi Manure Fertilizer Ubay AR',
    //             'database' => 'mfi_manure_fertilizer_ubay_ar',
    //             'host' => '127.0.0.1',
    //             'port' => '3306',
    //             'username' => 'root',
    //             'password' => '',
    //             'business_unit' => 'MFI MANURE FERTILIZER UBAY',
    //             'business_unit_code' => 'FERP UBAY-AG012',
    //         ],


    //         46 => [
    //             'bu_id' => 46,
    //             'dashboard_path' => 'meatprocessingplant',
    //             'name' => 'Meat Processing Plant AR',
    //             'database' => 'meat_processing_plant_ar',
    //             'host' => '127.0.0.1',
    //             'port' => '3306',
    //             'username' => 'root',
    //             'password' => '',
    //             'business_unit' => 'MEAT PROCESSING PLANT',
    //             'business_unit_code' => 'MPP-UBAY-AG017',
    //         ],


    //         23 => [
    //             'bu_id' => 23,
    //             'dashboard_path' => 'piggeryuntaga',
    //             'name' => 'Piggery Untaga AR',
    //             'database' => 'piggery_untaga_ar',
    //             'host' => '127.0.0.1',
    //             'port' => '3306',
    //             'username' => 'root',
    //             'password' => '',
    //             'business_unit' => 'PIGGERY UNTAGA',
    //             'business_unit_code' => 'PGRY ALC-AG013',
    //         ],


    //         41 => [
    //             'bu_id' => 41,
    //             'dashboard_path' => 'farmersmarket',
    //             'name' => 'Farmers Market AR',
    //             'database' => 'farmers_market_ar',
    //             'host' => '127.0.0.1',
    //             'port' => '3306',
    //             'username' => 'root',
    //             'password' => '',
    //             'business_unit' => 'FARMERS MARKET',
    //             'business_unit_code' => 'FARMERS MARKET',
    //         ],


    //         42 => [
    //             'bu_id' => 42,
    //             'dashboard_path' => 'mfifertilizercortes',
    //             'name' => 'MFI Fertilizer Cortes AR',
    //             'database' => 'mfi_fertilizer_cortes_ar',
    //             'host' => '127.0.0.1',
    //             'port' => '3306',
    //             'username' => 'root',
    //             'password' => '',
    //             'business_unit' => 'MFI FERTILIZER CORTES',
    //             'business_unit_code' => 'FERP CORTES-AG015',
    //         ],

    //     ];
    //     return $businessUnits[$businessUnitsId] ?? [];
    // }
}
