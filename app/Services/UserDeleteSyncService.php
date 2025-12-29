<?php

namespace App\Services;

use App\Models\BusinessUnit;
use App\Models\MasterfileModels\User as MasterfileModelsUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserDeleteSyncService
{
    /**
     * Remove a user from a specific BU.
     * Update bu_assign in main DB and all BU DBs.
     * If no BU remains, delete user completely.
     */
    public function removeUserFromBU(MasterfileModelsUser $user, int $bu_id_to_remove)
    {
        // Decode bu_assign and remove the specified BU
        $buIds = json_decode($user->bu_assign, true) ?? [];
        $buIds = array_filter($buIds, fn($id) => $id != $bu_id_to_remove);

        // 1️⃣ Update or delete user in the main DB explicitly
        try {
            $mainUser = MasterfileModelsUser::on('mysql') // main DB connection
                ->where('employee_id', $user->employee_id)
                ->first();

            if ($mainUser) {
                if (!empty($buIds)) {
                    $mainUser->bu_assign = json_encode(array_values($buIds));
                    $mainUser->save();
                    Log::info("Main DB: Updated bu_assign for user {$user->employee_id}");
                } else {
                    // No BU left → delete user and permissions in main DB
                    DB::transaction(function () use ($mainUser) {
                        DB::table('permissions')->where('user_id', $mainUser->id)->delete();
                        $mainUser->delete();
                    });
                    Log::info("Main DB: Deleted user {$user->employee_id} as no BU left");
                }
            }
        } catch (\Exception $e) {
            Log::error("Main DB update failed for user {$user->employee_id}: {$e->getMessage()}");
        }

        // 2️⃣ Loop through all BU databases dynamically
        $businessUnits = BusinessUnit::all();

        foreach ($businessUnits as $bu) {
            $conn = 'bu_' . $bu->bu_id;

            // Configure dynamic BU connection
            config()->set("database.connections.$conn", [
                'driver'    => 'mysql',
                'host'      => $bu->host,
                'port'      => $bu->port,
                'database'  => $bu->database,
                'username'  => $bu->username,
                'password'  => $bu->password,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);

            DB::disconnect($conn);
            DB::purge($conn);

            try {
                DB::connection($conn)->getPdo();

                if (!empty($buIds)) {
                    DB::connection($conn)
                        ->table('users')
                        ->where('employee_id', $user->employee_id)
                        ->update([
                            'bu_assign' => json_encode(array_values($buIds)),
                            'updated_at' => now(),
                        ]);
                    Log::info("BU {$bu->business_unit}: Updated bu_assign for user {$user->employee_id}");
                } else {
                    DB::transaction(function () use ($conn, $user) {
                        DB::connection($conn)->table('permissions')->where('user_id', $user->id)->delete();
                        DB::connection($conn)->table('users')->where('employee_id', $user->employee_id)->delete();
                    });
                    Log::info("BU {$bu->business_unit}: Deleted user {$user->employee_id} as no BU left");
                }
            } catch (\Exception $e) {
                Log::warning("User BU sync skipped for BU {$bu->business_unit}: {$e->getMessage()}");
            }
        }
    }
}
