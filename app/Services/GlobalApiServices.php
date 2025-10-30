<?php

namespace App\Services;

use App\Models\BusinessUnit;
use Illuminate\Support\Facades\Http;

class GlobalApiServices
{
    public function CustomerSync()
    {
        $buId = session('bu_id');

        if (!$buId) {
            return [
                'success' => false,
                'message' => 'No active business unit found in session.'
            ];
        }

        $url = "http://172.16.43.27/cent-farms-invoicing/masterfileController/CustomerController/fetchCustomers?noSession=true&bu={$buId}";

        $response = Http::get($url);

        if ($response->failed()) {
            return [
                'success' => false,
                'message' => 'API request failed',
                'status' => $response->status()
            ];
        }
        return [
            'success' => true,
            'data' => $response->json()
        ];
    }

    public function AccountCodeSync()
    {
        $buId = session('bu_id');
        if (!$buId) {
            return [
                'success' => false,
                'message' => 'No active business unit found in session.'
            ];
        }

        $url = "http://172.16.43.27/cent-farms-invoicing/masterfileController/GlAccountCodeController/fetchGlAccountCode?noSession=true&bu={$buId}";

        $response = Http::get($url);

        if ($response->failed()) {
            return [
                'success' => false,
                'message' => 'API request failed',
                'status' => $response->status()
            ];
        }
        return [
            'success' => true,
            'data' => $response->json()
        ];
    }

    public function GetPaymentAccountCode()
    {
        $buId = session('bu_id');
        if (!$buId) {
            return [
                'success' => false,
                'message' => 'No active business unit found in session.'
            ];
        }
        return "http://172.16.43.27/cent-farms-invoicing/masterfileController/paymentTypeSetupController/fetchPaymentType?noSession=true&bu={$buId}";
    }

    public function PaymentAccountCodeDescription()
    {
        $buId = session('bu_id');
        if (!$buId) {
            return [
                'success' => false,
                'message' => 'No active business unit found in session.'
            ];
        }
        return "http://172.16.43.27/cent-farms-invoicing/masterfileController/paymentTypeSetupController/fetchPaymentType?noSession=true&bu={$buId}";
    }

    // This if fetch to be pass in the front end component ItemPackingModal 
    public function FetchPriceGroup()
    {
        $buId = session('bu_id');
        if (!$buId) {
            return [
                'success' => false,
                'message' => 'No active business unit found in session.'
            ];
        }
        $url = "http://172.16.43.27/cent-farms-invoicing/index.php/masterfileController/PriceGroupController/fetchPriceGroup?noSession=true&bu={$buId}";

        $response = Http::get($url);

        if ($response->failed()) {
            return [
                'success' => false,
                'message' => 'API request failed',
                'status' => $response->status(),
            ];
        }

        return [
            'success' => true,
            'data' => $response->json(),
        ];
    }

    // This one be fetch to AccountCodeList front end component 
    public function AccoundCodeList()
    {
        $buId = session('bu_id');
        if (!$buId) {
            return [
                'success' => false,
                'message' => 'No active business unit found in session.'
            ];
        }
        $url = "http://172.16.43.27/cent-farms-invoicing/masterfileController/GlAccountCodeController/fetchGlAccountCode?noSession=true&bu={$buId}";

        $response = Http::get($url);

        if ($response->failed()) {
            return [
                'success' => false,
                'message' => 'API request failed',
                'status' => $response->status(),
            ];
        }

        return [
            'success' => true,
            'data' => $response->json(),
        ];
    }

    public function CustomerCodeList()
    {
        $buId = session('bu_id');
        if (!$buId) {
            return [
                'success' => false,
                'message' => 'No active business unit found in session.'
            ];
        }
        $url = "http://172.16.43.27/cent-farms-invoicing/masterfileController/CustomerController/fetchCustomers?noSession=true&bu={$buId}";

        $response = Http::get($url);

        if ($response->failed()) {
            return [
                'success' => false,
                'message' => 'API request failed',
                'status' => $response->status(),
            ];
        }

        return [
            'success' => true,
            'data' => $response->json(),
        ];
    }

    // this send status or data to invoice system 
    public function AddAdjustment($adjustmentAmount, $tdsNo)
    {
        $buId = session('bu_id');
        if (!$buId) {
            return [
                'success' => false,
                'message' => 'No active business unit found in session.'
            ];
        }

        $url = "http://172.16.43.27/cent-farms-invoicing/sales-invoice/update/adjustment-sales?bu={$buId}";

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->post($url, [
                'adj_sales' => (string) $adjustmentAmount,
                'tds_no'    => $tdsNo,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Adjustment successfully updated.',
                    'data' => $response->json(),
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'API request failed',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception occurred: ' . $e->getMessage(),
            ];
        }
    }

    public function AddPayment($adjustmentAmount, $tdsNo)
    {
        $buId = session('bu_id');
        if (!$buId) {
            return [
                'success' => false,
                'message' => 'No active business unit found in session.'
            ];
        }

        $url = "http://172.16.43.27/cent-farms-invoicing/sales-invoice/update/ar-payment-status?bu={$buId}";

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->post($url, [
                'ar_payment_status' => (string) $adjustmentAmount,
                'tds_no'    => $tdsNo,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Adjustment successfully updated.',
                    'data' => $response->json(),
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'API request failed',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Exception occurred: ' . $e->getMessage(),
            ];
        }
    }
}
