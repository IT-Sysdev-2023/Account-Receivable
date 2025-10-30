<?php

namespace App\Http\Controllers\TransactionControllers;

use App\Events\NewCreated;
use App\Http\Controllers\Controller;
use App\Models\MasterfileModels\Permission;
use App\Models\MasterfileModels\User;
use App\Models\ReportModels\CustomerLedger;
use App\Models\TransactionModels\Invoice;
use App\Models\TransactionModels\InvoiceItem;
use App\Models\TransactionModels\ManagerKeyEntries;
use App\Models\TransactionModels\Payment;
use App\Models\TransactionModels\PaymentDetails;
use App\Services\InvoiceNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class InvoiceController extends Controller
{

    public function index(Request $request)
    {
        $query = Invoice::query();

        // Search functionality
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('particular', '!=', 'N/A')
                    ->where('reference_no', '!=', 'N/A')
                    ->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('invoice_no', 'like', '%' . $request->search . '%');
            });
        }

        // Date sorting
        if ($request->date_start) {
            $query->whereDate('receipt_date', '>=', $request->date_start);
        }

        if ($request->date_end) {
            $query->whereDate('receipt_date', '<=', $request->date_end);
        }


        // Price Group filters
        if ($request->type_filtersPriceGroup) {
            $types = is_array($request->type_filtersPriceGroup)
                ? $request->type_filtersPriceGroup
                : explode(',', $request->type_filtersPriceGroup);
            $query->whereIn('price_group', $types);
        }

        // Payment Mode filters
        if ($request->type_filtersPaymentMode) {
            $types = is_array($request->type_filtersPaymentMode)
                ? $request->type_filtersPaymentMode
                : explode(',', $request->type_filtersPaymentMode);
            $query->whereIn('payment_mode', $types);
        }

        // Code sorting
        if ($request->code_sort) {
            $query->orderBy('invoice_no', $request->code_sort === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('invoice_no', 'desc');
        }

        return Inertia::render('Invoice', [
            'invoices' => $query->where('particular', '!=', 'N/A')->where('reference_no', '!=', 'N/A')->paginate(10)->withQueryString(),
            'searchTerm' => $request->search,
            'filters' => [
                'code_sort' => $request->code_sort,
                'type_filtersPriceGroup' => $request->type_filtersPriceGroup ?
                    (is_array($request->type_filtersPriceGroup) ?
                        $request->type_filtersPriceGroup :
                        explode(',', $request->type_filtersPriceGroup)) :
                    [],
                'type_filtersPaymentMode' => $request->type_filtersPaymentMode ?
                    (is_array($request->type_filtersPaymentMode) ?
                        $request->type_filtersPaymentMode :
                        explode(',', $request->type_filtersPaymentMode)) :
                    [],
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
            ],
            'broadcastChannel' => 'invoices',
        ]);
    }

    public function validateInvoiceCashPayment(Request $request)
    {
        $request->validate(
            [
                'invoice_no' => ['required', 'string'],
                'receipt_date' => ['required', 'date', 'before_or_equal:today'],
                'transaction_date' => ['required', 'date', 'before_or_equal:today'],
                'customer_code' => ['required', 'string'],
                'name' => ['required', 'string'],
                'price_group' => ['required', 'string'],
                'payment_mode' => ['required', 'in:Account Receivables,Cash'],
                'chargeinvoice_type' => ['required', 'string'],
                'particular' => ['required', 'string'],
                'reference_no' => ['required', 'string'],
                'total_amount' => ['required', 'string'],

                'invoices' => 'required|array',
                'invoices.*.item_code' => ['required_with:invoices', 'string'],
                'invoices.*.item_name' => ['required_with:invoices', 'string'],
                'invoices.*.packing' => ['required_with:invoices', 'string'],
                'invoices.*.quantity' => ['required_with:invoices', 'integer'],
                'invoices.*.price' => ['required_with:invoices', 'numeric'],
                'invoices.*.amount' => ['required_with:invoices', 'string'],

            ],
            [
                'invoice_no.required' => 'Invoice Number Required',
                'receipt_date.required' => 'Date Required',
                'receipt_date.date' => 'Date Must Be Valid Date',
                'receipt_date.before_or_equal' => 'Date Cannot Be Future',
                'transaction_date.required' => 'Date Required',
                'transaction_date.date' => 'Date Must Be Valid Date',
                'transaction_date.before_or_equal' => 'Date Cannot Be Future',
                'customer_code.required' => 'Customer Code Required',
                'name.required' => 'Customer Name Required',
                'price_group.required' => 'Price Group Required',
                'payment_mode.required' => 'Payment Mode Required',
                'payment_mode.in' => 'Payment Mode Required',
                'chargeinvoice_type.required' => 'Charge Invoice Type Required',
                'particular.required' => 'Particular Required',
                'reference_no.required' => 'Reference Number Required',

                'invoices.required' => 'Please Add Item',
                'invoices.*.item_code.required_with' => 'Item Code Required',
                'invoices.*.item_name.required_with' => 'Item Name Required',
                'invoices.*.packing.required_with' => 'Packing Required',
                'invoices.*.quantity.required_with' => 'Quantity Required',
                'invoices.*.quantity.integer' => 'Quantity Must Be Whole Number',
                'invoices.*.price.required_with' => 'Price Required',
                'invoices.*.price.numeric' => 'Price Must Be Valid Number',
                'invoices.*.amount.required_with' => 'Amount Required',
                'invoices.*.amount.numeric' => 'Amount Must Be Valid number',

                'total_amount.required' => 'Total Amount Required',
                'total_amount.numeric' => 'Total Amount Must Be Valid number',
            ]
        );
    }

    public function addInvoice(Request $request, InvoiceNumberService $invoiceNumberService)
    {
        $validated = $request->validate(
            [
                'invoice_no' => ['required', 'string'],
                'receipt_date' => ['required', 'date', 'before_or_equal:today'],
                'transaction_date' => ['required', 'date', 'before_or_equal:today'],
                'customer_code' => ['required', 'string'],
                'name' => ['required', 'string'],
                'price_group' => ['required', 'string'],
                'payment_mode' => ['required', 'in:Account Receivables,Cash'],
                'chargeinvoice_type' => ['required', 'string'],
                'particular' => ['required', 'string'],
                'reference_no' => ['required', 'string'],
                'total_amount' => ['required', 'string'],
                'payment_no' => ['nullable'],

                'invoices' => 'required|array',
                'invoices.*.item_code' => ['required_with:invoices', 'string'],
                'invoices.*.item_name' => ['required_with:invoices', 'string'],
                'invoices.*.packing' => ['required_with:invoices', 'string'],
                'invoices.*.quantity' => ['required_with:invoices', 'integer'],
                'invoices.*.price' => ['required_with:invoices', 'numeric'],
                'invoices.*.amount' => ['required_with:invoices', 'string'],

            ],
            [
                'invoice_no.required' => 'Invoice Number Required',
                'receipt_date.required' => 'Date Required',
                'receipt_date.date' => 'Date Must Be Valid Date',
                'receipt_date.before_or_equal' => 'Date Cannot Be Future',
                'transaction_date.required' => 'Date Required',
                'transaction_date.date' => 'Date Must Be Valid Date',
                'transaction_date.before_or_equal' => 'Date Cannot Be Future',
                'customer_code.required' => 'Customer Code Required',
                'name.required' => 'Customer Name Required',
                'price_group.required' => 'Price Group Required',
                'payment_mode.required' => 'Payment Mode Required',
                'payment_mode.in' => 'Payment Mode Required',
                'chargeinvoice_type.required' => 'Charge Invoice Type Required',
                'particular.required' => 'Particular Required',
                'reference_no.required' => 'Reference Number Required',

                'invoices.required' => 'Please Add Item',
                'invoices.*.item_code.required_with' => 'Item Code Required',
                'invoices.*.item_name.required_with' => 'Item Name Required',
                'invoices.*.packing.required_with' => 'Packing Required',
                'invoices.*.quantity.required_with' => 'Quantity Required',
                'invoices.*.quantity.integer' => 'Quantity Must Be Whole Number',
                'invoices.*.price.required_with' => 'Price Required',
                'invoices.*.price.numeric' => 'Price Must Be Valid Number',
                'invoices.*.amount.required_with' => 'Amount Required',
                'invoices.*.amount.numeric' => 'Amount Must Be Valid number',

                'total_amount.required' => 'Total Amount Required',
                'total_amount.numeric' => 'Total Amount Must Be Valid number',
            ]
        );


        $invNo = DB::transaction(function () use ($validated, $request, $invoiceNumberService) {

            $invoiceNumber = $invoiceNumberService->generate();

            if (Invoice::where('invoice_no', $invoiceNumber)->exists()) {
                throw ValidationException::withMessages([
                    'general' => 'Error Please Try Again',
                ]);
            }

            $validated['total_amount'] = (float)preg_replace('/[^0-9.]/', '', $validated['total_amount']);

            Invoice::create([
                'invoice_no' => $invoiceNumber,
                'payment_no' => $validated['payment_no'],
                'receipt_date' => $validated['receipt_date'],
                'transaction_date' => $validated['transaction_date'],
                'customer_code' => $validated['customer_code'],
                'name' => $validated['name'],
                'price_group' => $validated['price_group'],
                'payment_mode' => $validated['payment_mode'],
                'chargeinvoice_type' => $validated['chargeinvoice_type'],
                'particular' => $validated['particular'],
                'reference_no' => $validated['reference_no'],
                'total_amount' => $validated['total_amount'],
                'created_by' =>  $request->user()->name,
            ]);

            CustomerLedger::create([
                'invoice_number' => $invoiceNumber,
                'date' => $validated['receipt_date'],
                'type' => "Charge Invoice",
                'customer_code' => $validated['customer_code'],
                'customer_name' => $validated['name'],
                'currency' => "Php",
                'amount' => $validated['total_amount'],
                'adjusted_amount' => 0.00,
                'amount_paid' => $validated['payment_mode'] === 'Cash'
                    ? $validated['total_amount']
                    : 0.00,
                'running_balance' => $validated['payment_mode'] === 'Cash'
                    ? 0.00
                    : $validated['total_amount'],
            ]);

            InvoiceItem::insert(array_map(function ($item) use ($invoiceNumber) {
                return [
                    'invoice_no' => $invoiceNumber,
                    'item_code' => $item['item_code'],
                    'item_name' => $item['item_name'],
                    'packing' => $item['packing'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'amount' => (float)preg_replace('/[^0-9.]/', '', $item['amount']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $validated['invoices']));

            if ($validated['payment_mode'] === 'Cash') {
                PaymentDetails::where('payment_no', $validated['payment_no'])
                    ->update(['document_no' => $invoiceNumber]);
                Payment::where('payment_no', $validated['payment_no'])
                    ->update(['document_no' => $invoiceNumber]);
            }


            return $invoiceNumber;
        });

        event(new NewCreated('invoice'));
        event(new NewCreated('customerledger'));

        session()->put('invoice_number', $invNo);
        return redirect()->back();
    }

    public function destroy($id)
    {
        $inv = Invoice::findOrFail($id);
        $inv->delete();
    }

    public function latest()
    {
        //return Invoice::orderByDesc('id')->value('invoice_no'); // returns "25000001" or null

        return DB::transaction(function () {
            $latestInvoice = Invoice::withTrashed()
                ->lockForUpdate() // Prevent concurrent access
                ->orderByDesc('invoice_no')
                ->first();

            $nextNumber = $latestInvoice ? $latestInvoice->invoice_no + 1 : 25000001;

            return response()->json([
                'next_invoice_no' => $nextNumber,
                'is_new_sequence' => !$latestInvoice
            ]);
        });
    }

    public function getInvoiceList(Request $request)
    {
        $customerCode = $request->input('type');

        // Now get all ledger entries for these invoice numbers
        $ledgers = CustomerLedger::where('customer_code', $customerCode)
            ->select('invoice_number', 'date', 'type', 'amount', 'amount_paid', 'running_balance')
            ->get();

        // Format the results
        $result = $ledgers->map(function ($ledger) {
            return [
                'invoice_no' => $ledger->invoice_number,
                'receipt_date' => $ledger->date,
                'type' => $ledger->type,
                'amount' => $ledger->amount,
                'amount_paid' => $ledger->amount_paid,
                'running_balance' => $ledger->running_balance
            ];
        });

        return response()->json($result);
    }

    public function getInvoiceClearedList(Request $request)
    {
        $customerCode = $request->input('type');

        // Now get all ledger entries for these invoice numbers
        $ledgers = CustomerLedger::where('customer_code', $customerCode)
            ->onlyTrashed()
            ->select('invoice_number', 'date', 'type', 'amount', 'amount_paid', 'running_balance')
            ->get();

        // Format the results
        $result = $ledgers->map(function ($ledger) {
            return [
                'invoice_no' => $ledger->invoice_number,
                'receipt_date' => $ledger->date,
                'type' => $ledger->type,
                'amount' => $ledger->amount,
                'amount_paid' => $ledger->amount_paid,
                'running_balance' => $ledger->running_balance
            ];
        });

        return response()->json($result);
    }

    public function getCustomerBegBalList(Request $request)
    {
        $date = $request->input('date');

        // Validate date input
        if (!$date) {
            return response()->json(['error' => 'Date parameter is required'], 400);
        }

        // Get the latest ledger entries for each customer on or before the specified date
        $customerBalances = CustomerLedger::select(
            'customer_code',
            DB::raw('MAX(customers.cus_name) as customer_name'),
            DB::raw('SUM(CASE WHEN type != "BG" THEN running_balance ELSE 0 END) as total_balance')
        )
            ->join('customers', 'customer_ledger.customer_code', '=', 'customers.cus_code')
            ->where('date', '<=', $date)
            ->groupBy('customer_code')
            ->get();

        // Format the results
        $result = $customerBalances->map(function ($item) {
            return [
                'customer_code' => $item->customer_code,
                'customer_name' => $item->customer_name,
                'total_balance' => $item->total_balance
            ];
        });

        return response()->json($result);
    }

    public function getInvoiceListForPayment(Request $request)
    {
        $customerCode = $request->input('customer_code');
        $date = $request->input('date');

        // Get all ledger entries for the customer
        $ledgers = CustomerLedger::where('customer_code', $customerCode)
            ->select('invoice_number', 'date', 'type', 'amount', 'amount_paid', 'running_balance', 'trade_type')
            ->where('date', '<=', $date)
            ->orderBy('date')
            ->orderBy('created_at')
            ->get();

        $floatingAmounts = PaymentDetails::where('customer_code', $customerCode)
            ->where('status', 'Floating')
            ->selectRaw('document_no, type, SUM(amount_paid) as total_floating')
            ->groupBy('document_no', 'type')
            ->get()
            ->groupBy(['document_no', 'type']);

        // Format the results and check payment details
        $result = $ledgers->map(function ($ledger) {
            // Initialize floating amount
            $pdcFloatingAmount = 0;
            $dcFloatingAmount = 0;
            $whtFloatingAmount = 0;

            // Check if invoice exists in payment_details
            $paymentDetails = PaymentDetails::where('document_no', $ledger->invoice_number)
                ->where('type', $ledger->type)
                ->get();

            // If payment details exist, check status and calculate floating amount
            if ($paymentDetails->isNotEmpty()) {
                $floatingPayments = $paymentDetails->where('status', 'Floating');
                if ($floatingPayments->isNotEmpty()) {
                    $pdcFloatingAmount = $floatingPayments->where('payment_type', 'Check')
                        ->where('check_type', 'Post Dated Check')
                        ->sum('amount_paid');
                    $dcFloatingAmount = $floatingPayments->where('payment_type', 'Check')
                        ->where('check_type', 'Dated Check')
                        ->sum('amount_paid');
                    $whtFloatingAmount = $floatingPayments->where('payment_type', 'Creditable(WHT)')
                        ->sum('amount_paid');
                }
            }

            return [
                'invoice_no' => $ledger->invoice_number,
                'receipt_date' => $ledger->date,
                'type' => $ledger->type,
                'amount' => $ledger->amount,
                'amount_paid' => $ledger->amount_paid,
                'running_balance' => $ledger->running_balance,
                'trade_type' => $ledger->trade_type,
                'pdc_floating_amount' => $pdcFloatingAmount,
                'has_pdc_floating_payments' => $pdcFloatingAmount > 0,
                'dc_floating_amount' => $dcFloatingAmount,
                'has_dc_floating_payments' => $dcFloatingAmount > 0,
                'wht_floating_amount' => $whtFloatingAmount,
                'has_wht_floating_payments' => $whtFloatingAmount > 0
            ];
        });

        return response()->json($result);
    }
}
