<?php

namespace App\Services;

use App\Models\BusinessUnit;
use App\Models\MasterfileModels\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class BusinessUnitService
{

    public function checkUserInBusinessUnit($buId, $username, $password)
    {
        $businessUnit = BusinessUnit::where('bu_id', $buId)->first();

        if (!$businessUnit) {
            return [
                'success' => false,
                'message' => 'Selected business unit not found'
            ];
        }

        try {

            // TEMPORARY DB CONNECTION
            Config::set('database.connections.temp_bu', [
                'driver' => 'mysql',
                'host' => $businessUnit->host,
                'port' => $businessUnit->port,
                'database' => $businessUnit->database,
                'username' => $businessUnit->username,
                'password' => $businessUnit->password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);

            DB::purge('temp_bu');
            DB::reconnect('temp_bu');

            // RUN QUERY
            $user = User::on('temp_bu')
                ->where('username', $username)
                ->first();

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Invalid Username or Password for assigned business unit'
                ];
            }
            if (!password_verify($password, $user->password)) {
                return [
                    'success' => false,
                    'message' => 'Invalid Username or Password for assigned business unit'
                ];
            }
            return [
                'success' => true,
                'user' => $user
            ];

        } catch (\Exception $e) {

            return [
                'success' => false,
                'message' => "Unable to connect $businessUnit->business_unit - $businessUnit->business_unit_code database assigned of $username"
            ];
        }
    }

    public function setBusinessUnitSessionAndConnection($buId)
    {
        $businessUnit = BusinessUnit::where('bu_id',$buId)->first();

        if (!$businessUnit) {
            return [
                'error' => true,
                'message' => 'Business unit not found'
            ];
        }

        // This check again if the selected bu database has a connection 
        $tempConnection = "bu_check_temp";

        Config::set("database.connections.$tempConnection", [
            'driver' => 'mysql',
            'host' => $businessUnit->host,
            'port' => $businessUnit->port,
            'database' => $businessUnit->database,
            'username' => $businessUnit->username,
            'password' => $businessUnit->password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        try {
            // Try to connect
            DB::purge($tempConnection);
            DB::connection($tempConnection)->getPdo(); // Will throw exception if failed

        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => "Cannot connect to database: $businessUnit->business_unit - $businessUnit->business_unit_code"
            ];
        }

        // 1. Save to session
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

        // 2. Update Laravel DB Config
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
        // 3. FORCE Laravel to use the new DB settings
        DB::purge('business_unit');
        DB::reconnect('business_unit');

        return [
            'success' => true,
            'business_unit' => $businessUnit
        ];
    }
}
