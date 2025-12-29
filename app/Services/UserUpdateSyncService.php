<?php

namespace App\Services;

use App\Models\BusinessUnit;
use App\Models\MasterfileModels\Permission as MasterfileModelsPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UserUpdateSyncService
{
    /**
     * Sync updated user to main DB and all BU databases using employee_id as the key
     */
    public function syncUpdatedUser($user)
    {
        // Include main DB along with all BU databases
        $databases = collect(BusinessUnit::all())->push((object)[
            'bu_id' => 'main',
            'business_unit' => 'MAIN',
            'host' => config('database.connections.mysql.host'),
            'port' => config('database.connections.mysql.port'),
            'database' => config('database.connections.mysql.database'),
            'username' => config('database.connections.mysql.username'),
            'password' => config('database.connections.mysql.password'),
        ]);

        foreach ($databases as $db) {

            $conn = 'bu_' . $db->bu_id;

            config()->set("database.connections.$conn", [
                'driver'    => 'mysql',
                'host'      => $db->host,
                'port'      => $db->port,
                'database'  => $db->database,
                'username'  => $db->username,
                'password'  => $db->password,
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);

            DB::disconnect($conn);
            DB::purge($conn);

            try {
                DB::connection($conn)->getPdo();
            } catch (\Exception $e) {
                Log::warning("User update skipped for DB {$db->business_unit}");
                continue;
            }

            // Update user by employee_id
            $updated = DB::connection($conn)
                ->table('users')
                ->where('employee_id', $user->employee_id)
                ->update([
                    'username'    => $user->username,
                    'name'        => $user->name,
                    'password'    => $user->password,
                    'role'        => $user->role,
                    'status'      => $user->status,
                    'bu_assign'   => $user->bu_assign,
                    'created_by'  => $user->created_by,
                    'updated_at'  => now(),
                ]);

            if (!$updated) {
                Log::warning("User {$user->employee_id} not found in DB {$db->business_unit}");
            }

            // Sync permissions
            $permissions = MasterfileModelsPermission::where('user_id', $user->id)->get();

            DB::connection($conn)
                ->table('permissions')
                ->where('user_id', $user->id)
                ->delete();

            foreach ($permissions as $perm) {
                DB::connection($conn)
                    ->table('permissions')
                    ->insert([
                        'user_id'     => $user->id,
                        'role_id'     => $perm->role_id,
                        'can_view'    => $perm->can_view,
                        'can_insert'  => $perm->can_insert,
                        'can_update'  => $perm->can_update,
                        'can_delete'  => $perm->can_delete,
                        'can_print'   => $perm->can_print,
                        'can_tag'     => $perm->can_tag,
                        'can_reprint' => $perm->can_reprint,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
            }

            Log::info("User {$user->employee_id} synced to DB {$db->business_unit}");
        }
    }
}
