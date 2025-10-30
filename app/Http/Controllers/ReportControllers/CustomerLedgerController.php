<?php

namespace App\Http\Controllers\ReportControllers;

use App\Events\NewCreated;
use App\Http\Controllers\Controller;
use App\Models\ReportModels\CustomerLedger;
use App\Models\TransactionModels\Payment;
use App\Models\TransactionModels\PaymentDetails;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CustomerLedgerController extends Controller
{

    public function index(Request $request)
    {
        $query = CustomerLedger::query();

        // Customer code filter
        if ($request->customer_code) {
            $query->where('customer_code', 'like', '%' . $request->customer_code . '%');
        }

        // Date sorting
        if ($request->date_start) {
            $query->whereDate('date', '>=', $request->date_start);
        }

        if ($request->date_end) {
            $query->whereDate('date', '<=', $request->date_end);
        }

        if ($request->customer_code) {
            $partialPaymentForwarded = (float) CustomerLedger::where('customer_code', $request->customer_code)
                ->whereDate('date', '<', $request->date_start)
                ->sum('running_balance');

            $totalFloatingAmount = (float) PaymentDetails::where('customer_code', $request->customer_code)
                ->whereDate('document_date', '<', $request->date_start)
                ->where('status', 'Floating')
                ->sum('amount_paid');
        }
        $paymentForwarded = 0;

        if ($request->type_filters && $request->type_filters === 'Without Floating Deducted') {

            $paymentForwarded = $partialPaymentForwarded;

            // Get all records (not paginated) to calculate running balance
            $allRecords = $query->clone()
                ->orderBy('customer_code')
                // ->orderBy('type')
                ->orderBy('date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            // Calculate running balance per customer and type
            $runningBalances = [];
            $processedRecords = $allRecords->map(function ($record) use (&$runningBalances, $paymentForwarded) {
                $key = $record->customer_code;

                // Initialize running balance for this customer+type if not exists
                if (!isset($runningBalances[$key])) {
                    $runningBalances[$key] = $paymentForwarded;
                }

                // Calculate debit and credit based on transaction type
                $debit = ($record->amount ?? 0)
                    - ($record->shrinkage ?? 0)
                    + ($record->overage ?? 0)
                    - ($record->return ?? 0)
                    + ($record->adjusted_amount ?? 0);
                $credit = $record->amount_paid;

                $record->document_balance = $record->running_balance; //for display document real balance
                // Update running balance
                $runningBalances[$key] += $debit - $credit;
                $record->running_balance = $runningBalances[$key];
                $record->debit_amount = $debit;  // Optional: store for display
                $record->credit_amount = $credit; // Optional: store for display

                return $record;
            });
        } else if ($request->type_filters && $request->type_filters === 'With Floating Deducted') {
            $paymentForwarded = $partialPaymentForwarded - $totalFloatingAmount;

            // Get all records (not paginated) to calculate running balance
            $allRecords = $query->clone()
                ->orderBy('customer_code')
                // ->orderBy('type')
                ->orderBy('date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            $floatingAmounts = PaymentDetails::where('customer_code', $request->customer_code)
                ->where('status', 'Floating')
                ->selectRaw('document_no, type, SUM(amount_paid) as total_floating')
                ->groupBy('document_no', 'type')
                ->get()
                ->groupBy(['document_no', 'type']);

            // Calculate running balance per customer and type
            $runningBalances = [];
            $processedRecords = $allRecords->map(function ($record) use (&$runningBalances, $floatingAmounts, $paymentForwarded) {
                $key = $record->customer_code;

                // Initialize running balance for this customer+type if not exists
                if (!isset($runningBalances[$key])) {
                    $runningBalances[$key] = $paymentForwarded;
                }

                $floatingAmount = $floatingAmounts
                    ->get($record->invoice_number, collect())
                    ->get($record->type, collect())
                    ->first()
                    ->total_floating ?? 0;

                // Calculate debit and credit based on transaction type
                $debit = ($record->amount ?? 0)
                    - ($record->shrinkage ?? 0)
                    + ($record->overage ?? 0)
                    - ($record->return ?? 0)
                    + ($record->adjusted_amount ?? 0);
                $credit = ($record->amount_paid) + ($floatingAmount);

                $record->document_balance = $record->running_balance;//for display document real balance
                // Update running balance
                $runningBalances[$key] += $debit - $credit;
                $record->running_balance = $runningBalances[$key];
                $record->debit_amount = $debit;  // Optional: store for display
                $record->amount_paid = $credit; // Optional: store for display

                return $record;
            });
        } else {
            // Get all records (not paginated) to calculate running balance
            $allRecords = $query->clone()
                ->orderBy('customer_code')
                // ->orderBy('type')
                ->orderBy('date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            // Calculate running balance per customer and type
            $runningBalances = [];
            $processedRecords = $allRecords->map(function ($record) use (&$runningBalances) {
                $key = $record->customer_code;

                // Initialize running balance for this customer+type if not exists
                if (!isset($runningBalances[$key])) {
                    $runningBalances[$key] = 0;
                }

                // Calculate debit and credit based on transaction type
                $debit = ($record->amount ?? 0)
                    - ($record->shrinkage ?? 0)
                    + ($record->overage ?? 0)
                    - ($record->return ?? 0)
                    + ($record->adjusted_amount ?? 0);
                $credit = $record->amount_paid;

                $record->document_balance = $record->running_balance;//for display document real balance
                // Update running balance
                $runningBalances[$key] += $debit - $credit;
                $record->running_balance = $runningBalances[$key];
                $record->debit_amount = $debit;  // Optional: store for display
                $record->credit_amount = $credit; // Optional: store for display

                return $record;
            });
        }


        // Now apply pagination to the processed records
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $paginatedItems = $processedRecords->slice(($page - 1) * $perPage, $perPage)->values();
        $paginatedRecords = new LengthAwarePaginator(
            $paginatedItems,
            $processedRecords->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );


        return Inertia::render('CustomerLedger', [
            'customerledgers' => $paginatedRecords,
            // 'searchTerm' => $request->search,
            'paymentForwarded' => $paymentForwarded,
            'filters' => [
                'customer_code' => $request->customer_code,
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
                'type_filters' => $request->type_filters,
            ],
            'generateTableData' => filter_var($request->generateTableData, FILTER_VALIDATE_BOOLEAN),
            'broadcastChannel' => 'customerledgers',
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'invoice_number' => [
                    'required',
                    Rule::unique('customer_ledger')->where(function ($query) use ($request) {
                        return $query->where('type', $request->type);
                    }),
                ],
                'date' => 'required|date',
                'type' => 'required',
                'customer_code' => 'required',
                'customer_name' => 'required',
                'currency' => 'required',
                'amount' => 'required|numeric',
                'adjusted_amount' => 'required|numeric',
                'amount_paid' => 'required|numeric',
                'running_balance' => 'required|numeric',
                'trade_type' => 'required',
                'shrinkage' => 'required|numeric',
                'overage' => 'required|numeric',
                'return' => 'required|numeric',
                'si_payment_type' => 'required',
            ]);

            // Laravel will automatically add timestamps
            $ledger = CustomerLedger::create($validated);

            event(new NewCreated('customerledger'));

            return response()->json([
                'success' => true,
                'id' => $ledger->id
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(), // Optional: for debugging only
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string',
            'type' => 'required|string',
            'shrinkage' => 'required|numeric',
            'overage' => 'required|numeric',
            'return' => 'required|numeric',
        ]);

        try {
            // $ledger = CustomerLedger::where('invoice_number', $validated['invoice_number'])
            //     ->where('type', $validated['type'])
            //     ->firstOrFail();

            // $ledger->update([
            //     'shrinkage' => $validated['shrinkage'],
            //     'overage' => $validated['overage'],
            //     'return' => $validated['return'],
            // ]);

            $ledger = CustomerLedger::where('invoice_number', $validated['invoice_number'])
                ->where('type', $validated['type'])
                ->firstOrFail();

            // Initialize variables with default values
            $shrinkage = $validated['shrinkage'] ?? 0;
            $overage = $validated['overage'] ?? 0;
            $return = $validated['return'] ?? 0;

            // Calculate new running balance
            $newRunningBalance = $ledger->running_balance
                - $shrinkage
                + $overage
                - $return;

            $uShrinkage = ($ledger->shrinkage ?: 0.00) + $shrinkage;
            $uOverage = ($ledger->overage ?: 0.00) + $overage;
            $uReturn = ($ledger->return ?: 0.00) + $return;

            // Update the ledger
            $ledger->update([
                'shrinkage' => $uShrinkage,
                'overage' => $uOverage,
                'return' => $uReturn,
                'running_balance' => $newRunningBalance
            ]);

            event(new NewCreated('customerledger'));

            return response()->json([
                'success' => true,
                'message' => 'Ledger adjustments updated successfully',
                'data' => $ledger
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'No ledger entry found with the specified invoice number and type'
            ], 404);
        }
    }

    public function getCustomerLedgerList(Request $request)
    {
        $documentNo = $request->query('document_no');

        if (!$documentNo) {
            return response()->json(['data' => 'NOT FOUND']);
        }

        $ledger = CustomerLedger::where('type', 'Sales Invoice')
            ->where('invoice_number', $documentNo)
            ->first();

        if (!$ledger) {
            return response()->json(['data' => 'NOT FOUND']);
        }

        $hasFloatingPayment = PaymentDetails::where('document_no', $documentNo)
            ->where('type', 'Sales Invoice')
            ->where('status', 'Floating')
            ->exists();

        if ($ledger->amount_paid !== 0 && $hasFloatingPayment) {
            return response()->json(['data' => 'Paid']);
        }

        return response()->json(['data' => 'Not Paid']);
    }



    // /NON API/ ///////////////////////////////////////////////////////////////////

    public function getPaymentDetails(Request $request)
    {
        $customerCode = $request->input('customer_code');

        // Now get all ledger entries for these invoice numbers
        $payments = PaymentDetails::where('customer_code', $customerCode)
            ->select('payment_no', 'document_no', 'document_date', 'payment_date', 'payment_type', 'type', 'check_type', 'amount_paid', 'status')
            ->get();

        // Format the results
        $result = $payments->map(function ($payment) {
            return [
                'payment_no' => $payment->payment_no,
                'document_no' => $payment->document_no,
                'document_date' => $payment->document_date,
                'payment_date' => $payment->payment_date,
                'payment_type' => $payment->payment_type,
                'type' => $payment->type,
                'check_type' => $payment->check_type,
                'amount_paid' => $payment->amount_paid,
                'status' => $payment->status,
            ];
        });

        return response()->json($result);
    }

    public function getFloatingChecks(Request $request)
    {

        $validated = $request->validate([
            'customer_code' => 'required',
            'checktype' => 'required',
            'clearingdate' => 'required',
        ]);

        $customerCode = $validated['customer_code'];
        $checktype = $validated['checktype'];
        $clearingDate = $validated['clearingdate'];

        $payments = DB::table('payment_details')
            ->where('customer_code', $customerCode)
            ->where('check_type', $checktype) // Only get check payments
            ->where('due_date', '<=', $clearingDate) // Only get check payments
            ->where(function ($query) {
                $query->where('status', 'Floating')
                    ->orWhereNull('status');
            })
            ->select([
                'payment_no',
                'check_no',
                'document_no',
                'type',
                'due_date',
                'amount_paid',
                'status'
            ])
            ->orderBy('due_date', 'asc') // Add ordering
            ->get();

        return response()->json($payments);
    }

    public function getFloatingWht(Request $request)
    {

        $validated = $request->validate([
            'customer_code' => 'required',
            'payment_type' => 'required',
            'clearingdate' => 'required',
        ]);

        $customerCode = $validated['customer_code'];
        $paymentType = $validated['payment_type'];
        $clearingDate = $validated['clearingdate'];

        $payments = DB::table('payment_details')
            ->where('customer_code', $customerCode)
            ->where('payment_type', $paymentType) // Only get wht payments
            ->where('payment_receipt_date', '<=', $clearingDate) // Only get wht payments
            ->where(function ($query) {
                $query->where('status', 'Floating')
                    ->orWhereNull('status');
            })
            ->select([
                'payment_no',
                'check_no',
                'document_no',
                'type',
                'payment_receipt_date',
                'amount_paid',
                'status'
            ])
            ->orderBy('payment_receipt_date', 'asc') // Add ordering
            ->get();

        return response()->json($payments);
    }

    public function getPaymentList(Request $request)
    {
        $customerCode = $request->input('customer_code');
        $document_no = $request->input('document_no');
        $type = $request->input('type');

        $validatedDocumentNo = CustomerLedger::where('invoice_number', $document_no)
            ->where('type', 'Sales Invoice')
            ->where('si_payment_type', 'Cash')
            ->first();

        if ($validatedDocumentNo) {
            return response()->json([]);
        }

        // Now get all ledger entries for these invoice numbers
        $payments = PaymentDetails::where('customer_code', $customerCode)
            ->where('document_no', $document_no)
            ->where('type', $type)
            ->where('status', '!=', 'Cancelled')
            ->select('id', 'payment_no', 'document_no', 'payment_receipt_date', 'document_date', 'payment_date', 'payment_type', 'type', 'advpy_amount_paid', 'amount_paid', 'status', 'remarks')
            ->get();

        // Format the results
        $result = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'payment_no' => $payment->payment_no,
                'document_no' => $payment->document_no,
                'payment_receipt_date' => $payment->payment_receipt_date,
                'document_date' => $payment->document_date,
                'payment_date' => $payment->payment_date,
                'payment_type' => $payment->payment_type,
                'type' => $payment->type,
                'advpy_amount_paid' => $payment->advpy_amount_paid,
                'amount_paid' => $payment->amount_paid,
                'status' => $payment->status,
                'remarks' => $payment->remarks,
            ];
        });

        return response()->json($result);
    }

    public function getDocumentsPaidList(Request $request)
    {
        $payment_no = $request->input('payment_no');

        $paymentInfo = Payment::where('payment_no', $payment_no)
            ->select('document_no', 'customer_code', 'name', 'type')
            ->first();

        if ($paymentInfo) {
            $validatedDocumentNo = CustomerLedger::where('invoice_number', $paymentInfo->document_no)
                ->where('type', 'Sales Invoice')
                ->where('si_payment_type', 'Cash')
                ->first();

            if ($validatedDocumentNo) {
                return response()->json([]);
            }
        }


        // Now get all ledger entries for these invoice numbers
        $payments = PaymentDetails::where('payment_no', $payment_no)
            ->where('status', '!=', 'Cancelled')
            ->select('id', 'payment_no', 'document_no', 'payment_receipt_date', 'document_date', 'payment_date', 'payment_type', 'type', 'advpy_amount_paid', 'amount_paid', 'status', 'remarks')
            ->get();

        // Format the results
        $result = $payments->map(function ($payment) {
            return [
                'id' => $payment->id,
                'payment_no' => $payment->payment_no,
                'document_no' => $payment->document_no,
                'payment_receipt_date' => $payment->payment_receipt_date,
                'document_date' => $payment->document_date,
                'payment_date' => $payment->payment_date,
                'payment_type' => $payment->payment_type,
                'type' => $payment->type,
                'advpy_amount_paid' => $payment->advpy_amount_paid,
                'amount_paid' => $payment->amount_paid,
                'status' => $payment->status,
                'remarks' => $payment->remarks,
            ];
        });

        return response()->json([
            'payment_info' => $paymentInfo,
            'payment_details' => $result,
        ]);
    }
}
