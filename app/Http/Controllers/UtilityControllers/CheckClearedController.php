<?php

namespace App\Http\Controllers\UtilityControllers;

use App\Events\NewCreated;
use App\Events\NotificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationsController;
use App\Models\MasterfileModels\Customer;
use App\Models\MasterfileModels\User;
use App\Models\ReportModels\CustomerLedger;
use App\Models\TransactionModels\PaymentDetails;
use App\Models\UtilityModels\CheckCleared;
use App\Models\UtilityModels\CheckClearedItems;
use App\Services\CheckClearedNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Str;

class CheckClearedController extends Controller
{
    public function index(Request $request)
    {
        $query = CheckCleared::query();

        // Search functionality
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                    ->orWhere('clearing_no', 'like', '%' . $request->search . '%');
            });
        }

        // Date sorting
        if ($request->date_start) {
            $query->whereDate('clearing_date', '>=', $request->date_start);
        }

        if ($request->date_end) {
            $query->whereDate('clearing_date', '<=', $request->date_end);
        }

        // Check Type filters
        if ($request->type_filtersCheckType) {
            $types = is_array($request->type_filtersCheckType)
                ? $request->type_filtersCheckType
                : explode(',', $request->type_filtersCheckType);
            $query->whereIn('check_type', $types);
        }

        // Code sorting
        if ($request->code_sort) {
            $query->orderBy('clearing_no', $request->code_sort === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        return Inertia::render('PdcAndDcClearing', [
            'check_clearings' => $query->paginate(10)->withQueryString(),
            'searchTerm' => $request->search,
            'filters' => [
                'code_sort' => $request->code_sort,
                'type_filtersCheckType' => $request->type_filtersCheckType ?
                    (is_array($request->type_filtersCheckType) ?
                        $request->type_filtersCheckType :
                        explode(',', $request->type_filtersCheckType)) :
                    [],
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
            ],
            'broadcastChannel' => 'check_clearings',
        ]);
    }

    public function clearChecks(Request $request, CheckClearedNumberService $checkClearedNumberService)
    {
        // Log::debug($request->payment_details);
        $validated = $request->validate([
            'clearing_no' => 'required|string',
            'transaction_date' => 'required|date',
            'clearing_date' => 'required|date',
            'customer_code' => 'required|string',
            'customer_name' => 'required|string',
            'check_type' => 'required|string',
            'payment_details' => 'required|array',
            'payment_details.*.payment_no' => 'required|string',
            'payment_details.*.check_no' => 'required|string',
            'payment_details.*.type' => 'required|string',
            'payment_details.*.document_no' => 'required|string',
            'payment_details.*.due_date' => 'required|date',
            'payment_details.*.amount' => 'required|numeric',
            'payment_details.*.status' => 'required|string',
            'payment_details.*.remarks' => 'nullable|string'
        ]);

        $notificationsController = new NotificationsController();

        $clrNo = DB::transaction(function () use ($validated, $request, $checkClearedNumberService) {
            $clearingNo = $checkClearedNumberService->generate();
            // Validate the payment number is unique (just in case)
            if (CheckCleared::where('clearing_no', $clearingNo)->exists()) {
                throw ValidationException::withMessages([
                    'clearing_no' => 'Error Please Try Again',
                ]);
            }
            CheckCleared::create([
                'clearing_no' => $clearingNo,
                'transaction_date' => $validated['transaction_date'],
                'clearing_date' => $validated['clearing_date'],
                'check_type' => $validated['check_type'],
                'customer_code' => $validated['customer_code'],
                'customer_name' => $validated['customer_name'],
                'created_by' => $request->user()->name,
            ]);

            $checkClearedItems = array_map(function ($item) use ($clearingNo) {
                return [
                    'clearing_no' => $clearingNo,
                    'payment_no' => $item['payment_no'],
                    'check_no' => $item['check_no'],
                    'document_no' => $item['document_no'],
                    'due_date' => $item['due_date'],
                    'amount' => $item['amount'],
                    'status' => $item['status'],
                    'remarks' => $item['remarks'] ?? null,
                ];
            }, $validated['payment_details']);

            CheckClearedItems::insert($checkClearedItems);

            foreach ($validated['payment_details'] as $payment) {
                // Update the original payment status
                $pd = PaymentDetails::where([
                    'payment_no' => $payment['payment_no'],
                    'check_no'   => $payment['check_no'],
                    'type'       => $payment['type'],
                    'status'     => 'Floating',
                    'document_no' => $payment['document_no'],
                ])->lockForUpdate()->first();

                if ($pd) {
                    $pd->update([
                        'status' => $payment['status'],
                        'clearing_date' => $validated['clearing_date'],
                    ]);
                }

                if ($payment['status'] === 'Cleared') {
                    $ledger = CustomerLedger::where('invoice_number', $payment['document_no'])->where('type', $payment['type'])->firstOrFail();
                    $newAmount = max($ledger->running_balance - $payment['amount'], 0);
                    if ($newAmount < 0) {
                        throw ValidationException::withMessages([
                            'clearing_no' => 'Error Please Try Again',
                        ]);
                    }
                    $newAmountPaid = $ledger->amount_paid + $payment['amount'];
                    $ledger->update([
                        'running_balance' => $newAmount,
                        'amount_paid' => $newAmountPaid,
                    ]);
                }

                if ($payment['status'] === 'Cancelled') {
                    $cust = Customer::where('cus_code', $validated['customer_code'])
                        ->lockForUpdate()
                        ->first();

                    if ($cust && $pd) {
                        $cust->update([
                            'advanced_payment_balance' => $pd->advpy_amount_paid + $cust->advanced_payment_balance,
                        ]);
                    }
                }
            }
            return $clearingNo;
        });
        event(new NewCreated('check_clearing'));

        $notificationsController->index($request);

        $userIds = User::whereIn('role', ['Admin', 'Accounting'])
            ->pluck('id')
            ->unique();

        foreach ($userIds as $userId) {
            $channel = 'notification-update.' . Str::random(20);
            broadcast(new NotificationEvent($userId, $channel));
        }

        session()->put('clearing_number', $clrNo);
        return redirect()->back();
    }

    public function latest()
    {
        return DB::transaction(function () {
            $latestCheckCleared = CheckCleared::lockForUpdate()
                ->orderByDesc('clearing_no')
                ->first();

            $nextNumber = $latestCheckCleared ? $latestCheckCleared->clearing_no + 1 : 25000001;

            return response()->json([
                'next_clearing_no' => $nextNumber,
                'is_new_sequence' => !$latestCheckCleared
            ]);
        });
    }
}
