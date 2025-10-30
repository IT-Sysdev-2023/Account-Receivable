<?php

namespace App\Jobs;

use App\Events\PdfGenerated;
use App\Events\PdfGenerationProgress;
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
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use NumberFormatter;

class GeneratePdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected array $validatedData,
        protected string $userId,
        protected string $channel,
        protected string $preparedBy,
        protected string $reportType,
    ) {}

    public function handle()
    {
        switch ($this->reportType) {
            case 'invoiceprooflist':
                $this->generateInvoiceProoflist();
                break;
            case 'invoicesummary':
                $this->generateInvoiceSummary();
                break;
            case 'adjustmentprooflist':
                $this->generateAdjustmentProoflist();
                break;
            case 'paymentreport':
                $this->generatePaymentReport();
                break;
            case 'pdcdcreport':
                $this->generatePdcDcReport();
                break;
            case 'customeraragingreport':
                $this->generateCustomerARAgingReport();
                break;
            case 'begbalprooflistreport':
                $this->generateBegBalProoflist();
                break;
            case 'aroutstandingbalanceaoreport':
                if ($this->validatedData['file_type'] === 'PDF') {
                    $this->generateArOutstandingBalanceAOReport();
                } else {
                    $this->generateArOutstandingBalanceAODataForExcel();
                }
                break;
            case 'aroutstandingbalancedrreport':
                if ($this->validatedData['file_type'] === 'PDF') {
                    $this->generateArOutstandingBalanceDrReport();
                } else {
                    $this->generateArOutstandingBalanceDRDataForExcel();
                }
                break;
            case 'salesperitemreport':
                $this->generateSalesPerItemReport();
                break;
            case 'statementofaccountreport':
                $this->generateStatementOfAccountReport();
                break;
            case 'statementofaccountsummaryreport':
                $this->generateStatementOfAccountSummaryReport();
                break;
        }
    }

    protected function updateProgress(int $progress, string $message)
    {
        try {
            broadcast(new PdfGenerationProgress(
                $this->userId,
                $progress,
                $message
            ));
            // Log::info("Progress updated: {$progress}% - {$message}");
        } catch (\Exception $e) {
            Log::error("Progress update failed: " . $e->getMessage());
        }
    }


    //GENERATE PDF FUNCTIONS
    protected function generateInvoiceProoflist()
    {
        $this->updateProgress(1, 'Preparing To Process Report...');

        $query = Invoice::with('items')
            ->where('particular', '!=', 'N/A')
            ->orderBy('customer_code')
            ->orderBy('invoice_no');

        if ($this->validatedData['customer_type'] === 'By Customer') {
            $query->where('customer_code', $this->validatedData['customer_code']);
        }

        if ($this->validatedData['date_type'] === 'Receipt Date') {
            $query->whereBetween('receipt_date', [
                $this->validatedData['start_date'],
                $this->validatedData['end_date']
            ]);
        } else {
            $query->whereBetween('transaction_date', [
                $this->validatedData['start_date'],
                $this->validatedData['end_date']
            ]);
        }


        $totalRows = (clone $query)->count();
        $processedRows = 0;
        $lastProgress = 1;

        $groupedData = [];
        $grandTotalAR = 0;
        $grandTotalCash = 0;

        $query->chunkById(500, function ($invoicesChunk) use (
            &$groupedData,
            &$grandTotalAR,
            &$grandTotalCash,
            &$processedRows,
            $totalRows,
            &$lastProgress
        ) {
            $invoices = $invoicesChunk->groupBy(function ($invoice) {
                return $invoice->customer_code . '|' . $invoice->name;
            });

            foreach ($invoices as $customerKey => $customerInvoices) {
                [$customerCode, $customerName] = explode('|', $customerKey);
                $existing = collect($groupedData)->firstWhere('customer_code', $customerCode);

                $customerData = $existing ?? [
                    'customer_code' => $customerCode,
                    'customer_name' => $customerName,
                    'invoices' => [],
                    'customer_cash_total' => 0,
                    'customer_ar_total' => 0
                ];

                foreach ($customerInvoices as $invoice) {
                    $items = $invoice->items->map(fn($item) => [
                        'item_code' => $item->item_code,
                        'item_name' => $item->item_name,
                    ])->toArray();

                    $cashAmount = $invoice->payment_mode === 'Cash' ? $invoice->total_amount : 0;
                    $arAmount = $invoice->payment_mode === 'Account Receivables' ? $invoice->total_amount : 0;

                    $customerData['invoices'][] = [
                        'invoice_no' => $invoice->invoice_no,
                        'transaction_date' => $this->validatedData['date_type'] === 'Receipt Date'
                            ? $invoice->receipt_date
                            : $invoice->transaction_date,
                        'reference_no' => $invoice->reference_no,
                        'particular' => $invoice->particular,
                        'items' => $items,
                        'cash_amount' => $cashAmount,
                        'ar_amount' => $arAmount,
                    ];

                    $customerData['customer_cash_total'] += $cashAmount;
                    $customerData['customer_ar_total'] += $arAmount;

                    $processedRows++;
                    $progress = intval(($processedRows / $totalRows) * 100);

                    if ($progress > $lastProgress) {
                        $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                        $lastProgress = $progress;
                    }
                }

                $grandTotalCash += $customerData['customer_cash_total'];
                $grandTotalAR += $customerData['customer_ar_total'];

                $groupedData = collect($groupedData)
                    ->reject(fn($d) => $d['customer_code'] === $customerCode)
                    ->values()
                    ->toArray();
                $groupedData[] = $customerData;
            }
        });

        $this->updateProgress(98, 'Generating Report...');

        $formattedStartDate = date('m/d/Y', strtotime($this->validatedData['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($this->validatedData['end_date']));

        $data = [
            'dateRange' => "$formattedStartDate to $formattedEndDate",
            'currency' => 'PHP',
            'date_type' => $this->validatedData['date_type'] === 'Receipt Date' ? 'Receipt' : 'Transaction',
            'groupedData' => $groupedData,
            'preparedBy' => $this->preparedBy,
            'grandTotalAR' => $grandTotalAR,
            'grandTotalCash' => $grandTotalCash,
        ];

        $pdf = Pdf::loadView('pdf.Report.invoiceProoflist_pdf', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'margin_top' => 10,
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
            ]);

        $this->updateProgress(99, 'Almost Done...');

        $filename = 'InvoiceProoflistReport_' . time() . '_' . Str::random(6) . '.pdf';
        Storage::disk('public')->put("temp/{$filename}", $pdf->output());

        $prefix = trim(config('app.url'), '/');
        $publicUrl = $prefix . Storage::url("temp/{$filename}");

        $this->updateProgress(100, 'Report Ready!');

        broadcast(new PdfGenerated(
            $this->userId,
            $filename,
            $publicUrl,
            $this->channel
        ));
    }

    protected function generateInvoiceSummary()
    {
        $this->updateProgress(1, 'Preparing To Process Report...');

        // Setup base query
        $query = Invoice::with('items')
            ->where('particular', '!=', 'N/A')
            ->orderBy('customer_code')
            ->orderBy('invoice_no');

        if ($this->validatedData['customer_type'] === 'By Customer') {
            $query->where('customer_code', $this->validatedData['customer_code']);
        }

        $query->whereBetween('receipt_date', [$this->validatedData['start_date'], $this->validatedData['end_date']]);

        // Count total rows
        $totalRows = (clone $query)->count();
        $processedRows = 0;
        $lastProgress = 1;

        $flatInvoices = [];
        $grandTotalAR = 0;
        $grandTotalCash = 0;

        $query->chunkById(500, function ($invoices) use (
            &$flatInvoices,
            &$grandTotalAR,
            &$grandTotalCash,
            &$processedRows,
            $totalRows,
            &$lastProgress
        ) {
            foreach ($invoices as $invoice) {
                $items = [];
                $cashAmount = $invoice->payment_mode === 'Cash' ? $invoice->total_amount : 0;
                $arAmount = $invoice->payment_mode === 'Account Receivables' ? $invoice->total_amount : 0;

                $grandTotalCash += $cashAmount;
                $grandTotalAR += $arAmount;

                foreach ($invoice->items as $item) {
                    $items[] = [
                        'item_code' => $item->item_code,
                        'item_name' => $item->item_name,
                    ];
                }

                $flatInvoices[] = [
                    'customer_code' => $invoice->customer_code,
                    'customer_name' => $invoice->name,
                    'invoice_no' => $invoice->invoice_no,
                    'transaction_date' => $invoice->receipt_date,
                    'reference_no' => $invoice->reference_no,
                    'particular' => $invoice->particular,
                    'items' => $items,
                    'cash_amount' => $invoice->payment_mode === 'Cash' ? $invoice->total_amount : 0,
                    'ar_amount' => $invoice->payment_mode === 'Account Receivables' ? $invoice->total_amount : 0,
                ];
                $processedRows++;
                $progress = intval(($processedRows / $totalRows) * 100);

                if ($progress > $lastProgress) {
                    $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                    $lastProgress = $progress;
                }
            }
        });

        $this->updateProgress(98, 'Generating Report...');

        $formattedStartDate = date('m/d/Y', strtotime($this->validatedData['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($this->validatedData['end_date']));

        $data = [
            'dateRange' => "$formattedStartDate to $formattedEndDate",
            'currency' => 'PHP',
            'date_type' => 'Receipt',
            'invoices' => $flatInvoices,
            'preparedBy' => $this->preparedBy,
            'grandTotalAR' => $grandTotalAR,
            'grandTotalCash' => $grandTotalCash,
        ];

        $pdf = Pdf::loadView('pdf.Report.invoiceSummary_pdf', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'margin_top' => 10,
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
            ]);

        $this->updateProgress(99, 'Almost Done...');

        $filename = 'InvoiceSummaryReport_' . time() . '_' . Str::random(6) . '.pdf';
        Storage::disk('public')->put("temp/{$filename}", $pdf->output());

        $prefix = trim(config('app.url'), '/');
        $publicUrl = $prefix . Storage::url("temp/{$filename}");

        $this->updateProgress(100, 'Report Ready!');

        broadcast(new PdfGenerated(
            $this->userId,
            $filename,
            $publicUrl,
            $this->channel
        ));
    }

    protected function generateAdjustmentProoflist()
    {
        $this->updateProgress(1, 'Preparing To Process Report...');
        // Format date range
        $formattedStartDate = date('m/d/Y', strtotime($this->validatedData['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($this->validatedData['end_date']));

        // Base query for adjustments
        $query = Adjustment::orderBy('customer_code')
            ->orderBy('adjustment_no');

        // Apply customer filter if needed
        if ($this->validatedData['customer_type'] === 'By Customer') {
            $query->where('customer_code', $this->validatedData['customer_code']);
        }

        // Apply date filter based on date_type
        if ($this->validatedData['date_type'] === 'Receipt Date') {
            $query->whereBetween('receipt_date', [$this->validatedData['start_date'], $this->validatedData['end_date']]);
        } else {
            $query->whereBetween('transaction_date', [$this->validatedData['start_date'], $this->validatedData['end_date']]);
        }

        $totalRows = (clone $query)->count();
        $processedRows = 0;
        $lastProgress = 1;

        $groupedData = [];
        $customerOverallAmountTotal = 0;

        $query->chunkById(500, function ($adjustmentsChunk) use (&$groupedData, &$customerOverallAmountTotal, &$totalRows, &$processedRows, &$lastProgress) {
            $adjustments = $adjustmentsChunk->groupBy(function ($adjustment) {
                return $adjustment->customer_code . '|' . $adjustment->name;
            });

            foreach ($adjustments as $customerKey => $customerAdjustments) {
                [$customerCode, $customerName] = explode('|', $customerKey);
                $customerAmountTotal = 0;

                $existing = collect($groupedData)->firstWhere('customer_code', $customerCode);

                $customerData = $existing ?? [
                    'customer_code' => $customerCode,
                    'customer_name' => $customerName,
                    'adjustments' => [],
                    'customerAmountTotal' => 0
                ];

                foreach ($customerAdjustments as $adjustment) {
                    $customerAmountTotal += $adjustment->amount;

                    $customerData['adjustments'][] = [
                        'adjustment_no' => $adjustment->adjustment_no,
                        'transaction_date' => $this->validatedData['date_type'] === 'Receipt Date'
                            ? $adjustment->receipt_date
                            : $adjustment->transaction_date,
                        'type' => $adjustment->type,
                        'apply_to' => $adjustment->apply_to,
                        'invoice_no' => $adjustment->invoice_no,
                        'adjustment_reason' => $adjustment->adjustment_reason,
                        'particulars' => $adjustment->particulars,
                        'amount' => $adjustment->amount,
                        'balance' => $adjustment->balance,
                    ];

                    $processedRows++;
                    $progress = intval(($processedRows / $totalRows) * 100);

                    if ($progress > $lastProgress) {
                        $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                        $lastProgress = $progress;
                    }
                }

                $customerData['customerAmountTotal'] += $customerAmountTotal;
                $customerOverallAmountTotal += $customerAmountTotal;

                $groupedData = collect($groupedData)->reject(fn($d) => $d['customer_code'] === $customerCode)->values()->toArray();
                $groupedData[] = $customerData;
            }
        });

        $this->updateProgress(98, 'Generating Report...');

        $data = [
            'dateRange' => "$formattedStartDate to $formattedEndDate",
            'currency' => 'PHP',
            'date_type' => $this->validatedData['date_type'] === 'Receipt Date' ? 'Receipt' : 'Transaction',
            'groupedData' => $groupedData,
            'preparedBy' => $this->preparedBy,
            'customerOverallAmountTotal' => $customerOverallAmountTotal,
        ];

        $pdf = Pdf::loadView('pdf.Report.adjustmentProoflist_pdf', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'margin_top' => 10,
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
            ]);

        $this->updateProgress(99, 'Almost Done...');

        $filename = 'AdjustmentProoflistReport_' . time() . '_' . Str::random(6) . '.pdf';
        Storage::disk('public')->put("temp/{$filename}", $pdf->output());

        $prefix = trim(config('app.url'), '/');
        $publicUrl = $prefix . Storage::url("temp/{$filename}");

        $this->updateProgress(100, 'Report Ready!');

        broadcast(new PdfGenerated(
            $this->userId,
            $filename,
            $publicUrl,
            $this->channel
        ));
    }

    protected function generatePaymentReport()
    {
        $this->updateProgress(1, 'Preparing To Process Report...');

        $formattedStartDate = date('m/d/Y', strtotime($this->validatedData['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($this->validatedData['end_date']));

        $query = Payment::query();
        $query->where(function ($q) {
            $q->whereNotNull('reference_no')
                ->orWhereNotNull('ds_no');
        });

        if ($this->validatedData['customer_type'] === 'By Customer') {
            $query->where('customer_code', $this->validatedData['customer_code']);
        }

        if ($this->validatedData['date_type'] === 'Receipt Date') {
            $query->whereBetween('receipt_date', [$this->validatedData['start_date'], $this->validatedData['end_date']]);
        } else {
            $query->whereBetween('transaction_date', [$this->validatedData['start_date'], $this->validatedData['end_date']]);
        }

        if ($this->validatedData['sortOption'] === 'Date') {
            $query->orderBy($this->validatedData['date_type'] === 'Receipt Date' ? 'receipt_date' : 'transaction_date');
        } else {
            $query->orderBy('document_no');
        }

        $query->orderBy('customer_code');

        $reportOptions = $this->validatedData['reportOptions'];
        $selectedTypes = [];

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

        if (!empty($selectedTypes)) {
            $query->whereIn('payment_type', $selectedTypes);
        }

        $totalRows = (clone $query)->count();
        $processedRows = 0;
        $lastProgress = 1;

        if ($this->validatedData['paymentProoflistType'] === 'Detailed') {
            $groupedData = [];
            $grandTotal = 0;

            $query->with('paymentDetails')->chunkById(500, function ($paymentsChunk) use (
                &$groupedData,
                &$grandTotal,
                &$processedRows,
                $totalRows,
                &$lastProgress
            ) {
                $chunkGrouped = $paymentsChunk->groupBy(['payment_type', function ($payment) {
                    return $payment->customer_code . '|' . $payment->name;
                }]);

                foreach ($chunkGrouped as $paymentType => $customers) {
                    $paymentTypeIndex = collect($groupedData)->search(fn($item) => $item['payment_type'] === $paymentType);
                    $paymentTypeData = $paymentTypeIndex !== false ? $groupedData[$paymentTypeIndex] : [
                        'payment_type' => $paymentType,
                        'customers' => [],
                        'type_total' => 0
                    ];

                    foreach ($customers as $customerKey => $customerPayments) {
                        [$customerCode, $customerName] = explode('|', $customerKey);

                        $customerIndex = collect($paymentTypeData['customers'])->search(
                            fn($customer) => $customer['customer_code'] === $customerCode
                        );

                        $customerData = $customerIndex !== false ? $paymentTypeData['customers'][$customerIndex] : [
                            'customer_code' => $customerCode,
                            'customer_name' => $customerName,
                            'payments' => [],
                            'customer_total' => 0
                        ];

                        foreach ($customerPayments as $payment) {
                            $paymentDetails = [];
                            $paymentTotal = 0;

                            foreach ($payment->paymentDetails as $detail) {
                                $paymentDetails[] = [
                                    'document_no' => $detail->document_no,
                                    'type' => $detail->type,
                                    'ds_no' => $payment->ds_no,
                                    'reference_no' => $payment->reference_no,
                                    'amount_paid' => $detail->status === 'Cancelled' ? null : $detail->amount_paid,
                                    'document_date' => $detail->document_date,
                                    'remarks' => $detail->remarks,
                                    'cancelled_amount' => $detail->status === 'Cancelled' ? $detail->amount_paid : null,
                                ];
                                $paymentTotal += $detail->status === 'Cancelled' ? 0 : $detail->amount_paid;
                            }

                            $customerData['payments'][] = [
                                'payment_no' => $payment->payment_no,
                                'date' => $this->validatedData['date_type'] === 'Receipt Date'
                                    ? $payment->receipt_date
                                    : $payment->transaction_date,
                                'payment_type' => $payment->payment_type,
                                'cash_in_bank' => $payment->cash_in_bank,
                                'payment_details' => $paymentDetails,
                                'payment_total' => $paymentTotal
                            ];

                            $customerData['customer_total'] += $paymentTotal;
                            $paymentTypeData['type_total'] += $paymentTotal;

                            $processedRows++;
                            $progress = intval(($processedRows / $totalRows) * 100);

                            if ($progress > $lastProgress) {
                                $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                                $lastProgress = $progress;
                            }
                        }

                        if ($customerIndex !== false) {
                            $paymentTypeData['customers'][$customerIndex] = $customerData;
                        } else {
                            $paymentTypeData['customers'][] = $customerData;
                        }
                    }

                    if ($paymentTypeIndex !== false) {
                        $groupedData[$paymentTypeIndex] = $paymentTypeData;
                    } else {
                        $groupedData[] = $paymentTypeData;
                    }

                    $grandTotal += $paymentTypeData['type_total'];
                }
            });

            $this->updateProgress(98, 'Generating Report...');

            $data = [
                'grandTotal' => $grandTotal,
                'dateRange' => "$formattedStartDate to $formattedEndDate",
                'currency' => 'PHP',
                'date_type' => $this->validatedData['date_type'] === 'Receipt Date' ? 'Receipt' : 'Transaction',
                'groupedData' => $groupedData,
                'preparedBy' => $this->preparedBy,
            ];

            $pdf = Pdf::loadView('pdf.Report.paymentProoflistDetailed_pdf', $data)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'margin_top' => 10,    // in mm
                    'margin_right' => 10,
                    'margin_bottom' => 10,
                    'margin_left' => 10,
                ]);

            $this->updateProgress(99, 'Almost Done...');

            $filename = 'PaymentProoflistDetailedReport_' . time() . '_' . Str::random(6) . '.pdf';
            Storage::disk('public')->put("temp/{$filename}", $pdf->output());

            $prefix = trim(config('app.url'), '/');
            $publicUrl = $prefix . Storage::url("temp/{$filename}");

            $this->updateProgress(100, 'Report Ready!');

            broadcast(new PdfGenerated(
                $this->userId,
                $filename,
                $publicUrl,
                $this->channel
            ));
        } else {
            $formattedPayments = collect();

            $query->with('paymentDetails')->chunkById(200, function ($paymentsChunk) use (
                &$formattedPayments,
                &$processedRows,
                $totalRows,
                &$lastProgress
            ) {
                foreach ($paymentsChunk as $payment) {
                    $paymentDetails = [];

                    foreach ($payment->paymentDetails as $detail) {
                        $paymentDetails[] = [
                            'document_no' => $detail->document_no,
                            'type' => $detail->type,
                            'ds_no' => $payment->ds_no,
                            'reference_no' => $payment->reference_no,
                            'amount' => $detail->amount,
                            'amount_paid' => $detail->amount_paid,
                            'document_date' => $detail->document_date,
                            'remarks' => $detail->remarks,
                        ];
                        $processedRows++;
                        $progress = intval(($processedRows / $totalRows) * 100);

                        if ($progress > $lastProgress) {
                            $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                            $lastProgress = $progress;
                        }
                    }

                    $formattedPayments->push([
                        'payment_no' => $payment->payment_no,
                        'date' => $this->validatedData['date_type'] === 'Receipt Date'
                            ? $payment->receipt_date
                            : $payment->transaction_date,
                        'customer' => $payment->name,
                        'payment_type' => $payment->payment_type,
                        'type' => $payment->type,
                        'reference_no' => $payment->reference_no,
                        'ds_no' => $payment->ds_no,
                        'document_no' => $payment->document_no,
                        'document_date' => $payment->document_date,
                        'total_amount' => $payment->total_amount,
                        'amount_paid' => $payment->amount_paid,
                        'payment_details' => $paymentDetails,
                    ]);
                }
            });

            $this->updateProgress(98, 'Generating Report...');

            $data = [
                'dateRange' => "$formattedStartDate to $formattedEndDate",
                'currency' => 'PHP',
                'date_type' => $this->validatedData['date_type'] === 'Receipt Date' ? 'Receipt' : 'Transaction',
                'payments' => $formattedPayments,
                'preparedBy' => $this->preparedBy,
            ];

            $pdf = Pdf::loadView('pdf.Report.paymentProoflistSummary_pdf', $data)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'margin_top' => 10,    // in mm
                    'margin_right' => 10,
                    'margin_bottom' => 10,
                    'margin_left' => 10,
                ]);

            $this->updateProgress(99, 'Almost Done...');

            $filename = 'PaymentProoflistSummaryReport_' . time() . '_' . Str::random(6) . '.pdf';
            Storage::disk('public')->put("temp/{$filename}", $pdf->output());

            $prefix = trim(config('app.url'), '/');
            $publicUrl = $prefix . Storage::url("temp/{$filename}");

            $this->updateProgress(100, 'Report Ready!');

            broadcast(new PdfGenerated(
                $this->userId,
                $filename,
                $publicUrl,
                $this->channel
            ));
        }
    }

    protected function generatePdcDcReport()
    {
        $this->updateProgress(1, 'Preparing To Process Report...');

        $formattedStartDate = date('m/d/Y', strtotime($this->validatedData['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($this->validatedData['end_date']));

        $query = PaymentDetails::orderBy('customer_code')
            ->orderBy('payment_no')
            ->where('payment_type', 'Check');

        if ($this->validatedData['customer_type'] === 'By Customer') {
            $query->where('customer_code', $this->validatedData['customer_code']);
        }

        if ($this->validatedData['date_type'] === 'Payment Date') {
            $query->whereBetween('payment_date', [$this->validatedData['start_date'], $this->validatedData['end_date']]);
        } else {
            $query->whereBetween('due_date', [$this->validatedData['start_date'], $this->validatedData['end_date']]);
        }

        $totalRows = (clone $query)->count();
        $processedRows = 0;
        $lastProgress = 1;

        $groupedData = [];
        $customerOverallAmountTotal = 0;

        $query->chunkById(500, function ($paymentDetailsChunk) use (
            &$groupedData,
            &$customerOverallAmountTotal,
            &$processedRows,
            $totalRows,
            &$lastProgress
        ) {
            $chunkGrouped = $paymentDetailsChunk->groupBy(function ($paymentDetails) {
                return $paymentDetails->customer_code . '|' . $paymentDetails->customer_name;
            });

            foreach ($chunkGrouped as $customerKey => $customerPaymentDetails) {
                [$customerCode, $customerName] = explode('|', $customerKey);

                $customerIndex = collect($groupedData)->search(
                    fn($customer) => $customer['customer_code'] === $customerCode
                );

                $customerData = $customerIndex !== false ? $groupedData[$customerIndex] : [
                    'customer_code' => $customerCode,
                    'customer_name' => $customerName,
                    'paymentDetails' => [],
                    'customerAmountTotal' => 0
                ];

                $customerAmountTotal = $customerData['customerAmountTotal'];

                foreach ($customerPaymentDetails as $paymentDetail) {
                    $amountPaid = $paymentDetail->status === 'Cancelled' ? null : $paymentDetail->amount_paid;
                    $cancelledAmount = $paymentDetail->status === 'Cancelled' ? $paymentDetail->amount_paid : null;

                    if ($amountPaid !== null) {
                        $customerAmountTotal += $amountPaid;
                    }

                    $customerData['paymentDetails'][] = [
                        'payment_no' => $paymentDetail->payment_no,
                        'check_no' => $paymentDetail->check_no,
                        'document_no' => $paymentDetail->document_no,
                        'document_date' => $paymentDetail->document_date,
                        'payment_receipt_date' => $paymentDetail->payment_receipt_date,
                        'payment_date' => $paymentDetail->payment_date,
                        'payment_type' => $paymentDetail->payment_type,
                        'type' => $paymentDetail->type,
                        'check_type' => $paymentDetail->check_type,
                        'amount_paid' => $amountPaid,
                        'due_date' => $paymentDetail->due_date,
                        'clearing_date' => $paymentDetail->clearing_date,
                        'status' => $paymentDetail->status,
                        'cancelled_amount' => $cancelledAmount,
                    ];

                    $processedRows++;
                    $progress = intval(($processedRows / $totalRows) * 100);

                    if ($progress > $lastProgress) {
                        $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                        $lastProgress = $progress;
                    }
                }

                $customerData['customerAmountTotal'] = $customerAmountTotal;

                if ($customerIndex !== false) {
                    $groupedData[$customerIndex] = $customerData;
                } else {
                    $groupedData[] = $customerData;
                }
            }
        });

        $customerOverallAmountTotal = collect($groupedData)->sum('customerAmountTotal');

        $this->updateProgress(98, 'Generating Report...');

        $data = [
            'dateRange' => "$formattedStartDate to $formattedEndDate",
            'currency' => 'PHP',
            'date_type' => $this->validatedData['date_type'] === 'Payment Date' ? 'Payment' : 'Due',
            'groupedData' => $groupedData,
            'preparedBy' => $this->preparedBy,
            'customerOverallAmountTotal' => $customerOverallAmountTotal,
        ];

        $pdf = Pdf::loadView('pdf.Report.customerPDCReport_pdf', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'margin_top' => 10,
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
            ]);

        $this->updateProgress(99, 'Almost Done...');

        $filename = 'CustomerPdcDcReport_' . time() . '_' . Str::random(6) . '.pdf';
        Storage::disk('public')->put("temp/{$filename}", $pdf->output());

        $prefix = trim(config('app.url'), '/');
        $publicUrl = $prefix . Storage::url("temp/{$filename}");

        $this->updateProgress(100, 'Report Ready!');

        broadcast(new PdfGenerated(
            $this->userId,
            $filename,
            $publicUrl,
            $this->channel
        ));
    }

    protected function generateCustomerARAgingReport()
    {
        $this->updateProgress(1, 'Preparing To Process Report...');

        $formattedAsOfDate = date('m/d/Y', strtotime($this->validatedData['as_of_date']));

        if ($this->validatedData['reporttype'] === 'AR') {
            $query = CustomerLedger::orderBy('customer_code')
                ->orderBy('invoice_number')
                ->where(function ($q) {
                    $q->whereNull('si_payment_type')
                        ->orWhere('si_payment_type', '!=', 'Cash');
                });

            if ($this->validatedData['customer_type'] === 'By Customer') {
                $query->where('customer_code', $this->validatedData['customer_code']);
            }

            $query->where('date', '<=', $this->validatedData['as_of_date']);

            $totalRows = (clone $query)->count();
            $processedRows = 0;
            $lastProgress = 1;

            $groupedData = [];
            $grandTotals = [
                '1_30' => 0,
                '31_60' => 0,
                '61_90' => 0,
                '91_360' => 0,
                'above_1_year' => 0,
            ];
            $customerCodes = [];

            // First pass to collect all customer codes
            $query->chunkById(500, function ($chunk) use (&$customerCodes) {
                $chunk->each(function ($invoice) use (&$customerCodes) {
                    $customerCodes[$invoice->customer_code] = true;
                });
            });

            // Get all customers in one query
            $customers = Customer::whereIn('cus_code', array_keys($customerCodes))
                ->get()
                ->keyBy('cus_code');

            // Process data in chunks
            $query->chunkById(500, function ($invoicesChunk) use (
                &$groupedData,
                &$grandTotals,
                $customers,
                &$processedRows,
                $totalRows,
                &$lastProgress
            ) {
                $chunkGrouped = $invoicesChunk->groupBy(function ($invoice) {
                    return $invoice->customer_code . '|' . $invoice->customer_name;
                });

                foreach ($chunkGrouped as $customerKey => $customerInvoices) {
                    [$customerCode, $customerName] = explode('|', $customerKey);

                    // Find or create customer data
                    $customerIndex = collect($groupedData)->search(
                        fn($customer) => $customer['customer_code'] === $customerCode
                    );

                    $customerData = $customerIndex !== false ? $groupedData[$customerIndex] : [
                        'customer_code' => $customerCode,
                        'customer_name' => $customerName,
                        'payment_terms' => $customers[$customerCode]->payment_terms ?? null,
                        'invoices' => [],
                        'totals' => [
                            '1_30' => 0,
                            '31_60' => 0,
                            '61_90' => 0,
                            '91_360' => 0,
                            'above_1_year' => 0,
                            'total' => 0
                        ]
                    ];

                    foreach ($customerInvoices as $invoice) {
                        $docDate = Carbon::parse($invoice->date);
                        $daysDiff = (int) Carbon::parse($invoice->date)->diffInDays(Carbon::now(), false);

                        $amount1_30 = ($daysDiff >= 0 && $daysDiff <= 30) ? $invoice->amount : 0;
                        $amount31_60 = ($daysDiff > 30 && $daysDiff <= 60) ? $invoice->amount : 0;
                        $amount61_90 = ($daysDiff > 60 && $daysDiff <= 90) ? $invoice->amount : 0;
                        $amount91_360 = ($daysDiff > 90 && $daysDiff <= 360) ? $invoice->amount : 0;
                        $amountAbove1Year = ($daysDiff > 360) ? $invoice->amount : 0;

                        $customerData['totals']['1_30'] += $amount1_30;
                        $customerData['totals']['31_60'] += $amount31_60;
                        $customerData['totals']['61_90'] += $amount61_90;
                        $customerData['totals']['91_360'] += $amount91_360;
                        $customerData['totals']['above_1_year'] += $amountAbove1Year;
                        $customerData['totals']['total'] += $invoice->amount;

                        $customerData['invoices'][] = [
                            'invoice_no' => $invoice->invoice_number,
                            'document_date' => $invoice->date,
                            'document_type' => $invoice->type,
                            'amount' => $invoice->amount,
                            'document_due_date' => isset($customers[$customerCode]->payment_terms)
                                ? Carbon::parse($invoice->date)
                                ->addDays((int)preg_replace('/[^0-9]/', '', $customers[$customerCode]->payment_terms))
                                ->format('Y-m-d')
                                : null,
                            'days_diff' => $daysDiff,
                            'amount_1_30' => $amount1_30,
                            'amount_31_60' => $amount31_60,
                            'amount_61_90' => $amount61_90,
                            'amount_91_360' => $amount91_360,
                            'amount_above_1_year' => $amountAbove1Year
                        ];

                        $processedRows++;
                        $progress = intval(($processedRows / $totalRows) * 100);

                        if ($progress > $lastProgress) {
                            $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                            $lastProgress = $progress;
                        }
                    }

                    // Update or add customer data
                    if ($customerIndex !== false) {
                        $groupedData[$customerIndex] = $customerData;
                    } else {
                        $groupedData[] = $customerData;
                    }
                }
            });

            // Calculate grand totals
            $grandTotals = [
                '1_30' => collect($groupedData)->sum('totals.1_30'),
                '31_60' => collect($groupedData)->sum('totals.31_60'),
                '61_90' => collect($groupedData)->sum('totals.61_90'),
                '91_360' => collect($groupedData)->sum('totals.91_360'),
                'above_1_year' => collect($groupedData)->sum('totals.above_1_year'),
            ];

            $this->updateProgress(98, 'Generating Report...');

            $data = [
                'dateRange' => "$formattedAsOfDate",
                'currency' => 'PHP',
                'groupedData' => $groupedData,
                'preparedBy' => $this->preparedBy,
                'grandTotals' => $grandTotals,
            ];

            $pdf = Pdf::loadView('pdf.Report.customerArAgingReport_pdf', $data)
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'margin_top' => 10,
                    'margin_right' => 10,
                    'margin_bottom' => 10,
                    'margin_left' => 10,
                ]);

            $this->updateProgress(99, 'Almost Done...');

            $filename = 'CustomerArAgingReport_' . time() . '_' . Str::random(6) . '.pdf';
            Storage::disk('public')->put("temp/{$filename}", $pdf->output());

            $prefix = trim(config('app.url'), '/');
            $publicUrl = $prefix . Storage::url("temp/{$filename}");

            $this->updateProgress(100, 'Report Ready!');

            broadcast(new PdfGenerated(
                $this->userId,
                $filename,
                $publicUrl,
                $this->channel
            ));
        } else {
            $query = PaymentDetails::where('payment_type', 'Check')
                ->orderBy('customer_code')
                ->orderBy('document_no');

            if ($this->validatedData['customer_type'] === 'By Customer') {
                $query->where('customer_code', $this->validatedData['customer_code']);
            }

            $query->where('payment_receipt_date', '<=', $this->validatedData['as_of_date']);

            $totalRows = (clone $query)->count();
            $processedRows = 0;
            $lastProgress = 1;

            $groupedData = [];
            $grandTotals = [
                '1_15' => 0,
                '16_30' => 0,
                '31_45' => 0,
                '46_60' => 0,
                '61_75' => 0,
                '76_90' => 0,
                'above_90_days' => 0,
            ];

            $query->chunkById(500, function ($invoicesChunk) use (
                &$groupedData,
                &$grandTotals,
                &$processedRows,
                $totalRows,
                &$lastProgress
            ) {
                $chunkGrouped = $invoicesChunk->groupBy(function ($invoice) {
                    return $invoice->customer_code . '|' . $invoice->customer_name;
                });

                foreach ($chunkGrouped as $customerKey => $customerInvoices) {
                    [$customerCode, $customerName] = explode('|', $customerKey);

                    // Find or create customer data
                    $customerIndex = collect($groupedData)->search(
                        fn($customer) => $customer['customer_code'] === $customerCode
                    );

                    $customerData = $customerIndex !== false ? $groupedData[$customerIndex] : [
                        'customer_code' => $customerCode,
                        'customer_name' => $customerName,
                        'check_types' => [],
                        'totals' => [
                            '1_15' => 0,
                            '16_30' => 0,
                            '31_45' => 0,
                            '46_60' => 0,
                            '61_75' => 0,
                            '76_90' => 0,
                            'above_90_days' => 0,
                            'total' => 0
                        ]
                    ];

                    $groupedByCheckType = $customerInvoices->groupBy('check_type');

                    foreach ($groupedByCheckType as $checkType => $invoicesByType) {
                        // Find or create check type data
                        $typeIndex = collect($customerData['check_types'])->search(
                            fn($type) => $type['check_type'] === $checkType
                        );

                        $typeData = $typeIndex !== false ? $customerData['check_types'][$typeIndex] : [
                            'check_type' => $checkType,
                            'invoices' => [],
                            'totals' => [
                                '1_15' => 0,
                                '16_30' => 0,
                                '31_45' => 0,
                                '46_60' => 0,
                                '61_75' => 0,
                                '76_90' => 0,
                                'above_90_days' => 0,
                                'total' => 0
                            ]
                        ];

                        foreach ($invoicesByType as $invoice) {
                            $daysDiff = (int) Carbon::parse($invoice->payment_receipt_date)->diffInDays(Carbon::now(), false);

                            $amount1_15 = ($daysDiff >= 0 && $daysDiff <= 15) ? $invoice->amount_paid : 0;
                            $amount16_30 = ($daysDiff > 15 && $daysDiff <= 30) ? $invoice->amount_paid : 0;
                            $amount31_45 = ($daysDiff > 30 && $daysDiff <= 45) ? $invoice->amount_paid : 0;
                            $amount46_60 = ($daysDiff > 45 && $daysDiff <= 60) ? $invoice->amount_paid : 0;
                            $amount61_75 = ($daysDiff > 60 && $daysDiff <= 75) ? $invoice->amount_paid : 0;
                            $amount76_90 = ($daysDiff > 75 && $daysDiff <= 90) ? $invoice->amount_paid : 0;
                            $amountAbove90Days = ($daysDiff > 90) ? $invoice->amount_paid : 0;

                            $cancelled = $invoice->remarks === 'Cancelled';
                            $amount = $cancelled ? 0 : $invoice->amount_paid;

                            $typeData['totals']['1_15'] += ($daysDiff >= 0 && $daysDiff <= 15) ? $amount : 0;
                            $typeData['totals']['16_30'] += ($daysDiff > 15 && $daysDiff <= 30) ? $amount : 0;
                            $typeData['totals']['31_45'] += ($daysDiff > 30 && $daysDiff <= 45) ? $amount : 0;
                            $typeData['totals']['46_60'] += ($daysDiff > 45 && $daysDiff <= 60) ? $amount : 0;
                            $typeData['totals']['61_75'] += ($daysDiff > 60 && $daysDiff <= 75) ? $amount : 0;
                            $typeData['totals']['76_90'] += ($daysDiff > 75 && $daysDiff <= 90) ? $amount : 0;
                            $typeData['totals']['above_90_days'] += ($daysDiff > 90) ? $amount : 0;
                            $typeData['totals']['total'] += $amount;

                            $customerData['totals']['1_15'] += ($daysDiff >= 0 && $daysDiff <= 15) ? $amount : 0;
                            $customerData['totals']['16_30'] += ($daysDiff > 15 && $daysDiff <= 30) ? $amount : 0;
                            $customerData['totals']['31_45'] += ($daysDiff > 30 && $daysDiff <= 45) ? $amount : 0;
                            $customerData['totals']['46_60'] += ($daysDiff > 45 && $daysDiff <= 60) ? $amount : 0;
                            $customerData['totals']['61_75'] += ($daysDiff > 60 && $daysDiff <= 75) ? $amount : 0;
                            $customerData['totals']['76_90'] += ($daysDiff > 75 && $daysDiff <= 90) ? $amount : 0;
                            $customerData['totals']['above_90_days'] += ($daysDiff > 90) ? $amount : 0;
                            $customerData['totals']['total'] += $amount;

                            $typeData['invoices'][] = [
                                'document_no' => $invoice->document_no,
                                'payment_receipt_date' => $invoice->payment_receipt_date,
                                'document_type' => $invoice->type,
                                'amount' => $invoice->amount_paid,
                                'document_due_date' => $invoice->due_date,
                                'check_no' => $invoice->check_no,
                                'check_type' => $invoice->check_type,
                                'remarks' => $invoice->remarks,
                                'days_diff' => $daysDiff,
                                'amount_1_15' => $amount1_15,
                                'amount_16_30' => $amount16_30,
                                'amount_31_45' => $amount31_45,
                                'amount_46_60' => $amount46_60,
                                'amount_61_75' => $amount61_75,
                                'amount_76_90' => $amount76_90,
                                'amount_above_90_days' => $amountAbove90Days
                            ];

                            $processedRows++;
                            $progress = intval(($processedRows / $totalRows) * 100);

                            if ($progress > $lastProgress) {
                                $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                                $lastProgress = $progress;
                            }
                        }

                        // Update or add check type data
                        if ($typeIndex !== false) {
                            $customerData['check_types'][$typeIndex] = $typeData;
                        } else {
                            $customerData['check_types'][] = $typeData;
                        }
                    }

                    // Update or add customer data
                    if ($customerIndex !== false) {
                        $groupedData[$customerIndex] = $customerData;
                    } else {
                        $groupedData[] = $customerData;
                    }
                }
            });

            // Calculate grand totals
            $grandTotals = [
                '1_15' => collect($groupedData)->sum('totals.1_15'),
                '16_30' => collect($groupedData)->sum('totals.16_30'),
                '31_45' => collect($groupedData)->sum('totals.31_45'),
                '46_60' => collect($groupedData)->sum('totals.46_60'),
                '61_75' => collect($groupedData)->sum('totals.61_75'),
                '76_90' => collect($groupedData)->sum('totals.76_90'),
                'above_90_days' => collect($groupedData)->sum('totals.above_90_days'),
            ];

            $this->updateProgress(98, 'Generating Report...');

            $data = [
                'dateRange' => "$formattedAsOfDate",
                'currency' => 'PHP',
                'groupedData' => $groupedData,
                'preparedBy' => $this->preparedBy,
                'grandTotals' => $grandTotals,
            ];

            $pdf = Pdf::loadView('pdf.Report.customerARPdcDcAgingReport_pdf', $data)
                ->setPaper('A4', 'landscape')
                ->setOptions([
                    'margin_top' => 10,    // in mm
                    'margin_right' => 10,
                    'margin_bottom' => 10,
                    'margin_left' => 10,
                ]);

            $this->updateProgress(99, 'Almost Done...');

            $filename = 'CustomerArPdcDcAgingReport_' . time() . '_' . Str::random(6) . '.pdf';
            Storage::disk('public')->put("temp/{$filename}", $pdf->output());

            $prefix = trim(config('app.url'), '/');
            $publicUrl = $prefix . Storage::url("temp/{$filename}");

            $this->updateProgress(100, 'Report Ready!');

            broadcast(new PdfGenerated(
                $this->userId,
                $filename,
                $publicUrl,
                $this->channel
            ));
        }
    }

    protected function generateBegBalProoflist()
    {
        $this->updateProgress(1, 'Preparing To Process Report...');

        $formattedStartDate = date('m/d/Y', strtotime($this->validatedData['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($this->validatedData['end_date']));

        $query = BeginningBalance::query()
            ->whereBetween('receipt_date', [$this->validatedData['start_date'], $this->validatedData['end_date']])
            ->orderBy('beginningbalance_no');

        $totalRows = (clone $query)->count();
        $processedRows = 0;
        $lastProgress = 1;

        $formattedBegBals = collect();
        $totalAmount = 0;

        $query->chunkById(500, function ($begBalsChunk) use (
            &$formattedBegBals,
            &$totalAmount,
            &$processedRows,
            $totalRows,
            &$lastProgress
        ) {
            foreach ($begBalsChunk as $begBal) {
                $formattedBegBals->push([
                    'beginningbalance_no' => $begBal->beginningbalance_no,
                    'date' => $begBal->receipt_date,
                    'customer' => $begBal->name,
                    'amount' => $begBal->balance_amount,
                ]);
                $totalAmount += $begBal->balance_amount;

                $processedRows++;
                $progress = intval(($processedRows / $totalRows) * 100);

                if ($progress > $lastProgress) {
                    $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                    $lastProgress = $progress;
                }
            }
        });

        $this->updateProgress(98, 'Generating Report...');

        $data = [
            'dateRange' => "$formattedStartDate to $formattedEndDate",
            'currency' => 'PHP',
            'begBals' => $formattedBegBals,
            'totalAmount' => $totalAmount,
            'preparedBy' => $this->preparedBy,
        ];

        $pdf = Pdf::loadView('pdf.Report.begBalProoflist_pdf', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'margin_top' => 10,
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
            ]);

        $this->updateProgress(99, 'Almost Done...');

        $filename = 'BegBalProoflistReport_' . time() . '_' . Str::random(6) . '.pdf';
        Storage::disk('public')->put("temp/{$filename}", $pdf->output());

        $prefix = trim(config('app.url'), '/');
        $publicUrl = $prefix . Storage::url("temp/{$filename}");

        $this->updateProgress(100, 'Report Ready!');

        broadcast(new PdfGenerated(
            $this->userId,
            $filename,
            $publicUrl,
            $this->channel
        ));
    }

    protected function generateArOutstandingBalanceAOReport()
    {
        $this->updateProgress(1, 'Preparing To Process Report...');

        $formattedAsOfDate = date('m/d/Y', strtotime($this->validatedData['as_of_date']));

        $query = CustomerLedger::orderBy('customer_code')
            ->orderBy('invoice_number')
            ->where('date', '<=', $this->validatedData['as_of_date'])
            ->where(function ($q) {
                $q->whereNull('si_payment_type')
                    ->orWhere('si_payment_type', '!=', 'Cash');
            });

        if ($this->validatedData['customer_type'] === 'All Customer') {
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
        } elseif ($this->validatedData['customer_type'] === 'By Customer') {
            $query->where('customer_code', $this->validatedData['customer_code']);
        }

        $totalRows = (clone $query)->count();
        $processedRows = 0;
        $lastProgress = 1;

        $groupedData = [];
        $customerOverallAmountTotal = 0;
        $customerCodes = [];

        // First pass to collect all customer codes
        $query->chunkById(500, function ($chunk) use (&$customerCodes) {
            $chunk->each(function ($item) use (&$customerCodes) {
                $customerCodes[$item->customer_code] = $item->customer_name;
            });
        });

        // Preload all floating PDC/DC and WHT amounts for these customers
        $floatingAmounts = [];
        if (!empty($customerCodes)) {
            $paymentDetails = PaymentDetails::whereIn('customer_code', array_keys($customerCodes))
                ->whereIn('payment_type', ['Check', 'Creditable(WHT)'])
                ->where('status', 'Floating')
                ->get();

            foreach ($paymentDetails as $detail) {
                $key = $detail->customer_code . '|' . $detail->document_no . '|' . $detail->type;
                if (!isset($floatingAmounts[$key])) {
                    $floatingAmounts[$key] = [
                        'pdc_dc' => 0,
                        'wht' => 0
                    ];
                }

                if ($detail->payment_type === 'Check') {
                    $floatingAmounts[$key]['pdc_dc'] += $detail->amount_paid;
                } else {
                    $floatingAmounts[$key]['wht'] += $detail->amount_paid;
                }
            }
        }

        // Process data in chunks
        $query->chunkById(500, function ($outstandingBalancesChunk) use (
            &$groupedData,
            &$customerOverallAmountTotal,
            $floatingAmounts,
            $customerCodes,
            &$processedRows,
            $totalRows,
            &$lastProgress
        ) {
            $chunkGrouped = $outstandingBalancesChunk->groupBy(function ($item) {
                return $item->customer_code . '|' . $item->customer_name;
            });

            foreach ($chunkGrouped as $customerKey => $customerOutstandingBalances) {
                [$customerCode, $customerName] = explode('|', $customerKey);

                // Find or create customer data
                $customerIndex = collect($groupedData)->search(
                    fn($customer) => $customer['customer_code'] === $customerCode
                );

                $customerData = $customerIndex !== false ? $groupedData[$customerIndex] : [
                    'customer_code' => $customerCode,
                    'customer_name' => $customerName,
                    'outstandingBalances' => [],
                    'customerAmountTotal' => 0
                ];

                foreach ($customerOutstandingBalances as $outstandingBalance) {
                    $key = $customerCode . '|' . $outstandingBalance->invoice_number . '|' . $outstandingBalance->type;
                    $floatingPdcDc = $floatingAmounts[$key]['pdc_dc'] ?? 0;
                    $floatingWht = $floatingAmounts[$key]['wht'] ?? 0;

                    $shrinkage = $outstandingBalance->shrinkage ?? 0;
                    $overage = $outstandingBalance->overage ?? 0;
                    $shrinkage_overage = $overage - $shrinkage;

                    $arNetAmount = $outstandingBalance->running_balance;
                    $customerData['customerAmountTotal'] += $arNetAmount;

                    $customerData['outstandingBalances'][] = [
                        'document_no' => $outstandingBalance->invoice_number,
                        'type' => $outstandingBalance->type,
                        'receipt_date' => $outstandingBalance->date,
                        'gross_amount' => $outstandingBalance->amount,
                        'shrinkage_overage' => $shrinkage_overage,
                        'return' => $outstandingBalance->return,
                        'adjustment' => $outstandingBalance->adjusted_amount,
                        'partial_payment' => $outstandingBalance->amount_paid,
                        'floating_pdc_dc' => $floatingPdcDc,
                        'floating_wht' => $floatingWht,
                        'ar_net_amount' => $arNetAmount,
                    ];

                    $processedRows++;
                    $progress = intval(($processedRows / $totalRows) * 100);

                    if ($progress > $lastProgress) {
                        $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                        $lastProgress = $progress;
                    }
                }

                // Update or add customer data
                if ($customerIndex !== false) {
                    $groupedData[$customerIndex] = $customerData;
                } else {
                    $groupedData[] = $customerData;
                }
            }
        });

        // Calculate overall total
        $customerOverallAmountTotal = collect($groupedData)->sum('customerAmountTotal');

        $this->updateProgress(98, 'Generating Report...');

        $data = [
            'dateRange' => "$formattedAsOfDate",
            'currency' => 'PHP',
            'groupedData' => $groupedData,
            'preparedBy' => $this->preparedBy,
            'customerOverallAmountTotal' => $customerOverallAmountTotal,
        ];

        $pdf = Pdf::loadView('pdf.Report.arOutstandingBalanceAO_pdf', $data)
            ->setPaper('A4', 'landscape')
            ->setOptions([
                'margin_top' => 10,    // in mm
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
            ]);

        $this->updateProgress(99, 'Almost Done...');

        $filename = 'ArOutstandingBalanceAOReport_' . time() . '_' . Str::random(6) . '.pdf';
        Storage::disk('public')->put("temp/{$filename}", $pdf->output());

        $prefix = trim(config('app.url'), '/');
        $publicUrl = $prefix . Storage::url("temp/{$filename}");

        $this->updateProgress(100, 'Report Ready!');

        broadcast(new PdfGenerated(
            $this->userId,
            $filename,
            $publicUrl,
            $this->channel
        ));
    }

    protected function generateArOutstandingBalanceDRReport()
    {
        $this->updateProgress(1, 'Preparing To Process Report...');

        $formattedStartDate = date('m/d/Y', strtotime($this->validatedData['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($this->validatedData['end_date']));

        $query = CustomerLedger::orderBy('customer_code')
            ->orderBy('invoice_number')
            ->whereBetween('date', [$this->validatedData['start_date'], $this->validatedData['end_date']])
            ->where(function ($q) {
                $q->whereNull('si_payment_type')
                    ->orWhere('si_payment_type', '!=', 'Cash');
            });

        if ($this->validatedData['customer_type'] === 'All Customer') {
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
        } elseif ($this->validatedData['customer_type'] === 'By Customer') {
            $query->where('customer_code', $this->validatedData['customer_code']);
        }

        $totalRows = (clone $query)->count();
        $processedRows = 0;
        $lastProgress = 1;

        $groupedData = [];
        $customerOverallAmountTotal = 0;
        $customerCodes = [];

        $query->chunkById(200, function ($chunk) use (&$customerCodes) {
            $chunk->each(function ($item) use (&$customerCodes) {
                $customerCodes[$item->customer_code] = $item->customer_name;
            });
        });

        $floatingAmounts = [];
        if (!empty($customerCodes)) {
            $paymentDetails = PaymentDetails::whereIn('customer_code', array_keys($customerCodes))
                ->whereIn('payment_type', ['Check', 'Creditable(WHT)'])
                ->where('status', 'Floating')
                ->get();

            foreach ($paymentDetails as $detail) {
                $key = $detail->customer_code . '|' . $detail->document_no . '|' . $detail->type;
                if (!isset($floatingAmounts[$key])) {
                    $floatingAmounts[$key] = [
                        'pdc_dc' => 0,
                        'wht' => 0
                    ];
                }

                if ($detail->payment_type === 'Check') {
                    $floatingAmounts[$key]['pdc_dc'] += $detail->amount_paid;
                } else {
                    $floatingAmounts[$key]['wht'] += $detail->amount_paid;
                }
            }
        }

        $query->chunkById(200, function ($outstandingBalancesChunk) use (
            &$groupedData,
            &$customerOverallAmountTotal,
            $floatingAmounts,
            $customerCodes,
            &$processedRows,
            $totalRows,
            &$lastProgress
        ) {
            $chunkGrouped = $outstandingBalancesChunk->groupBy(function ($item) {
                return $item->customer_code . '|' . $item->customer_name;
            });

            foreach ($chunkGrouped as $customerKey => $customerOutstandingBalances) {
                [$customerCode, $customerName] = explode('|', $customerKey);

                $customerIndex = collect($groupedData)->search(
                    fn($customer) => $customer['customer_code'] === $customerCode
                );

                $customerData = $customerIndex !== false ? $groupedData[$customerIndex] : [
                    'customer_code' => $customerCode,
                    'customer_name' => $customerName,
                    'outstandingBalances' => [],
                    'customerAmountTotal' => 0
                ];

                foreach ($customerOutstandingBalances as $outstandingBalance) {
                    $key = $customerCode . '|' . $outstandingBalance->invoice_number . '|' . $outstandingBalance->type;
                    $floatingPdcDc = $floatingAmounts[$key]['pdc_dc'] ?? 0;
                    $floatingWht = $floatingAmounts[$key]['wht'] ?? 0;

                    $shrinkage = $outstandingBalance->shrinkage ?? 0;
                    $overage = $outstandingBalance->overage ?? 0;
                    $shrinkage_overage = $overage - $shrinkage;

                    $arNetAmount = $outstandingBalance->running_balance;
                    $customerData['customerAmountTotal'] += $arNetAmount;

                    $customerData['outstandingBalances'][] = [
                        'document_no' => $outstandingBalance->invoice_number,
                        'type' => $outstandingBalance->type,
                        'receipt_date' => $outstandingBalance->date,
                        'gross_amount' => $outstandingBalance->amount,
                        'shrinkage_overage' => $shrinkage_overage,
                        'return' => $outstandingBalance->return,
                        'adjustment' => $outstandingBalance->adjusted_amount,
                        'partial_payment' => $outstandingBalance->amount_paid,
                        'floating_pdc_dc' => $floatingPdcDc,
                        'floating_wht' => $floatingWht,
                        'ar_net_amount' => $arNetAmount,
                    ];

                    $processedRows++;
                    $progress = intval(($processedRows / $totalRows) * 100);

                    if ($progress > $lastProgress) {
                        $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                        $lastProgress = $progress;
                    }
                }

                if ($customerIndex !== false) {
                    $groupedData[$customerIndex] = $customerData;
                } else {
                    $groupedData[] = $customerData;
                }
            }
        });

        $customerOverallAmountTotal = collect($groupedData)->sum('customerAmountTotal');

        $this->updateProgress(98, 'Generating Report...');

        $data = [
            'dateRange' => "$formattedStartDate to $formattedEndDate",
            'currency' => 'PHP',
            'groupedData' => $groupedData,
            'preparedBy' => $this->preparedBy,
            'customerOverallAmountTotal' => $customerOverallAmountTotal,
        ];

        $pdf = Pdf::loadView('pdf.Report.arOutstandingBalanceDR_pdf', $data)
            ->setPaper('A4', 'landscape')
            ->setOptions([
                'margin_top' => 10,    // in mm
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
            ]);

        $this->updateProgress(99, 'Almost Done...');

        $filename = 'ArOutstandingBalanceDRReport_' . time() . '_' . Str::random(6) . '.pdf';
        Storage::disk('public')->put("temp/{$filename}", $pdf->output());

        $prefix = trim(config('app.url'), '/');
        $publicUrl = $prefix . Storage::url("temp/{$filename}");

        $this->updateProgress(100, 'Report Ready!');

        broadcast(new PdfGenerated(
            $this->userId,
            $filename,
            $publicUrl,
            $this->channel
        ));
    }

    protected function generateArOutstandingBalanceAODataForExcel()
    {
        $this->updateProgress(1, 'Preparing To Process Report...');

        $formattedAsOfDate = date('m/d/Y', strtotime($this->validatedData['as_of_date']));

        $query = CustomerLedger::orderBy('customer_code')
            ->orderBy('invoice_number')
            ->where('date', '<=', $this->validatedData['as_of_date'])
            ->where(function ($q) {
                $q->whereNull('si_payment_type')
                    ->orWhere('si_payment_type', '!=', 'Cash');
            });

        if ($this->validatedData['customer_type'] === 'All Customer') {
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
        } elseif ($this->validatedData['customer_type'] === 'By Customer') {
            $query->where('customer_code', $this->validatedData['customer_code']);
        }

        $totalRows = (clone $query)->count();
        $processedRows = 0;
        $lastProgress = 1;

        $groupedData = [];
        $customerOverallAmountTotal = 0;
        $customerCodes = [];

        // First pass to collect all customer codes
        $query->chunkById(500, function ($chunk) use (&$customerCodes) {
            $chunk->each(function ($item) use (&$customerCodes) {
                $customerCodes[$item->customer_code] = $item->customer_name;
            });
        });

        // Preload all floating PDC/DC and WHT amounts for these customers
        $floatingAmounts = [];
        if (!empty($customerCodes)) {
            $paymentDetails = PaymentDetails::whereIn('customer_code', array_keys($customerCodes))
                ->whereIn('payment_type', ['Check', 'Creditable(WHT)'])
                ->where('status', 'Floating')
                ->get();

            foreach ($paymentDetails as $detail) {
                $key = $detail->customer_code . '|' . $detail->document_no . '|' . $detail->type;
                if (!isset($floatingAmounts[$key])) {
                    $floatingAmounts[$key] = [
                        'pdc_dc' => 0,
                        'wht' => 0
                    ];
                }

                if ($detail->payment_type === 'Check') {
                    $floatingAmounts[$key]['pdc_dc'] += $detail->amount_paid;
                } else {
                    $floatingAmounts[$key]['wht'] += $detail->amount_paid;
                }
            }
        }

        // Process data in chunks
        $query->chunkById(500, function ($outstandingBalancesChunk) use (
            &$groupedData,
            &$customerOverallAmountTotal,
            $floatingAmounts,
            $customerCodes,
            &$processedRows,
            $totalRows,
            &$lastProgress
        ) {
            $chunkGrouped = $outstandingBalancesChunk->groupBy(function ($item) {
                return $item->customer_code . '|' . $item->customer_name;
            });

            foreach ($chunkGrouped as $customerKey => $customerOutstandingBalances) {
                [$customerCode, $customerName] = explode('|', $customerKey);

                // Find or create customer data
                $customerIndex = collect($groupedData)->search(
                    fn($customer) => $customer['customer_code'] === $customerCode
                );

                $customerData = $customerIndex !== false ? $groupedData[$customerIndex] : [
                    'customer_code' => $customerCode,
                    'customer_name' => $customerName,
                    'outstandingBalances' => [],
                    'customerAmountTotal' => 0
                ];

                foreach ($customerOutstandingBalances as $outstandingBalance) {
                    $key = $customerCode . '|' . $outstandingBalance->invoice_number . '|' . $outstandingBalance->type;
                    $floatingPdcDc = $floatingAmounts[$key]['pdc_dc'] ?? 0;
                    $floatingWht = $floatingAmounts[$key]['wht'] ?? 0;

                    $shrinkage = $outstandingBalance->shrinkage ?? 0;
                    $overage = $outstandingBalance->overage ?? 0;
                    $shrinkage_overage = $overage - $shrinkage;

                    $arNetAmount = $outstandingBalance->running_balance;
                    $customerData['customerAmountTotal'] += $arNetAmount;

                    $customerData['outstandingBalances'][] = [
                        'document_no' => $outstandingBalance->invoice_number,
                        'type' => $outstandingBalance->type,
                        'receipt_date' => $outstandingBalance->date,
                        'gross_amount' => $outstandingBalance->amount,
                        'shrinkage_overage' => $shrinkage_overage,
                        'return' => $outstandingBalance->return,
                        'adjustment' => $outstandingBalance->adjusted_amount,
                        'partial_payment' => $outstandingBalance->amount_paid,
                        'floating_pdc_dc' => $floatingPdcDc,
                        'floating_wht' => $floatingWht,
                        'ar_net_amount' => $arNetAmount,
                    ];

                    $processedRows++;
                    $progress = intval(($processedRows / $totalRows) * 100);

                    if ($progress > $lastProgress) {
                        $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                        $lastProgress = $progress;
                    }
                }

                // Update or add customer data
                if ($customerIndex !== false) {
                    $groupedData[$customerIndex] = $customerData;
                } else {
                    $groupedData[] = $customerData;
                }
            }
        });

        // Calculate overall total
        $customerOverallAmountTotal = collect($groupedData)->sum('customerAmountTotal');

        $this->updateProgress(99, 'Preparing Excel Data...');

        // Send data to frontend instead of generating file
        $excelData = [
            'dateRange' => "$formattedAsOfDate",
            'currency' => 'PHP',
            'groupedData' => $groupedData,
            'preparedBy' => $this->preparedBy,
            'customerOverallAmountTotal' => $customerOverallAmountTotal,
            'runDateTime' => now()->format('m/d/Y h:i:s A'),
        ];

        $this->updateProgress(100, 'Data Ready for Excel Generation!');

        broadcast(new PdfGenerated(
            $this->userId,
            '',
            '',
            $this->channel,
            $excelData
        ));
    }

    protected function generateArOutstandingBalanceDRDataForExcel()
    {
        $this->updateProgress(1, 'Preparing To Process Report...');

        $formattedStartDate = date('m/d/Y', strtotime($this->validatedData['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($this->validatedData['end_date']));

        $query = CustomerLedger::orderBy('customer_code')
            ->orderBy('invoice_number')
            ->whereBetween('date', [$this->validatedData['start_date'], $this->validatedData['end_date']])
            ->where(function ($q) {
                $q->whereNull('si_payment_type')
                    ->orWhere('si_payment_type', '!=', 'Cash');
            });

        if ($this->validatedData['customer_type'] === 'All Customer') {
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
        } elseif ($this->validatedData['customer_type'] === 'By Customer') {
            $query->where('customer_code', $this->validatedData['customer_code']);
        }

        $totalRows = (clone $query)->count();
        $processedRows = 0;
        $lastProgress = 1;

        $groupedData = [];
        $customerOverallAmountTotal = 0;
        $customerCodes = [];

        $query->chunkById(200, function ($chunk) use (&$customerCodes) {
            $chunk->each(function ($item) use (&$customerCodes) {
                $customerCodes[$item->customer_code] = $item->customer_name;
            });
        });

        $floatingAmounts = [];
        if (!empty($customerCodes)) {
            $paymentDetails = PaymentDetails::whereIn('customer_code', array_keys($customerCodes))
                ->whereIn('payment_type', ['Check', 'Creditable(WHT)'])
                ->where('status', 'Floating')
                ->get();

            foreach ($paymentDetails as $detail) {
                $key = $detail->customer_code . '|' . $detail->document_no . '|' . $detail->type;
                if (!isset($floatingAmounts[$key])) {
                    $floatingAmounts[$key] = [
                        'pdc_dc' => 0,
                        'wht' => 0
                    ];
                }

                if ($detail->payment_type === 'Check') {
                    $floatingAmounts[$key]['pdc_dc'] += $detail->amount_paid;
                } else {
                    $floatingAmounts[$key]['wht'] += $detail->amount_paid;
                }
            }
        }

        $query->chunkById(200, function ($outstandingBalancesChunk) use (
            &$groupedData,
            &$customerOverallAmountTotal,
            $floatingAmounts,
            $customerCodes,
            &$processedRows,
            $totalRows,
            &$lastProgress
        ) {
            $chunkGrouped = $outstandingBalancesChunk->groupBy(function ($item) {
                return $item->customer_code . '|' . $item->customer_name;
            });

            foreach ($chunkGrouped as $customerKey => $customerOutstandingBalances) {
                [$customerCode, $customerName] = explode('|', $customerKey);

                $customerIndex = collect($groupedData)->search(
                    fn($customer) => $customer['customer_code'] === $customerCode
                );

                $customerData = $customerIndex !== false ? $groupedData[$customerIndex] : [
                    'customer_code' => $customerCode,
                    'customer_name' => $customerName,
                    'outstandingBalances' => [],
                    'customerAmountTotal' => 0
                ];

                foreach ($customerOutstandingBalances as $outstandingBalance) {
                    $key = $customerCode . '|' . $outstandingBalance->invoice_number . '|' . $outstandingBalance->type;
                    $floatingPdcDc = $floatingAmounts[$key]['pdc_dc'] ?? 0;
                    $floatingWht = $floatingAmounts[$key]['wht'] ?? 0;

                    $shrinkage = $outstandingBalance->shrinkage ?? 0;
                    $overage = $outstandingBalance->overage ?? 0;
                    $shrinkage_overage = $overage - $shrinkage;

                    $arNetAmount = $outstandingBalance->running_balance;
                    $customerData['customerAmountTotal'] += $arNetAmount;

                    $customerData['outstandingBalances'][] = [
                        'document_no' => $outstandingBalance->invoice_number,
                        'type' => $outstandingBalance->type,
                        'receipt_date' => $outstandingBalance->date,
                        'gross_amount' => $outstandingBalance->amount,
                        'shrinkage_overage' => $shrinkage_overage,
                        'return' => $outstandingBalance->return,
                        'adjustment' => $outstandingBalance->adjusted_amount,
                        'partial_payment' => $outstandingBalance->amount_paid,
                        'floating_pdc_dc' => $floatingPdcDc,
                        'floating_wht' => $floatingWht,
                        'ar_net_amount' => $arNetAmount,
                    ];

                    $processedRows++;
                    $progress = intval(($processedRows / $totalRows) * 100);

                    if ($progress > $lastProgress) {
                        $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                        $lastProgress = $progress;
                    }
                }

                if ($customerIndex !== false) {
                    $groupedData[$customerIndex] = $customerData;
                } else {
                    $groupedData[] = $customerData;
                }
            }
        });

        $customerOverallAmountTotal = collect($groupedData)->sum('customerAmountTotal');

        $this->updateProgress(99, 'Preparing Excel Data...');

        // Send data to frontend instead of generating file
        $excelData = [
            'dateRange' => "$formattedStartDate to $formattedEndDate",
            'currency' => 'PHP',
            'groupedData' => $groupedData,
            'preparedBy' => $this->preparedBy,
            'customerOverallAmountTotal' => $customerOverallAmountTotal,
            'runDateTime' => now()->format('m/d/Y h:i:s A'),
        ];

        $this->updateProgress(100, 'Data Ready for Excel Generation!');

        broadcast(new PdfGenerated(
            $this->userId,
            '',
            '',
            $this->channel,
            $excelData
        ));
    }

    protected function generateSalesPerItemReport()
    {
        $this->updateProgress(1, 'Preparing To Process Report...');

        $formattedStartDate = date('m/d/Y', strtotime($this->validatedData['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($this->validatedData['end_date']));

        $invoiceNos = Invoice::whereBetween('receipt_date', [
            $this->validatedData['start_date'],
            $this->validatedData['end_date']
        ])->pluck('invoice_no');

        $query = InvoiceItem::query()
            ->whereIn('invoice_no', $invoiceNos)
            ->when($this->validatedData['item_type'] === 'By Items', function ($q) {
                $q->whereIn('item_name', $this->validatedData['selectedItems']);
            })
            ->orderBy('item_code')
            ->orderBy('item_name');

        $totalRows = (clone $query)->count();
        $processedRows = 0;
        $lastProgress = 1;

        $formattedSalesPerItem = collect();
        $totalAmount = 0;

        $query->chunkById(200, function ($itemsChunk) use (
            &$formattedSalesPerItem,
            &$totalAmount,
            &$processedRows,
            $totalRows,
            &$lastProgress
        ) {
            foreach ($itemsChunk as $salesPerItem) {
                $totalAmount += $salesPerItem->amount;

                $formattedSalesPerItem->push([
                    'item_code' => $salesPerItem->item_code,
                    'item_name' => $salesPerItem->item_name,
                    'packing' => $salesPerItem->packing,
                    'quantity' => $salesPerItem->quantity,
                    'price' => $salesPerItem->price,
                    'amount' => $salesPerItem->amount,
                ]);

                $processedRows++;
                $progress = intval(($processedRows / $totalRows) * 100);

                if ($progress > $lastProgress) {
                    $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                    $lastProgress = $progress;
                }
            }
        });

        $this->updateProgress(98, 'Generating Report...');

        $data = [
            'dateRange' => "$formattedStartDate to $formattedEndDate",
            'currency' => 'PHP',
            'salesperItems' => $formattedSalesPerItem,
            'preparedBy' => $this->preparedBy,
            'totalAmount' => $totalAmount,
        ];

        $pdf = Pdf::loadView('pdf.Report.salesPerItem_pdf', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'margin_top' => 10,
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
            ]);

        $this->updateProgress(99, 'Almost Done...');

        $filename = 'SalesPerItemReport_' . time() . '_' . Str::random(6) . '.pdf';
        Storage::disk('public')->put("temp/{$filename}", $pdf->output());

        $prefix = trim(config('app.url'), '/');
        $publicUrl = $prefix . Storage::url("temp/{$filename}");

        $this->updateProgress(100, 'Report Ready!');

        broadcast(new PdfGenerated(
            $this->userId,
            $filename,
            $publicUrl,
            $this->channel
        ));
    }

    protected function generateStatementOfAccountReport()
    {
        $this->updateProgress(1, 'Preparing To Process Report...');

        $formattedStartDate = date('F j, Y', strtotime($this->validatedData['start_date']));
        $formattedEndDate = date('F j, Y', strtotime($this->validatedData['end_date']));
        $formattedStatementDate = date('F j, Y', strtotime($this->validatedData['statement_date']));

        $query = CustomerLedger::orderBy('customer_code')
            ->orderBy('invoice_number')
            ->where('type', $this->validatedData['type']);

        $query->where(function ($q) {
            $q->whereNull('si_payment_type')
                ->orWhere('si_payment_type', '!=', 'Cash');
        });

        if ($this->validatedData['customer_type'] === 'By Customer') {
            $query->where('customer_code', $this->validatedData['customer_code']);
        }

        $query->whereBetween('date', [$this->validatedData['start_date'], $this->validatedData['end_date']]);

        $totalRows = (clone $query)->count();
        $processedRows = 0;
        $lastProgress = 1;

        $groupedData = [];

        $query->chunkById(500, function ($ledgerChunk) use (
            &$groupedData,
            &$processedRows,
            $totalRows,
            &$lastProgress
        ) {
            $grouped = $ledgerChunk->groupBy(function ($item) {
                return $item->customer_code . '|' . $item->customer_name;
            });

            foreach ($grouped as $customerKey => $customerPaymentDetails) {
                [$customerCode, $customerName] = explode('|', $customerKey);

                $customerData = [
                    'customer_code' => $customerCode,
                    'customer_name' => $customerName,
                    'paymentDetails' => [],
                    'address' => Customer::where('cus_code', $customerCode)->value('cus_address'),
                ];

                $beginningBalance = CustomerLedger::where('customer_code', $customerCode)
                    ->where('type', $this->validatedData['type'])
                    ->where('date', '<', $this->validatedData['start_date'])
                    ->sum('running_balance');

                $customerData['beginning_balance'] = number_format($beginningBalance, 2, '.', '');

                $totalBalance = 0;

                foreach ($customerPaymentDetails as $paymentDetail) {
                    $floatingPdcDc = (float) PaymentDetails::where([
                        ['customer_code', $customerCode],
                        ['document_no', $paymentDetail->invoice_number],
                        ['payment_type', 'Check'],
                        ['type', $paymentDetail->type],
                        ['status', 'Floating'],
                    ])->sum('amount_paid');

                    $floatingWht = (float) PaymentDetails::where([
                        ['customer_code', $customerCode],
                        ['document_no', $paymentDetail->invoice_number],
                        ['payment_type', 'Creditable(WHT)'],
                        ['type', $paymentDetail->type],
                        ['status', 'Floating'],
                    ])->sum('amount_paid');

                    $balance = $this->validatedData['soatype'] === 'SOA'
                        ? $paymentDetail->running_balance
                        : $paymentDetail->running_balance - ($floatingPdcDc + $floatingWht);

                    $totalBalance += $balance;

                    $adjustments = Adjustment::where('invoice_no', $paymentDetail->invoice_number)->get();
                    $groupedAdjustments = [];

                    foreach ($adjustments as $adjustment) {
                        $reason = $adjustment->adjustment_reason;
                        $amount = $adjustment->amount;
                        $groupedAdjustments[$reason] = $adjustment->type === 'Positive' ? $amount : '-' . $amount;
                    }

                    $agingDays = (int) Carbon::parse($paymentDetail->date)->diffInDays(Carbon::now(), false);

                    $customerData['paymentDetails'][] = [
                        'document_no' => $paymentDetail->invoice_number,
                        'date' => $paymentDetail->date,
                        'type' => $paymentDetail->type,
                        'amount' => $paymentDetail->amount,
                        'partial_payment' => $paymentDetail->amount_paid,
                        'balance' => $balance,
                        'floating_pdc_dc' => number_format($floatingPdcDc, 2, '.', ''),
                        'floating_wht' => number_format($floatingWht, 2, '.', ''),
                        'agingDays' => $agingDays,
                        'adjustment_reason' => $groupedAdjustments,
                    ];

                    $processedRows++;
                    $progress = intval(($processedRows / $totalRows) * 100);

                    if ($progress > $lastProgress) {
                        $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                        $lastProgress = $progress;
                    }
                }

                $customerData['total_balance'] = number_format($totalBalance, 2, '.', '');

                $customerData['total_balance_words'] = $this->convertToPesosWords($totalBalance);
                $groupedData[] = $customerData;
            }
        });

        $this->updateProgress(98, 'Generating Report...');

        $data = [
            'dateRange' => "$formattedStartDate to $formattedEndDate",
            'statement_date' => $formattedStatementDate,
            'groupedData' => $groupedData,
            'preparedBy' => $this->preparedBy,
        ];

        $view = $this->validatedData['soatype'] === 'SOA'
            ? 'pdf.Report.statementOfAccount_pdf'
            : 'pdf.Report.statementOfAccountDFC_pdf';
        $pdf = Pdf::loadView($view, $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'margin_top' => 10,
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
            ]);

        $this->updateProgress(99, 'Almost Done...');

        $soafilename = $this->validatedData['soatype'] === 'SOA'
            ? 'StatementOfAccount'
            : 'StatementOfAccountDFC';

        $filename = $soafilename . 'Report_' . time() . '_' . Str::random(6) . '.pdf';
        Storage::disk('public')->put("temp/{$filename}", $pdf->output());

        $prefix = trim(config('app.url'), '/');
        $publicUrl = $prefix . Storage::url("temp/{$filename}");

        $this->updateProgress(100, 'Report Ready!');

        broadcast(new PdfGenerated(
            $this->userId,
            $filename,
            $publicUrl,
            $this->channel
        ));
    }

    protected function generateStatementOfAccountSummaryReport()
    {

        $this->updateProgress(1, 'Preparing To Process Report...');

        $formattedStartDate = date('m/d/Y', strtotime($this->validatedData['start_date']));
        $formattedEndDate = date('m/d/Y', strtotime($this->validatedData['end_date']));

        $query = CustomerLedger::orderBy('customer_code')
            ->orderBy('invoice_number');

        $query->where(function ($q) {
            $q->whereNull('si_payment_type')
                ->orWhere('si_payment_type', '!=', 'Cash');
        });

        if ($this->validatedData['customer_type'] === 'By Customer Code') {
            $query->where('customer_code', $this->validatedData['customer_code']);
        }

        if ($this->validatedData['customer_type'] === 'By Customer Type') {
            $customerCodesQuery = Customer::select('cus_code');

            $customerCodesQuery->where('cus_type', $this->validatedData['customer_select_type']);

            $customerCodes = $customerCodesQuery->pluck('cus_code');

            $query->whereIn('customer_code', $customerCodes);
        }

        $query->whereBetween('date', [$this->validatedData['start_date'], $this->validatedData['end_date']]);

        $totalRows = (clone $query)->count();
        $processedRows = 0;
        $lastProgress = 1;

        $groupedData = [];
        $customerOverallAmountTotal = 0;

        $query->chunkById(200, function ($chunk) use (
            &$groupedData,
            &$customerOverallAmountTotal,
            &$processedRows,
            $totalRows,
            &$lastProgress
        ) {
            $chunkGrouped = $chunk->groupBy(function ($item) {
                return $item->customer_code . '|' . $item->customer_name;
            });

            foreach ($chunkGrouped as $customerKey => $customerPaymentDetails) {
                [$customerCode, $customerName] = explode('|', $customerKey);
                $customerAmountTotal = 0;

                $existingCustomerIndex = collect($groupedData)->search(fn($c) => $c['customer_code'] === $customerCode);

                if ($existingCustomerIndex !== false) {
                    $customerData = $groupedData[$existingCustomerIndex];
                } else {
                    $customerData = [
                        'customer_code' => $customerCode,
                        'customer_name' => $customerName,
                        'customerAmountTotal' => 0,
                        'paymentDetails' => []
                    ];
                }

                foreach ($customerPaymentDetails as $paymentDetail) {
                    $type = $paymentDetail->type;

                    if (!isset($customerData['paymentDetails'][$type])) {
                        $customerData['paymentDetails'][$type] = [];
                    }

                    $customerAmountTotal += $paymentDetail->running_balance;

                    $customerData['paymentDetails'][$type][] = [
                        'document_no' => $paymentDetail->invoice_number,
                        'date' => $paymentDetail->date,
                        'amount' => $paymentDetail->running_balance,
                    ];


                    $processedRows++;
                    $progress = intval(($processedRows / $totalRows) * 100);

                    if ($progress > $lastProgress) {
                        $this->updateProgress($progress, "Processing Report... ({$processedRows}/{$totalRows})");
                        $lastProgress = $progress;
                    }
                }

                $customerData['customerAmountTotal'] += $customerAmountTotal;
                $customerOverallAmountTotal += $customerAmountTotal;

                if ($existingCustomerIndex !== false) {
                    $groupedData[$existingCustomerIndex] = $customerData;
                } else {
                    $groupedData[] = $customerData;
                }
            }
        });

        $this->updateProgress(98, 'Generating Report...');

        $data = [
            'dateRange' => "$formattedStartDate to $formattedEndDate",
            'groupedData' => $groupedData,
            'preparedBy' => $this->preparedBy,
            'customerOverallAmountTotal' => $customerOverallAmountTotal,
        ];

        $pdf = Pdf::loadView('pdf.Report.statementOfAccountSummary_pdf', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'margin_top' => 10,
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10,
            ]);

        $this->updateProgress(99, 'Almost Done...');

        $filename = 'StatementOfAccountSummaryReport_' . time() . '_' . Str::random(6) . '.pdf';
        Storage::disk('public')->put("temp/{$filename}", $pdf->output());

        $prefix = trim(config('app.url'), '/');
        $publicUrl = $prefix . Storage::url("temp/{$filename}");

        $this->updateProgress(100, 'Report Ready!');

        broadcast(new PdfGenerated(
            $this->userId,
            $filename,
            $publicUrl,
            $this->channel
        ));
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
