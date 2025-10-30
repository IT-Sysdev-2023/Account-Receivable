<?php

namespace App\Services;

use App\Models\AccCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncAccCodeService
{
    public function sync(GlobalApiServices $globalApi)
    {
        try {
            // From global api configuration

            $response = $globalApi->AccountCodeSync();

            if (!$response['success']) {
                return response()->json(['error' => $response['message']], 500);
            }

            $apiAccCodes = $response['data']['gl_account_code'] ?? [];

            if (empty($apiAccCodes)) {
                return response()->json(['message' => 'No customers found in API'], 200);
            }

            $syncedIds = [];

            DB::transaction(function () use ($apiAccCodes, &$syncedIds) {
                foreach ($apiAccCodes as $apiAccCode) {
                    $syncedIds[] = $apiAccCode['gl_account_id'];

                    $data = [
                        'gl_account_navcode' => $apiAccCode['gl_account_navcode'],
                        'gl_account_name' => $apiAccCode['gl_account_name'],
                        'setup_by' => $apiAccCode['setup_by'],
                        'status' => $apiAccCode['status'] ?? false,
                        'business_unit' => $apiAccCode['business_unit'],
                    ];

                    AccCode::updateOrCreate(
                        ['gl_account_id' => $apiAccCode['gl_account_id']],
                        $data
                    );
                }

                // Delete local acc code not present in the API
                AccCode::whereNotIn('gl_account_id', $syncedIds)->delete();
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Acc Code sync failed: ' . $e->getMessage());
            return false;
        }
    }
}
