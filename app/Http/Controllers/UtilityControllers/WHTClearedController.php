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
use App\Models\UtilityModels\WHTCleared;
use App\Models\UtilityModels\WHTClearedItems;
use App\Services\WHTClearedNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Str;

class WHTClearedController extends Controller
{
    public function index(Request $request)
    {
        $query = WHTCleared::query();

        // Search functionality
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                    ->orWhere('wht_clearing_no', 'like', '%' . $request->search . '%');
            });
        }

        // Date sorting
        if ($request->date_start) {
            $query->whereDate('clearing_date', '>=', $request->date_start);
        }

        if ($request->date_end) {
            $query->whereDate('clearing_date', '<=', $request->date_end);
        }

        // Code sorting
        if ($request->code_sort) {
            $query->orderBy('wht_clearing_no', $request->code_sort === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        return Inertia::render('WithHoldingTaxClearing', [
            'wht_clearings' => $query->paginate(10)->withQueryString(),
            'searchTerm' => $request->search,
            'filters' => [
                'code_sort' => $request->code_sort,
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
            ],
            'broadcastChannel' => 'wht_clearings',
        ]);
    }

    public function clearWht(Request $request, WHTClearedNumberService $wHTClearedNumberService)
    {
        $validated = $request->validate([
            'wht_clearing_no' => 'required|string',
            'transaction_date' => 'required|date',
            'clearing_date' => 'required|date',
            'customer_code' => 'required|string',
            'customer_name' => 'required|string',
            'payment_details' => 'required|array',
            'payment_details.*.payment_no' => 'required|string',
            'payment_details.*.wht_no' => 'required|string',
            'payment_details.*.type' => 'required|string',
            'payment_details.*.document_no' => 'required|string',
            'payment_details.*.receipt_date' => 'required|date',
            'payment_details.*.amount' => 'required|numeric',
            'payment_details.*.status' => 'required|string',
            'payment_details.*.remarks' => 'nullable|string'
        ]);

        $notificationsController = new NotificationsController();


        $whtclrNo = DB::transaction(function () use ($validated, $request, $wHTClearedNumberService) {
            $whtClearingNo = $wHTClearedNumberService->generate();
            // Validate the payment number is unique (just in case)
            if (WHTCleared::where('wht_clearing_no', $whtClearingNo)->exists()) {
                throw ValidationException::withMessages([
                    'wht_clearing_no' => 'Error Please Try Again',
                ]);
            }
            WHTCleared::create([
                'wht_clearing_no' => $whtClearingNo,
                'transaction_date' => $validated['transaction_date'],
                'clearing_date' => $validated['clearing_date'],
                'customer_code' => $validated['customer_code'],
                'customer_name' => $validated['customer_name'],
                'created_by' => $request->user()->name,
            ]);

            $whtClearedItems = array_map(function ($item) use ($whtClearingNo) {
                return [
                    'wht_clearing_no' => $whtClearingNo,
                    'payment_no' => $item['payment_no'],
                    'wht_no' => $item['wht_no'],
                    'document_no' => $item['document_no'],
                    'receipt_date' => $item['receipt_date'],
                    'amount' => $item['amount'],
                    'status' => $item['status'],
                    'remarks' => $item['remarks'] ?? null,
                ];
            }, $validated['payment_details']);

            WHTClearedItems::insert($whtClearedItems);

            foreach ($validated['payment_details'] as $payment) {
                // Update the original payment status
                $pd = PaymentDetails::where([
                    'payment_no' => $payment['payment_no'],
                    'check_no'   => $payment['wht_no'],
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
                    $newAmount = $ledger->running_balance - $payment['amount'];
                    if ($newAmount < 0) {
                        throw ValidationException::withMessages([
                            'wht_clearing_no' => 'Error Please Try Again',
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
            return $whtClearingNo;
        });
        event(new NewCreated('wht_clearing'));

        $notificationsController->index($request);

        $userIds = User::whereIn('role', ['Admin', 'Accounting'])
            ->pluck('id')
            ->unique();

        foreach ($userIds as $userId) {
            $channel = 'notification-update.' . Str::random(20);
            broadcast(new NotificationEvent($userId, $channel));
        }

        session()->put('whtclearing_number', $whtclrNo);
        return redirect()->back();
    }

    public function latest()
    {
        return DB::transaction(function () {
            $latestWhtCleared = WHTCleared::lockForUpdate()
                ->orderByDesc('wht_clearing_no')
                ->first();

            $nextNumber = $latestWhtCleared ? $latestWhtCleared->wht_clearing_no + 1 : 25000001;

            return response()->json([
                'next_clearing_no' => $nextNumber,
                'is_new_sequence' => !$latestWhtCleared
            ]);
        });
    }
}
