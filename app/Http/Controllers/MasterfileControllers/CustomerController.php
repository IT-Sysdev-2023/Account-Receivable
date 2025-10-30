<?php

namespace App\Http\Controllers\MasterfileControllers;

use App\Http\Controllers\Controller;
use App\Models\MasterfileModels\Customer;
use App\Services\GlobalApiServices;
use App\Services\SyncCustomerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search functionality
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('cus_name', 'like', '%' . $request->search . '%')
                    ->orWhere('cus_code', 'like', '%' . $request->search . '%');
            });
        }

        // Type filters
        if ($request->type_filters) {
            $types = is_array($request->type_filters)
                ? $request->type_filters
                : explode(',', $request->type_filters);
            $query->whereIn('cus_type', $types);
        }

        // Status filters
        if ($request->status_filters) {
            $statuses = is_array($request->status_filters)
                ? $request->status_filters
                : explode(',', $request->status_filters);
            $query->whereIn('cus_status', $statuses);
        }

        // Code sorting
        if ($request->code_sort) {
            $query->orderBy('cus_code', $request->code_sort === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderByRaw("LTRIM(cus_name) ASC");
        }

        return Inertia::render('Customer', [
            'customers' => $query->paginate(10)->withQueryString(),
            'searchTerm' => $request->search,
            'filters' => [
                'code_sort' => $request->code_sort,
                'type_filters' => $request->type_filters ? (is_array($request->type_filters) ? $request->type_filters : explode(',', $request->type_filters)) : [],
                'status_filters' => $request->status_filters
                    ? (is_array($request->status_filters)
                        ? $request->status_filters
                        : explode(',', $request->status_filters))
                    : [],
            ],
        ]);
    }

    public function syncCustomers(SyncCustomerService $syncService, GlobalApiServices $globalApi)
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
                        'cus_address' => $apiCustomer['cus_address'] ?? 'NA',
                        'cus_tin' => $apiCustomer['cus_tin'] ?? "",
                        'cus_currency' => $apiCustomer['cus_currency'] ?? null,
                        'cus_bu' => $apiCustomer['cus_bu'] ?? null,
                        'nav_code' => $apiCustomer['nav_code'] ?? null,
                        'credit_limit' => $apiCustomer['credit_limit'] ?? 0,
                        'payment_terms' => $apiCustomer['payment_terms'] ?? null,
                        'non_trade' => $apiCustomer['cus_trade_type'] === 'NON-TRADE',
                        'applies_shrinkage' => $apiCustomer['applies_shrinkage'] ?? false,
                        'editable_wht' => $apiCustomer['editable_wht'] ?? false,
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

            $syncService->sync($globalApi);

            return response()->json([
                'success' => true,
                // 'message' => 'Successfully synchronized ' . count($syncedIds) . ' customers'
            ]);
        } catch (\Exception $e) {
            Log::error('Customer sync failed: ' . $e->getMessage());
            return response()->json([
                'error' => 'Synchronization failed: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function getCustomerList()
    {
        $customers = Customer::select(['cus_code', 'cus_name'])
            ->get()
            ->toArray();

        return response()->json($customers);
    }

    public function getAllCustomerList()
    {
        $customers = Customer::get();

        return response()->json($customers);
    }

    public function getAllCustomerAdvancePaymenBalanceList()
    {
        $customers = Customer::select('cus_code', 'cus_name', 'advance_payment_balance')->get();

        return response()->json($customers);
    }
}
