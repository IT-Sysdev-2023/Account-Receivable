<?php

namespace App\Services;

use App\Models\BusinessUnit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserSyncService
{
    /**
     * Sync specific users to all BU databases.
     * @param array $users
     */
    public function syncUsersToAllBU(array $users)
    {
        if (empty($users)) {
            return;
        }

        $businessUnits = BusinessUnit::all();

        foreach ($businessUnits as $bu) {

            // Build dynamic BU DB config
            $buConfig = [
                'driver'    => 'mysql',
                'host'      => $bu->host,
                'port'      => $bu->port,
                'database'  => $bu->database,
                'username'  => $bu->username,
                'password'  => $bu->password,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ];

            config(['database.connections.bu_' . $bu->bu_id => $buConfig]);
            $buConnection = 'bu_' . $bu->bu_id;

            // test connection
            try {
                DB::purge($buConnection);
                DB::connection($buConnection)->getPdo();
            } catch (\Exception $e) {
                Log::warning("Skipping BU {$bu->business_unit}: connection failed.");
                continue;
            }

            foreach ($users as $u) {

                $userData = [
                    'employee_id' => $u->employee_id,
                    'name'        => $u->name,
                    'username'    => $u->username,
                    'password'    => $u->password,
                    'role'        => $u->role,
                    'status'      => $u->status,
                    'bu_assign'   => $u->bu_assign,
                    'created_by'  => $u->created_by,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];

                /**
                 * UPSERT – CREATE or UPDATE automatically
                 * Unique key: employee_id
                 * Columns that update on conflict:
                 */
                DB::connection($buConnection)
                    ->table('users')
                    ->upsert(
                        [$userData],
                        ['employee_id'],     // unique key
                        [
                            'name',
                            'username',
                            'password',
                            'role',
                            'status',
                            'bu_assign',
                            'updated_at'
                        ]
                    );
            }

            Log::info("Synced user(s) to BU {$bu->business_unit} successfully.");
        }
    }
}
