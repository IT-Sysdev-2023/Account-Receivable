<?php

namespace App\Services;

use App\Models\MasterfileModels\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncCustomerService
{
    public function sync(GlobalApiServices $globalApi)
    {
        try {

            // From global api configuration
            $response = $globalApi->CustomerSync();

            if (!$response['success']) {
                return response()->json(['error' => $response['message']], 500);
            }

            $apiCustomers = $response['data']['customers'] ?? [];

            if (empty($apiCustomers)) {
                return response()->json(['message' => 'No customers found in API'], 200);
            }

            $syncedIds = [];

            DB::transaction(function () use ($apiCustomers, &$syncedIds) {
                foreach ($apiCustomers as $apiCustomer) {
                    $syncedIds[] = $apiCustomer['cus_id'];

                    $existingCustomer = Customer::where('cus_id', $apiCustomer['cus_id'])->first();

                    $data = [
                        'cus_code' => $apiCustomer['cus_code'],
                        'cus_name' => $apiCustomer['cus_name'],
                        'cus_type' => $apiCustomer['cus_type'],
                        'cus_price_group' => $apiCustomer['cus_price_group'],
                        'cus_address' => $apiCustomer['cus_address'] ?? 'N/A',
                        'cus_tin' => $apiCustomer['cus_tin'] ?? "",
                        'cus_currency' => $apiCustomer['cus_currency'] ?? null,
                        'cus_bu' => $apiCustomer['cus_bu'] ?? null,
                        'nav_code' => $apiCustomer['nav_code'] ?? null,
                        'credit_limit' => $apiCustomer['credit_limit'] ?? 0,
                        'payment_terms' => $apiCustomer['payment_terms'] ?? null,
                        'non_trade' => $apiCustomer['cus_trade_type'] === 'NON-TRADE',
                        'applies_shrinkage' => $apiCustomer['applies_shrinkage'] ?? false,
                        'editable_wht' => $apiCustomer['editable_wht'] ?? false,
                        'journal_voucher' => $apiCustomer['journal_voucher'] ?? false,
                        'gen_posting' => $apiCustomer['gen_posting'] ?? null,
                        'cus_posting' => $apiCustomer['cus_posting'] ?? null,
                        'vat_posting' => $apiCustomer['vat_posting'] ?? null,
                        'cus_status' => $apiCustomer['cus_status'] ?? null,
                        'setup_by' => $apiCustomer['setup_by'] ?? 'system',
                    ];

                    // Add advance_payment_balance only if the customer is new
                    if (!$existingCustomer) {
                        $data['advanced_payment_balance'] = 0.00;
                    }

                    Customer::updateOrCreate(
                        ['cus_id' => $apiCustomer['cus_id']],
                        $data
                    );
                }

                // Delete local customers not present in the API
                Customer::whereNotIn('cus_id', $syncedIds)->delete();
            });

            return true;
        } catch (\Exception $e) {
            Log::error('Customer sync failed: ' . $e->getMessage());
            return false;
        }
    }
}
