<?php

namespace App\Http\Controllers\ReportControllers;

use App\Http\Controllers\Controller;
use App\Jobs\GeneratePdfJob;
use App\Models\MasterfileModels\Customer;
use App\Models\ReportModels\CustomerLedger;
use App\Models\TransactionModels\Adjustment;
use App\Models\TransactionModels\BeginningBalance;
use App\Models\TransactionModels\Invoice;
use App\Models\TransactionModels\InvoiceItem;
use App\Models\TransactionModels\Payment;
use App\Models\TransactionModels\PaymentDetails;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use NumberFormatter;
use Illuminate\Support\Str;

class ReportPDFGeneratorController extends Controller
{

    public function invoiceReport(Request $request)
    {
        $validated = $request->validate(
            [
                'customer_type' => 'required',
                'customer_code' => 'required_if:customer_type,By Customer',
                'customer_name' => 'required_if:customer_type,By Customer',
                'date_type' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'processtype' => 'nullable'
            ],
            [
                'customer_type.required' => 'Customer Type Required',
                'customer_code.required' => 'Customer Code Required',
                'customer_name.required' => 'Customer Name Required',
                'date_type.required' => 'Date Type Required',
                'start_date.required' => 'Start Date Required',
                'end_date.required' => 'End Date Required',
                'end_date.after_or_equal' => 'End Date must be after or equal to Start Date',
            ]
        );

        $formattedStartDate = date('m/d/Y', strtotime($validated['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($validated['end_date']));

        $query = Invoice::with('items')
            ->where('particular', '!=', 'N/A')
            ->orderBy('customer_code')
            ->orderBy('invoice_no');

        if ($validated['customer_type'] === 'By Customer') {
            $query->where('customer_code', $validated['customer_code']);
        }

        if ($validated['date_type'] === 'Receipt Date') {
            $query->whereBetween('receipt_date', [$validated['start_date'], $validated['end_date']]);
        } else {
            $query->whereBetween('transaction_date', [$validated['start_date'], $validated['end_date']]);
        }

        $checkQuery = clone $query;
        if (!$checkQuery->exists()) {
            throw ValidationException::withMessages([
                'general' => 'No Invoice Data Found',
            ]);
        }
        if ($validated['processtype'] === 'axios') {
            // Generate a unique channel for this PDF generation
            $channel = 'pdf-generation.' . Str::random(20);

            // Dispatch the job with all required data
            GeneratePdfJob::dispatch(
                $validated,
                $request->user()->id,
                $channel,
                strtoupper($request->user()->name),
                'invoiceprooflist'
            );

            return response()->json([
                'channel' => 'pdf-generation.' . $request->user()->id,
                'user_id' => $request->user()->id,
                'status' => 'started',
                'message' => 'PDF generation has started',
            ]);
        }
    }

    public function invoiceReportSummary(Request $request)
    {
        $validated = $request->validate(
            [
                'customer_type' => 'required',
                'customer_code' => 'required_if:customer_type,By Customer',
                'customer_name' => 'required_if:customer_type,By Customer',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'processtype' => 'nullable'
            ],
            [
                'customer_type.required' => 'Customer Type Required',
                'customer_code.required' => 'Customer Code Required',
                'customer_name.required' => 'Customer Name Required',
                'start_date.required' => 'Start Date Required',
                'end_date.required' => 'End Date Required',
            ]
        );

        $formattedStartDate = date('m/d/Y', strtotime($validated['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($validated['end_date']));

        $query = Invoice::with('items')
            ->where('particular', '!=', 'N/A')
            ->orderBy('customer_code')
            ->orderBy('invoice_no');

        if ($validated['customer_type'] === 'By Customer') {
            $query->where('customer_code', $validated['customer_code']);
        }

        $query->whereBetween('receipt_date', [$validated['start_date'], $validated['end_date']]);

        $checkQuery = clone $query;
        if (!$checkQuery->exists()) {
            throw ValidationException::withMessages([
                'general' => 'No Data Found',
            ]);
        }

        if ($validated['processtype'] === 'axios') {
            // Generate a unique channel for this PDF generation
            $channel = 'pdf-generation.' . Str::random(20);

            // Dispatch the job with all required data
            GeneratePdfJob::dispatch(
                $validated,
                $request->user()->id,
                $channel,
                strtoupper($request->user()->name),
                'invoicesummary'
            );

            return response()->json([
                'channel' => 'pdf-generation.' . $request->user()->id,
                'user_id' => $request->user()->id,
                'status' => 'started',
                'message' => 'PDF generation has started',
            ]);
        }
    }

    public function adjustmentReport(Request $request)
    {
        $validated = $request->validate(
            [
                'customer_type' => 'required',
                'customer_code' => 'required_if:customer_type,By Customer',
                'customer_name' => 'required_if:customer_type,By Customer',
                'date_type' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'processtype' => 'nullable'
            ],
            [
                'customer_type.required' => 'Customer Type Required',
                'customer_code.required' => 'Customer Code Required',
                'customer_name.required' => 'Customer Name Required',
                'date_type.required' => 'Date Type Required',
                'start_date.required' => 'Start Date Required',
                'end_date.required' => 'End Date Required',
            ]
        );

        // Format date range
        $formattedStartDate = date('m/d/Y', strtotime($validated['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($validated['end_date']));

        // Base query for adjustments
        $query = Adjustment::orderBy('customer_code')
            ->orderBy('adjustment_no');

        // Apply customer filter if needed
        if ($validated['customer_type'] === 'By Customer') {
            $query->where('customer_code', $validated['customer_code']);
        }

        // Apply date filter based on date_type
        if ($validated['date_type'] === 'Receipt Date') {
            $query->whereBetween('receipt_date', [$validated['start_date'], $validated['end_date']]);
        } else {
            $query->whereBetween('transaction_date', [$validated['start_date'], $validated['end_date']]);
        }

        $checkQuery = clone $query;
        if (!$checkQuery->exists()) {
            throw ValidationException::withMessages([
                'general' => 'No Data Found',
            ]);
        }

        if ($validated['processtype'] === 'axios') {
            // Generate a unique channel for this PDF generation
            $channel = 'pdf-generation.' . Str::random(20);

            // Dispatch the job with all required data
            GeneratePdfJob::dispatch(
                $validated,
                $request->user()->id,
                $channel,
                strtoupper($request->user()->name),
                'adjustmentprooflist'
            );

            return response()->json([
                'channel' => 'pdf-generation.' . $request->user()->id,
                'user_id' => $request->user()->id,
                'status' => 'started',
                'message' => 'PDF generation has started',
            ]);
        }
    }

    public function paymentReport(Request $request)
    {
        $validated = $request->validate(
            [
                'customer_type' => 'required',
                'customer_code' => 'required_if:customer_type,By Customer',
                'customer_name' => 'required_if:customer_type,By Customer',
                'date_type' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'processtype' => 'nullable',
                'reportOptions.cash' => 'boolean',
                'reportOptions.check' => 'boolean',
                'reportOptions.journalVoucher' => 'boolean',
                'reportOptions.onlineDeposits' => 'boolean',
                'reportOptions.creditableWHT' => 'boolean',
                'sortOption' => 'required',
                'paymentProoflistType' => 'nullable',
            ],
            [
                'customer_type.required' => 'Customer Type Required',
                'customer_code.required' => 'Customer Code Required',
                'customer_name.required' => 'Customer Name Required',
                'date_type.required' => 'Date Type Required',
                'start_date.required' => 'Start Date Required',
                'end_date.required' => 'End Date Required',
                'sortOption.required' => 'Sort Option Required',
            ]
        );

        if (!in_array(true, array_values($request->reportOptions), true)) {
            throw ValidationException::withMessages([
                'reportOptions' => ['At least one report option must be selected.']
            ]);
        }

        // Format date range
        $formattedStartDate = date('m/d/Y', strtotime($validated['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($validated['end_date']));

        // Base query for payments
        $query = Payment::query();
        $query->where(function ($q) {
            $q->whereNotNull('reference_no')
                ->orWhereNotNull('ds_no');
        });

        // Apply customer filter if needed
        if ($validated['customer_type'] === 'By Customer') {
            $query->where('customer_code', $validated['customer_code']);
        }

        // Apply date filter based on date_type
        if ($validated['date_type'] === 'Receipt Date') {
            $query->whereBetween('receipt_date', [$validated['start_date'], $validated['end_date']]);
        } else {
            $query->whereBetween('transaction_date', [$validated['start_date'], $validated['end_date']]);
        }

        // Apply sorting
        if ($validated['sortOption'] === 'Date') {
            $query->orderBy($validated['date_type'] === 'Receipt Date' ? 'receipt_date' : 'transaction_date');
        } else {
            $query->orderBy('document_no');
        }

        $query->orderBy('customer_code');

        // Get the selected report options
        $reportOptions = $validated['reportOptions'];
        $selectedTypes = [];

        // Map checkbox options to payment types
        if ($reportOptions['cash']) {
            $selectedTypes[] = '5A - Cash';
        }
        if ($reportOptions['check']) {
            $selectedTypes[] = '5D - Check';
        }
        if ($reportOptions['journalVoucher']) {
            $selectedTypes[] = '5B - Journal Voucher';
        }
        if ($reportOptions['onlineDeposits']) {
            $selectedTypes[] = '5C - Online Deposit';
        }
        if ($reportOptions['creditableWHT']) {
            $selectedTypes[] = '5E - Creditable(WHT)';
        }

        // Apply payment type filter if any options selected
        if (!empty($selectedTypes)) {
            $query->whereIn('payment_type', $selectedTypes);
        }

        $checkQuery = clone $query;
        if (!$checkQuery->exists()) {
            throw ValidationException::withMessages([
                'general' => 'No Data Found',
            ]);
        }

        if ($validated['processtype'] === 'axios') {
            $channel = 'pdf-generation.' . Str::random(20);

            GeneratePdfJob::dispatch(
                $validated,
                $request->user()->id,
                $channel,
                strtoupper($request->user()->name),
                'paymentreport'
            );

            return response()->json([
                'channel' => 'pdf-generation.' . $request->user()->id,
                'user_id' => $request->user()->id,
                'status' => 'started',
                'message' => 'PDF generation has started',
            ]);
        }
    }

    public function pdcDcReport(Request $request)
    {
        $validated = $request->validate(
            [
                'customer_type' => 'required',
                'customer_code' => 'required_if:customer_type,By Customer',
                'customer_name' => 'required_if:customer_type,By Customer',
                'date_type' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'processtype' => 'nullable'
            ],
            [
                'customer_type.required' => 'Customer Type Required',
                'customer_code.required' => 'Customer Code Required',
                'customer_name.required' => 'Customer Name Required',
                'date_type.required' => 'Date Type Required',
                'start_date.required' => 'Start Date Required',
                'end_date.required' => 'End Date Required',
            ]
        );

        // Format date range
        $formattedStartDate = date('m/d/Y', strtotime($validated['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($validated['end_date']));

        // Base query for adjustments
        $query = PaymentDetails::orderBy('customer_code')
            ->orderBy('payment_no')
            ->where('payment_type', 'Check');

        // Apply customer filter if needed
        if ($validated['customer_type'] === 'By Customer') {
            $query->where('customer_code', $validated['customer_code']);
        }

        // Apply date filter based on date_type
        if ($validated['date_type'] === 'Payment Date') {
            $query->whereBetween('payment_date', [$validated['start_date'], $validated['end_date']]);
        } else {
            $query->whereBetween('due_date', [$validated['start_date'], $validated['end_date']]);
        }

        $checkQuery = clone $query;
        if (!$checkQuery->exists()) {
            throw ValidationException::withMessages([
                'general' => 'No Data Found',
            ]);
        }

        if ($validated['processtype'] === 'axios') {
            $channel = 'pdf-generation.' . Str::random(20);

            GeneratePdfJob::dispatch(
                $validated,
                $request->user()->id,
                $channel,
                strtoupper($request->user()->name),
                'pdcdcreport'
            );

            return response()->json([
                'channel' => 'pdf-generation.' . $request->user()->id,
                'user_id' => $request->user()->id,
                'status' => 'started',
                'message' => 'PDF generation has started',
            ]);
        }
    }

    public function customerArAging(Request $request)
    {
        $validated = $request->validate(
            [
                'customer_type' => 'required',
                'customer_code' => 'required_if:customer_type,By Customer',
                'customer_name' => 'required_if:customer_type,By Customer',
                'as_of_date' => 'required|date',
                'processtype' => 'nullable',
                'reporttype' => 'nullable',
            ],
            [
                'customer_type.required' => 'Customer Type Required',
                'customer_code.required' => 'Customer Code Required',
                'customer_name.required' => 'Customer Name Required',
                'as_of_date.required' => 'As of Date Required',
            ]
        );

        $formattedAsOfDate = date('m/d/Y', strtotime($validated['as_of_date']));

        if ($validated['reporttype'] === 'AR') {
            $query = CustomerLedger::orderBy('customer_code')
                ->orderBy('invoice_number')
                ->where(function ($q) {
                    $q->whereNull('si_payment_type')
                        ->orWhere('si_payment_type', '!=', 'Cash');
                });

            if ($validated['customer_type'] === 'By Customer') {
                $query->where('customer_code', $validated['customer_code']);
            }

            $query->where('date', '<=', $validated['as_of_date']);

            $checkQuery = clone $query;
            if (!$checkQuery->exists()) {
                throw ValidationException::withMessages([
                    'general' => 'No Data Found',
                ]);
            }

            if ($validated['processtype'] === 'axios') {
                $channel = 'pdf-generation.' . Str::random(20);

                GeneratePdfJob::dispatch(
                    $validated,
                    $request->user()->id,
                    $channel,
                    strtoupper($request->user()->name),
                    'customeraragingreport'
                );

                return response()->json([
                    'channel' => 'pdf-generation.' . $request->user()->id,
                    'user_id' => $request->user()->id,
                    'status' => 'started',
                    'message' => 'PDF generation has started',
                ]);
            }
        } else {
            $query = PaymentDetails::where('payment_type', 'Check')
                ->orderBy('customer_code')
                ->orderBy('document_no');

            if ($validated['customer_type'] === 'By Customer') {
                $query->where('customer_code', $validated['customer_code']);
            }

            $query->where('payment_receipt_date', '<=', $validated['as_of_date']);

            $checkQuery = clone $query;
            if (!$checkQuery->exists()) {
                throw ValidationException::withMessages([
                    'general' => 'No Data Found',
                ]);
            }

            if ($validated['processtype'] === 'axios') {
                $channel = 'pdf-generation.' . Str::random(20);

                GeneratePdfJob::dispatch(
                    $validated,
                    $request->user()->id,
                    $channel,
                    strtoupper($request->user()->name),
                    'customeraragingreport'
                );

                return response()->json([
                    'channel' => 'pdf-generation.' . $request->user()->id,
                    'user_id' => $request->user()->id,
                    'status' => 'started',
                    'message' => 'PDF generation has started',
                ]);
            }
        }
    }

    public function begBalProoflist(Request $request)
    {
        $validated = $request->validate(
            [
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'processtype' => 'nullable',
            ],
            [
                'start_date.required' => 'Start Date Required',
                'end_date.required' => 'End Date Required',
            ]
        );

        $formattedStartDate = date('m/d/Y', strtotime($validated['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($validated['end_date']));

        $query = BeginningBalance::query()
            ->whereBetween('receipt_date', [$validated['start_date'], $validated['end_date']])
            ->orderBy('beginningbalance_no');

        $checkQuery = clone $query;
        if (!$checkQuery->exists()) {
            throw ValidationException::withMessages([
                'general' => 'No Data Found',
            ]);
        }

        if ($validated['processtype'] === 'axios') {
            $channel = 'pdf-generation.' . Str::random(20);

            GeneratePdfJob::dispatch(
                $validated,
                $request->user()->id,
                $channel,
                strtoupper($request->user()->name),
                'begbalprooflistreport'
            );

            return response()->json([
                'channel' => 'pdf-generation.' . $request->user()->id,
                'user_id' => $request->user()->id,
                'status' => 'started',
                'message' => 'PDF generation has started',
            ]);
        }
    }

    public function arOutstandingBalanceAO(Request $request)
    {
        $validated = $request->validate(
            [
                'file_type' => 'required',
                'customer_type' => 'required',
                'customer_code' => 'required_if:customer_type,By Customer',
                'customer_name' => 'required_if:customer_type,By Customer',
                'as_of_date' => 'required|date',
                'customerOptions.booking' => 'boolean',
                'customerOptions.concession' => 'boolean',
                'customerOptions.external' => 'boolean',
                'customerOptions.internal' => 'boolean',
                'customerOptions.nicoEmployees' => 'boolean',
                'customerOptions.subsidiary' => 'boolean',
                'customerOptions.institutional' => 'boolean',
                'customerOptions.alturasEmployees' => 'boolean',
                'customerOptions.easyLinkEmployees' => 'boolean',
                'processtype' => 'nullable'
            ],
            [
                'customer_type.required' => 'Customer Type Required',
                'customer_code.required' => 'Customer Code Required',
                'customer_name.required' => 'Customer Name Required',
                'as_of_date.required' => 'As of Date Required',
            ]
        );

        if ($validated['customer_type'] === 'All Customer') {
            if (!in_array(true, array_values($request->customerOptions), true)) {
                throw ValidationException::withMessages([
                    'customerOptions' => ['At least one customer option must be selected.']
                ]);
            }
        }

        $formattedAsOfDate = date('m/d/Y', strtotime($validated['as_of_date']));

        $query = CustomerLedger::orderBy('customer_code')
            ->orderBy('invoice_number')
            ->where('date', '<=', $validated['as_of_date'])
            ->where(function ($q) {
                $q->whereNull('si_payment_type')
                    ->orWhere('si_payment_type', '!=', 'Cash');
            });

        if ($validated['customer_type'] === 'All Customer') {
            $customerCodesQuery = Customer::select('cus_code');
            $typeMappings = [
                'booking' => 'Booking',
                'concession' => 'Concession',
                'external' => 'External',
                'internal' => 'Internal',
                'nicoEmployees' => 'NICO Employees',
                'subsidiary' => 'Subsidiary',
                'institutional' => 'Institutional',
                'alturasEmployees' => 'Alturas Employees',
                'easyLinkEmployees' => 'EasyLink Employees'
            ];

            $conditions = [];
            foreach ($typeMappings as $option => $type) {
                if ($request->customerOptions[$option] ?? false) {
                    $conditions[] = ['cus_type', '=', $type];
                }
            }

            $customerCodesQuery->where(function ($q) use ($conditions) {
                foreach ($conditions as $condition) {
                    $q->orWhere(...$condition);
                }
            });

            $customerCodes = $customerCodesQuery->pluck('cus_code');
            $query->whereIn('customer_code', $customerCodes);
        } elseif ($validated['customer_type'] === 'By Customer') {
            $query->where('customer_code', $validated['customer_code']);
        }

        $checkQuery = clone $query;
        if (!$checkQuery->exists()) {
            throw ValidationException::withMessages([
                'general' => 'No Data Found',
            ]);
        }

        if ($validated['processtype'] === 'axios') {
            $channel = 'pdf-generation.' . Str::random(20);

            GeneratePdfJob::dispatch(
                $validated,
                $request->user()->id,
                $channel,
                strtoupper($request->user()->name),
                'aroutstandingbalanceaoreport'
            );

            return response()->json([
                'channel' => 'pdf-generation.' . $request->user()->id,
                'user_id' => $request->user()->id,
                'status' => 'started',
                'message' => 'PDF generation has started',
            ]);
        }
    }

    public function arOutstandingBalanceDR(Request $request)
    {
        $validated = $request->validate(
            [
                'file_type' => 'required',
                'customer_type' => 'required',
                'customer_code' => 'required_if:customer_type,By Customer',
                'customer_name' => 'required_if:customer_type,By Customer',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'customerOptions.booking' => 'boolean',
                'customerOptions.concession' => 'boolean',
                'customerOptions.external' => 'boolean',
                'customerOptions.internal' => 'boolean',
                'customerOptions.nicoEmployees' => 'boolean',
                'customerOptions.subsidiary' => 'boolean',
                'customerOptions.institutional' => 'boolean',
                'customerOptions.alturasEmployees' => 'boolean',
                'customerOptions.easyLinkEmployees' => 'boolean',
                'processtype' => 'nullable'
            ],
            [
                'customer_type.required' => 'Customer Type Required',
                'customer_code.required' => 'Customer Code Required',
                'customer_name.required' => 'Customer Name Required',
                'start_date.required' => 'Start Date Required',
                'end_date.required' => 'End Date Required',
            ]
        );

        if ($validated['customer_type'] === 'All Customer') {
            if (!in_array(true, array_values($request->customerOptions), true)) {
                throw ValidationException::withMessages([
                    'customerOptions' => ['At least one customer Type must be selected.']
                ]);
            }
        }

        $formattedStartDate = date('m/d/Y', strtotime($validated['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($validated['end_date']));

        $query = CustomerLedger::orderBy('customer_code')
            ->orderBy('invoice_number')
            ->whereBetween('date', [$validated['start_date'], $validated['end_date']])
            ->where(function ($q) {
                $q->whereNull('si_payment_type')
                    ->orWhere('si_payment_type', '!=', 'Cash');
            });

        if ($validated['customer_type'] === 'All Customer') {
            $customerCodesQuery = Customer::select('cus_code');
            $typeMappings = [
                'booking' => 'Booking',
                'concession' => 'Concession',
                'external' => 'External',
                'internal' => 'Internal',
                'nicoEmployees' => 'NICO Employees',
                'subsidiary' => 'Subsidiary',
                'institutional' => 'Institutional',
                'alturasEmployees' => 'Alturas Employees',
                'easyLinkEmployees' => 'EasyLink Employees'
            ];

            $conditions = [];
            foreach ($typeMappings as $option => $type) {
                if ($request->customerOptions[$option] ?? false) {
                    $conditions[] = ['cus_type', '=', $type];
                }
            }

            $customerCodesQuery->where(function ($q) use ($conditions) {
                foreach ($conditions as $condition) {
                    $q->orWhere(...$condition);
                }
            });

            $customerCodes = $customerCodesQuery->pluck('cus_code');
            $query->whereIn('customer_code', $customerCodes);
        } elseif ($validated['customer_type'] === 'By Customer') {
            $query->where('customer_code', $validated['customer_code']);
        }

        $checkQuery = clone $query;
        if (!$checkQuery->exists()) {
            throw ValidationException::withMessages([
                'general' => 'No Data Found',
            ]);
        }

        if ($validated['processtype'] === 'axios') {
            $channel = 'pdf-generation.' . Str::random(20);

            GeneratePdfJob::dispatch(
                $validated,
                $request->user()->id,
                $channel,
                strtoupper($request->user()->name),
                'aroutstandingbalancedrreport'
            );

            return response()->json([
                'channel' => 'pdf-generation.' . $request->user()->id,
                'user_id' => $request->user()->id,
                'status' => 'started',
                'message' => 'PDF generation has started',
            ]);
        }
    }

    public function salesPerItem(Request $request)
    {
        $validated = $request->validate(
            [
                'item_type' => 'required',
                'selectedItems' => 'required|array|min:1',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'processtype' => 'nullable'
            ],
            [
                'item_type.required' => 'Item Type Required',
                'selectedItems.required' => 'Please select at least one item.',
                'start_date.required' => 'Start Date Required',
                'end_date.required' => 'End Date Required',
            ]
        );

        $formattedStartDate = date('m/d/Y', strtotime($validated['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($validated['end_date']));

        $invoiceNos = Invoice::whereBetween('receipt_date', [
            $validated['start_date'],
            $validated['end_date']
        ])->pluck('invoice_no');

        $query = InvoiceItem::query()
            ->whereIn('invoice_no', $invoiceNos)
            ->when($validated['item_type'] === 'By Items', function ($q) use ($validated) {
                $q->whereIn('item_name', $validated['selectedItems']);
            })
            ->orderBy('item_code')
            ->orderBy('item_name');

        $checkQuery = clone $query;
        if (!$checkQuery->exists()) {
            throw ValidationException::withMessages([
                'general' => 'No Data Found',
            ]);
        }

        if ($validated['processtype'] === 'axios') {
            $channel = 'pdf-generation.' . Str::random(20);

            GeneratePdfJob::dispatch(
                $validated,
                $request->user()->id,
                $channel,
                strtoupper($request->user()->name),
                'salesperitemreport'
            );

            return response()->json([
                'channel' => 'pdf-generation.' . $request->user()->id,
                'user_id' => $request->user()->id,
                'status' => 'started',
                'message' => 'PDF generation has started',
            ]);
        }
    }

    public function overageShortage(Request $request)
    {
        $validated = $request->validate(
            [
                'customer_type' => 'required',
                'customer_code' => 'required_if:customer_type,By Customer',
                'customer_name' => 'required_if:customer_type,By Customer',
                'date_type' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'processtype' => 'nullable',
                'sortOption' => 'required',
            ],
            [
                'customer_type.required' => 'Customer Type Required',
                'customer_code.required' => 'Customer Code Required',
                'customer_name.required' => 'Customer Name Required',
                'date_type.required' => 'Date Type Required',
                'start_date.required' => 'Start Date Required',
                'end_date.required' => 'End Date Required',
                'sortOption.required' => 'Sort Option Required',
            ]
        );

        // Base query for payments
        $query = Payment::query();
        $query->where(function ($q) {
            $q->whereNotNull('reference_no')
                ->orWhereNotNull('ds_no');
        });

        // Apply customer filter if needed
        if ($validated['customer_type'] === 'By Customer') {
            $query->where('customer_code', $validated['customer_code']);
        }

        // Apply date filter based on date_type
        if ($validated['date_type'] === 'Receipt Date') {
            $query->whereBetween('receipt_date', [$validated['start_date'], $validated['end_date']]);
        } else {
            $query->whereBetween('transaction_date', [$validated['start_date'], $validated['end_date']]);
        }

        // Apply sorting
        if ($validated['sortOption'] === 'Date') {
            $query->orderBy($validated['date_type'] === 'Receipt Date' ? 'receipt_date' : 'transaction_date');
        } else {
            $query->orderBy('document_no');
        }

        $query->orderBy('customer_code');

        // Get the selected report options
        // $reportOptions = $validated['reportOptions'];
        // $selectedTypes = [];

        // Map checkbox options to payment types
        // if ($reportOptions['cash']) {
        //     $selectedTypes[] = '5A - Cash';
        // }
        // if ($reportOptions['check']) {
        //     $selectedTypes[] = '5D - Check';
        // }
        // if ($reportOptions['journalVoucher']) {
        //     $selectedTypes[] = '5B - Journal Voucher';
        // }
        // if ($reportOptions['onlineDeposits']) {
        //     $selectedTypes[] = '5C - Online Deposit';
        // }
        // if ($reportOptions['creditableWHT']) {
        //     $selectedTypes[] = '5E - Creditable(WHT)';
        // }

        // Apply payment type filter if any options selected
        // if (!empty($selectedTypes)) {
        //     $query->whereIn('payment_type', $selectedTypes);
        // }

        $checkQuery = clone $query;
        if (!$checkQuery->exists()) {
            throw ValidationException::withMessages([
                'general' => 'No Data Found',
            ]);
        }

        if ($validated['processtype'] === 'axios') {
            $channel = 'pdf-generation.' . Str::random(20);

            GeneratePdfJob::dispatch(
                $validated,
                $request->user()->id,
                $channel,
                strtoupper($request->user()->name),
                'overageshortagereport'
            );

            return response()->json([
                'channel' => 'pdf-generation.' . $request->user()->id,
                'user_id' => $request->user()->id,
                'status' => 'started',
                'message' => 'PDF generation has started',
            ]);
        }
    }

    public function statementOfAccount(Request $request)
    {
        $validated = $request->validate(
            [
                'customer_type' => 'required',
                'customer_code' => 'required_if:customer_type,By Customer',
                'customer_name' => 'required_if:customer_type,By Customer',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'type' => 'required',
                'statement_date' => 'required',
                'processtype' => 'nullable',
                'soatype' => 'nullable',

            ],
            [
                'customer_type.required' => 'Customer Type Required',
                'customer_code.required' => 'Customer Code Required',
                'customer_name.required' => 'Customer Name Required',
                'start_date.required' => 'Start Date Required',
                'end_date.required' => 'End Date Required',
                'type.required' => 'Customer Type Required',
                'statement_date.required' => 'Statement Date Required',
            ]
        );

        // Format date range
        $formattedStartDate = date('F j, Y', strtotime($validated['start_date']));
        $formattedEndDate = date('F j, Y', strtotime($validated['end_date']));
        $formattedStatementDate = date('F j, Y', strtotime($validated['statement_date']));

        // Base query for adjustments
        $query = CustomerLedger::orderBy('customer_code')
            ->orderBy('invoice_number')
            ->where('type', $validated['type']);

        $query->where(function ($q) {
            $q->whereNull('si_payment_type')
                ->orWhere('si_payment_type', '!=', 'Cash');
        });

        // Apply customer filter if needed
        if ($validated['customer_type'] === 'By Customer') {
            $query->where('customer_code', $validated['customer_code']);
        }

        // Apply date filter based on date_type
        $query->whereBetween('date', [$validated['start_date'], $validated['end_date']]);
        $checkQuery = clone $query;
        if (!$checkQuery->exists()) {
            throw ValidationException::withMessages([
                'general' => 'No Data Found',
            ]);
        }
        if ($validated['processtype'] === 'axios') {
            $channel = 'pdf-generation.' . Str::random(20);

            GeneratePdfJob::dispatch(
                $validated,
                $request->user()->id,
                $channel,
                strtoupper($request->user()->name),
                'statementofaccountreport'
            );

            return response()->json([
                'channel' => 'pdf-generation.' . $request->user()->id,
                'user_id' => $request->user()->id,
                'status' => 'started',
                'message' => 'PDF generation has started',
            ]);
        }
    }

    public function statementOfAccountSummary(Request $request)
    {
        $validated = $request->validate(
            [
                'customer_type' => 'required',
                'customer_code' => 'required_if:customer_type,By Customer Code',
                'customer_name' => 'required_if:customer_type,By Customer Code',
                'customer_select_type' => 'required_if:customer_type,By Customer Type',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'processtype' => 'nullable',
            ],
            [
                'customer_type.required' => 'Customer Type Required',
                'customer_code.required_if' => 'Customer Code Required',
                'customer_name.required_if' => 'Customer Name Required',
                'customer_select_type.required_if' => 'Customer Type Required',
                'start_date.required' => 'Start Date Required',
                'end_date.required' => 'End Date Required',
            ]
        );

        $formattedStartDate = date('m/d/Y', strtotime($validated['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($validated['end_date']));

        $query = CustomerLedger::orderBy('customer_code')
            ->orderBy('invoice_number');

        $query->where(function ($q) {
            $q->whereNull('si_payment_type')
                ->orWhere('si_payment_type', '!=', 'Cash');
        });

        if ($validated['customer_type'] === 'By Customer Code') {
            $query->where('customer_code', $validated['customer_code']);
        }

        if ($validated['customer_type'] === 'By Customer Type') {
            $customerCodesQuery = Customer::select('cus_code');

            $customerCodesQuery->where('cus_type', $validated['customer_select_type']);

            $customerCodes = $customerCodesQuery->pluck('cus_code');

            $query->whereIn('customer_code', $customerCodes);
        }

        $query->whereBetween('date', [$validated['start_date'], $validated['end_date']]);

        $checkQuery = clone $query;
        if (!$checkQuery->exists()) {
            throw ValidationException::withMessages([
                'general' => 'No Data Found',
            ]);
        }

        if ($validated['processtype'] === 'axios') {
            $channel = 'pdf-generation.' . Str::random(20);

            GeneratePdfJob::dispatch(
                $validated,
                $request->user()->id,
                $channel,
                strtoupper($request->user()->name),
                'statementofaccountsummaryreport'
            );

            return response()->json([
                'channel' => 'pdf-generation.' . $request->user()->id,
                'user_id' => $request->user()->id,
                'status' => 'started',
                'message' => 'PDF generation has started',
            ]);
        }
    }

    public function delete(Request $request)
    {
        $path = $request->input('path');

        if (!$path || !Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        Storage::disk('public')->delete($path);
        return response()->json(['message' => 'PDF deleted successfully.']);
    }



    //HELPER
    protected function capitalizeWordsWithHyphens($text)
    {
        // Capitalize normally
        $text = ucwords($text);
        // Capitalize after hyphens manually
        return preg_replace_callback(
            '/-([a-z])/',
            function ($matches) {
                return '-' . strtoupper($matches[1]);
            },
            $text,
        );
    }

    // Helper function to convert amount to pesos words
    protected function convertToPesosWords($amount)
    {
        $f = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $amount = number_format($amount, 2, '.', '');
        [$pesos, $centavos] = explode('.', $amount);

        $words = '';

        if ((int) $pesos > 0) {
            $words .= $this->capitalizeWordsWithHyphens($f->format($pesos)) . ' Peso' . ((int) $pesos > 1 ? 's' : '');
        }

        if ((int) $centavos > 0) {
            if ($words !== '') {
                $words .= ' and ';
            }
            $words .= $this->capitalizeWordsWithHyphens($f->format($centavos)) . ' Centavo' . ((int) $centavos > 1 ? 's' : '');
        }

        return $words;
    }
}
