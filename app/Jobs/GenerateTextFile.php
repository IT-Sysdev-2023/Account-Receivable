<?php

namespace App\Jobs;

use App\Events\ExportTextFileGenerated;
use App\Events\ExportTextFileGenerationProgress;
use App\Models\AccCode;
use App\Models\MasterfileModels\AdjustmentReasonSetup;
use App\Models\MasterfileModels\CashInBank;
use App\Models\MasterfileModels\Customer;
use App\Models\MasterfileModels\Item;
use App\Models\TransactionModels\Adjustment;
use App\Models\TransactionModels\Invoice;
use App\Models\TransactionModels\Payment;
use App\Services\GlobalApiServices;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateTextFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected array $validatedData,
        protected string $userId,
        protected string $channel,
    ) {}


    public function handle(GlobalApiServices $globalApi)
    {
        switch ($this->validatedData["export_type"]) {
            case 'Other Income':
                $this->otherIncomeTextFile();
                break;
            case 'Adjustment':
                $this->adjustmentTextFile();
                break;
            case 'Payment':
                $this->paymentTextFile($globalApi);
                break;

            default:
                break;
        }
    }

    protected function updateProgress(int $progress, string $message)
    {
        try {
            broadcast(new ExportTextFileGenerationProgress(
                $this->userId,
                $progress,
                $message
            ));
        } catch (\Exception $e) {
            Log::error("Progress update failed: " . $e->getMessage());
        }
    }

    protected function otherIncomeTextFile()
    {
        try {
            $this->updateProgress(1, 'Preparing To Process Text File...');

            $query = Invoice::with('items')
                ->whereBetween('receipt_date', [
                    $this->validatedData['start_date'],
                    $this->validatedData['end_date']
                ])
                ->where('exported', false)
                ->orderBy('receipt_date');

            Storage::disk('local')->makeDirectory('exports');

            $filename = 'BB_OCASHSALES' . $this->formatDateForName($this->validatedData['start_date']) . '_' . $this->formatDateForName($this->validatedData['end_date']) . '-' . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT) . '.txt';
            $storagePath = "exports/{$filename}";

            $customers = Customer::all()->keyBy('cus_code');
            $accCodes = AccCode::all()->keyBy('gl_account_navcode');
            $bankNames = Payment::all()->keyBy('document_no');
            $banks = CashInBank::all()->keyBy('bank_name');
            $itemsList = Item::all()->keyBy('name');

            $auto_increment = 0;

            $totalRows = (clone $query)->count();
            $processedRows = 0;
            $lastProgress = 1;

            $stream = fopen('php://temp', 'w+');

            DB::transaction(function () use (
                $query,
                $storagePath,
                &$stream,
                &$auto_increment,
                &$totalRows,
                &$processedRows,
                &$lastProgress,
                $customers,
                $accCodes,
                $bankNames,
                $banks,
                $itemsList,
            ) {

                $query->chunkById(500, function ($invoices) use (
                    &$stream,
                    &$auto_increment,
                    &$totalRows,
                    &$processedRows,
                    &$lastProgress,
                    $customers,
                    $accCodes,
                    $bankNames,
                    $banks,
                    $itemsList,
                ) {
                    $idsToMark = [];
                    $lines = [];
                    foreach ($invoices as $invoice) {

                        $customerCusNavCode = $customers->get($invoice->customer_code)?->nav_code ?? '';
                        $customerCusNavCodeDescription = $accCodes->get($customerCusNavCode)?->gl_account_name ?? '';
                        $bankName = $bankNames->get($invoice->invoice_no)?->cash_in_bank ?? '';
                        $bankCode = $banks->get($bankName)?->bank_code ?? '';

                        $itemName = $invoice->items->first()?->item_name ?? '';
                        $itemCode = $itemsList->get($itemName)?->acc_code ?? '';

                        $lines[] = $this->generateOtherIncomeLine(
                            $invoice,
                            $auto_increment,
                            $customerCusNavCode,
                            $customerCusNavCodeDescription,
                            $bankCode,
                            $itemName,
                            $itemCode
                        );


                        $processedRows++;
                        $progress = intval(($processedRows / $totalRows) * 100);

                        if ($progress > $lastProgress) {
                            $this->updateProgress($progress, "Processing Text File... ({$processedRows}/{$totalRows})");
                            $lastProgress = $progress;
                        }

                        $idsToMark[] = $invoice->id;
                    }
                    if (!empty($idsToMark)) {
                        DB::table('invoice')
                            ->whereIn('id', $idsToMark)
                            ->update(['exported' => true]);
                    }
                    fwrite($stream, implode("", $lines));
                });
                $this->updateProgress(98, 'Generating Text File...');


                // Save to storage
                rewind($stream);
                Storage::disk('local')->writeStream($storagePath, $stream);
                fclose($stream);
            });

            $this->updateProgress(99, 'Almost Done...');
            // Generate URL
            $privateUrl = route('exports.download', ['filename' => $filename]);

            DB::table('invoice')
                ->whereBetween('receipt_date', [
                    $this->validatedData['start_date'],
                    $this->validatedData['end_date']
                ])
                ->update(['exported' => true]);

            $this->updateProgress(100, 'Ready to Download!');

            broadcast(new ExportTextFileGenerated(
                $this->userId,
                $filename,
                $privateUrl,
                $this->channel
            ));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    protected function adjustmentTextFile()
    {
        try {
            $this->updateProgress(1, 'Preparing To Process Text File...');

            $query = Adjustment::whereBetween('receipt_date', [
                $this->validatedData['start_date'],
                $this->validatedData['end_date']
            ])
                ->where('exported', false)
                ->orderBy('receipt_date');

            Storage::disk('local')->makeDirectory('exports');

            $filename = 'BB_ADJSALES' . $this->formatDateForName($this->validatedData['start_date']) . '_' . $this->formatDateForName($this->validatedData['end_date']) . '-' . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT) . '.txt';
            $storagePath = "exports/{$filename}";

            $adjAccCode = AdjustmentReasonSetup::all()->keyBy('reason_name');
            $customers = Customer::all()->keyBy('cus_code');

            $auto_increment = 0;

            $totalRows = (clone $query)->count();
            $processedRows = 0;
            $lastProgress = 1;

            $stream = fopen('php://temp', 'w+');

            DB::transaction(function () use (
                $query,
                $storagePath,
                &$stream,
                &$auto_increment,
                &$totalRows,
                &$processedRows,
                &$lastProgress,
                $adjAccCode,
                $customers,
            ) {
                $query->chunkById(500, function ($adjustments) use (
                    &$stream,
                    &$auto_increment,
                    &$totalRows,
                    &$processedRows,
                    &$lastProgress,
                    $adjAccCode,
                    $customers,
                ) {
                    $idsToMark = [];
                    $lines = [];
                    foreach ($adjustments as $adjustment) {

                        $adjustmentAccCode = $adjAccCode->get($adjustment->adjustment_reason)?->acc_code ?? '';
                        $customerCusPosting = $customers->get($adjustment->customer_code)?->cus_posting ?? '';

                        if ($adjustment->type === 'Negative') {
                            $lines[] = $this->generateCreditAdjustmentLine(
                                $adjustment,
                                $auto_increment,
                                $adjustmentAccCode,
                                $customerCusPosting,
                            );
                        } elseif ($adjustment->type === 'Positive') {
                            $lines[] = $this->generateDebitAdjustmentLine(
                                $adjustment,
                                $auto_increment,
                                $adjustmentAccCode,
                                $customerCusPosting,
                            );
                        }


                        $processedRows++;
                        $progress = intval(($processedRows / $totalRows) * 100);

                        if ($progress > $lastProgress) {
                            $this->updateProgress($progress, "Processing Text File... ({$processedRows}/{$totalRows})");
                            $lastProgress = $progress;
                        }

                        $idsToMark[] = $adjustment->id;
                    }

                    if (!empty($idsToMark)) {
                        DB::table('adjustment')
                            ->whereIn('id', $idsToMark)
                            ->update(['exported' => true]);
                    }

                    fwrite($stream, implode("", $lines));
                });
                $this->updateProgress(98, 'Generating Text File...');


                // Save to storage
                rewind($stream);
                Storage::disk('local')->writeStream($storagePath, $stream);
                fclose($stream);
            });

            $this->updateProgress(99, 'Almost Done...');

            // Generate URL
            $privateUrl = route('exports.download', ['filename' => $filename]);

            DB::table('adjustment')
                ->whereBetween('receipt_date', [
                    $this->validatedData['start_date'],
                    $this->validatedData['end_date']
                ])
                ->update(['exported' => true]);

            $this->updateProgress(100, 'Ready to Download!');

            broadcast(new ExportTextFileGenerated(
                $this->userId,
                $filename,
                $privateUrl,
                $this->channel,
            ));
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    protected function paymentTextFile(GlobalApiServices $globalApi)
    {
        try {
            $this->updateProgress(1, 'Preparing To Process Text File...');

            $query = Payment::with(['paymentDetails' => function ($q) {
                $q->where('status', '!=', 'Floating')
                    ->where('status', '!=', 'Cancelled');
            }])
                ->whereBetween('receipt_date', [
                    $this->validatedData['start_date'],
                    $this->validatedData['end_date']
                ])
                ->where('exported', false)
                ->orderBy('receipt_date');

            Storage::disk('local')->makeDirectory('exports');

            $filename = 'BB_BBCOLL' . $this->formatDateForName($this->validatedData['start_date']) . '_' . $this->formatDateForName($this->validatedData['end_date']) . '-' . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT) . '.txt';
            $storagePath = "exports/{$filename}";

            $auto_increment = 0;
            $cashInBanks = CashInBank::all()->keyBy('bank_name');
            $customers = Customer::all()->keyBy('cus_code');
            $accCodes = AccCode::all()->keyBy('gl_account_navcode');

            $paymentAccountCode = $this->getPaymentAccCode('5E', $globalApi );
            $paymentAccountCodeDescription = $this->getPaymentAccCodeDescription('5E', $globalApi);

            $totalRows = (clone $query)->count();
            $processedRows = 0;
            $lastProgress = 1;

            $stream = fopen('php://temp', 'w+');

            DB::transaction(function () use (
                $query,
                $stream,
                $storagePath,
                $auto_increment,
                $cashInBanks,
                $customers,
                $accCodes,
                $paymentAccountCode,
                $paymentAccountCodeDescription,
                $processedRows,
                $totalRows,
                &$lastProgress
            ) {

                $query->chunkById(500, function ($payments) use (
                    &$stream,
                    &$auto_increment,
                    $cashInBanks,
                    $customers,
                    $accCodes,
                    $paymentAccountCode,
                    $paymentAccountCodeDescription,
                    &$processedRows,
                    $totalRows,
                    &$lastProgress
                ) {
                    $idsToMark = [];
                    $lines = [];
                    foreach ($payments as $payment) {
                        $bankCode = $cashInBanks->get($payment->cash_in_bank)?->bank_code ?? '';
                        $bankName = $cashInBanks->get($payment->cash_in_bank)?->bank_name ?? '';

                        $customerNavCode = $customers->get($payment->customer_code)?->nav_code ?? '';
                        $customerCusPosting = $customers->get($payment->customer_code)?->cus_posting ?? '';


                        $accCodeName = $accCodes->get($payment->acc_code)?->gl_account_name ?? '';

                        foreach ($payment->paymentDetails as $detail) {
                            if ($payment->payment_type === '5A - Cash') {
                                $lines[] = $this->generateCashPaymentLine(
                                    $auto_increment,
                                    $bankCode,
                                    $detail,
                                    $bankName,
                                    $customerNavCode
                                );
                            } elseif ($payment->payment_type === '5B - Journal Voucher') {
                                $lines[] = $this->generateJournalVoucherLine(
                                    $auto_increment,
                                    $detail,
                                    $customerNavCode,
                                    $customerCusPosting,
                                    $payment->customer_code,
                                    $payment->customer_name,
                                    $payment->acc_code,
                                    $accCodeName
                                );
                            } elseif ($payment->payment_type === '5C - Online Deposit') {
                                $lines[] = $this->generateOnlineDepositLine(
                                    $auto_increment,
                                    $detail,
                                    $bankCode,
                                    $bankName,
                                    $customerNavCode,
                                    $customerCusPosting,
                                    $payment->customer_code,
                                    $payment->customer_name,
                                    $payment->acc_code,
                                    $accCodeName
                                );
                            } elseif ($payment->payment_type === '5E - Creditable(WHT)') {
                                if ($detail->status === 'Floating') {
                                    continue;
                                }
                                $lines[] = $this->generateWHTLine(
                                    $auto_increment,
                                    $detail,
                                    $paymentAccountCode,
                                    $paymentAccountCodeDescription,
                                    $bankCode,
                                    $bankName,
                                    $customerNavCode,
                                    $customerCusPosting,
                                    $payment->customer_code,
                                    $payment->customer_name,
                                    $payment->acc_code,
                                    $accCodeName
                                );
                            }
                            $processedRows++;
                        }

                        $progress = intval(($processedRows / $totalRows) * 100);

                        if ($progress > $lastProgress) {
                            $this->updateProgress($progress, "Processing Text File... ({$processedRows}/{$totalRows})");
                            $lastProgress = $progress;
                        }

                        if ($payment->paymentDetails->isNotEmpty()) {
                            $idsToMark[] = $payment->id;
                        }
                    }
                    if (!empty($idsToMark)) {
                        DB::table('payment')
                            ->whereIn('id', $idsToMark)
                            ->update(['exported' => true]);
                    }

                    fwrite($stream, implode("", $lines));
                });

                $this->updateProgress(98, 'Generating Text File...');


                // Save to storage
                rewind($stream);
                Storage::disk('local')->writeStream($storagePath, $stream);
                fclose($stream);
            });
            $this->updateProgress(99, 'Almost Done...');
            // Generate URL
            $privateUrl = route('exports.download', ['filename' => $filename]);

            $this->updateProgress(100, 'Ready to Download!');

            broadcast(new ExportTextFileGenerated(
                $this->userId,
                $filename,
                $privateUrl,
                $this->channel
            ));
        } catch (Exception $e) {
            Log::error("TextFile generation failed: " . $e->getMessage());

            throw $e;
        }
    }

    protected function generateOtherIncomeLine($invoice, &$auto_increment, $customerCusNavCode, $customerCusNavCodeDescription, $bankCode, $itemName, $itemCode)
    {
        $formattedDate = $this->formatDate($invoice->receipt_date);
        $headerLine = [
            'SALES',
            'OCASHSALES',
            ($auto_increment += 10000),
            'G/L Account',
            $customerCusNavCode === '' ? $invoice->customer_code : $customerCusNavCode,
            $formattedDate,
            'Invoice',
            'BBCI' . $invoice->invoice_no,
            $customerCusNavCodeDescription,
            'PHP',
            $invoice->total_amount,
            $invoice->total_amount,
            ' ',
            $invoice->total_amount,
            $invoice->total_amount,
            '1',
            '3',
            '03.01.2.02.2',
            'SALESJNL',
            $invoice->total_amount,
            ($invoice->total_amount * -1),
            $formattedDate,
            'CASH SALES',
            'Bank Account',
            $invoice->payment_mode === 'Cash' ? $bankCode : ' ',
            $invoice->total_amount,
            ($invoice->total_amount * -1)
        ];

        $detailLine = [
            'SALES',
            'OCASHSALES',
            ($auto_increment += 10000),
            'G/L Account',
            $itemCode,
            $formattedDate,
            'Invoice',
            'BBCI' . $invoice->invoice_no,
            $invoice->type . $itemName,
            'PHP',
            ($invoice->total_amount * -1),
            ' ',
            $invoice->total_amount,
            ($invoice->total_amount * -1),
            ($invoice->total_amount * -1),
            '1',
            '3',
            '03.01.2.02.2',
            'SALESJNL',
            ($invoice->total_amount * -1),
            $invoice->total_amount,
            $formattedDate,
            'CASH SALES',
            ' ',
            ' ',
            ($invoice->total_amount * -1),
            $invoice->total_amount,
        ];

        return implode(',', $headerLine) . "\n" . implode(',', $detailLine) . "\n";
    }

    protected function generateCreditAdjustmentLine($adjustment, &$auto_increment, $adjustmentAccCode, $customerCusPosting)
    {
        $formattedDate = $this->formatDate($adjustment->receipt_date);
        $headerLine = [
            'SALES',
            'ADJSALES',
            ($auto_increment += 10000),
            'G/L Account',
            $adjustmentAccCode,
            $formattedDate,
            'Credit Memo',
            'BBARCM' . $adjustment->adjustment_no,
            $adjustment->adjustment_reason,
            'PHP',
            $adjustment->amount,
            ' ',
            $adjustment->amount,
            $adjustment->amount,
            $adjustment->amount,
            '1',
            ' ',
            ' ',
            '3',
            '03.01.2.02.2',
            'SALESJNL',
            ' ',
            ' ',
            ' ',
            $adjustment->amount,
            ($adjustment->amount * -1),
            $formattedDate,
            $adjustment->apply_to == 'Sales Invoice' ? 'BBSI#' . $adjustment->invoice_no . '/' . $adjustment->particulars : 'BBCI#' . $adjustment->invoice_no . '/' . $adjustment->particulars,
            ' ',
            ' ',
            $adjustment->amount,
            ($adjustment->amount * -1)
        ];

        $detailLine = [
            'SALES',
            'ADJSALES',
            ($auto_increment += 10000),
            'Customer',
            $adjustment->customer_code,
            $formattedDate,
            'Credit Memo',
            'BBARCM' . $adjustment->adjustment_no,
            $adjustment->name,
            'PHP',
            ($adjustment->amount * -1),
            $adjustment->amount,
            ' ',
            ($adjustment->amount * -1),
            ($adjustment->amount * -1),
            '1',
            $adjustment->customer_code,
            $customerCusPosting,
            '3',
            '03.01.2.02.2',
            'SALESJNL',
            'Invoice',
            $adjustment->apply_to == 'Sales Invoice' ? 'BBSI' . $adjustment->invoice_no : 'BBCI' . $adjustment->invoice_no,
            $formattedDate,
            ($adjustment->amount * -1),
            $adjustment->amount,
            $formattedDate,
            $adjustment->apply_to == 'Sales Invoice' ? 'BBSI#' . $adjustment->invoice_no . '/' . $adjustment->particulars : 'BBCI#' . $adjustment->invoice_no . '/' . $adjustment->particulars,
            'Customer',
            ' ',
            ($adjustment->amount * -1),
            $adjustment->amount
        ];

        return implode(',', $headerLine) . "\n" . implode(',', $detailLine) . "\n";
    }

    protected function generateDebitAdjustmentLine($adjustment, &$auto_increment, $adjustmentAccCode, $customerCusPosting)
    {
        $formattedDate = $this->formatDate($adjustment->receipt_date);
        $headerLine = [
            'SALES',
            'ADJSALES',
            ($auto_increment += 10000),
            'Customer',
            $adjustment->customer_code,
            $formattedDate,
            'Invoice',
            'BBARCM' . $adjustment->adjustment_no,
            $adjustment->name,
            'PHP',
            $adjustment->amount,
            $adjustment->amount,
            ' ',
            $adjustment->amount,
            $adjustment->amount,
            '1',
            $adjustment->customer_code,
            $customerCusPosting,
            '3',
            '03.01.2.02.2',
            'SALESJNL',
            ' ',
            ' ',
            $formattedDate,
            $adjustment->amount,
            ($adjustment->amount * -1),
            $formattedDate,
            $adjustment->apply_to == 'Sales Invoice' ? 'BBSI#' . $adjustment->invoice_no . '/' . $adjustment->particulars : 'BBCI#' . $adjustment->invoice_no . '/' . $adjustment->particulars,
            'Customer',
            $adjustment->customer_code,
            $adjustment->amount,
            ($adjustment->amount * -1)
        ];

        $detailLine = [
            'SALES',
            'ADJSALES',
            ($auto_increment += 10000),
            'G/L Account',
            $adjustmentAccCode,
            $formattedDate,
            'Invoice',
            'BBARCM' . $adjustment->adjustment_no,
            $adjustment->adjustment_reason,
            'PHP',
            ($adjustment->amount * -1),
            ' ',
            $adjustment->amount,
            ($adjustment->amount * -1),
            ($adjustment->amount * -1),
            '1',
            ' ',
            ' ',
            '3',
            '03.01.2.02.2',
            'SALESJNL',
            ' ',
            ' ',
            ' ',
            ($adjustment->amount * -1),
            $adjustment->amount,
            $formattedDate,
            $adjustment->apply_to == 'Sales Invoice' ? 'BBSI#' . $adjustment->invoice_no . '/' . $adjustment->particulars : 'BBCI#' . $adjustment->invoice_no . '/' . $adjustment->particulars,
            ' ',
            ' ',
            ($adjustment->amount * -1),
            $adjustment->amount
        ];

        return implode(',', $headerLine) . "\n" . implode(',', $detailLine) . "\n";
    }

    protected function generateCashPaymentLine(&$auto_increment, $bankCode, $detail, $bankName, $customerNavCode)
    {
        $formattedDate = $this->formatDate($detail->payment_receipt_date);
        $headerLine = [
            'CASH RECEI',
            'BBCOLL',
            ($auto_increment += 10000),
            'Bank Account',
            $bankCode,
            $formattedDate,
            'Payment',
            'BBPY' . $detail->payment_no,
            $bankName,
            'PHP',
            $detail->amount_paid,
            $detail->amount_paid,
            ' ',
            $detail->amount_paid,
            $detail->amount_paid,
            '1',
            ' ',
            ' ',
            '3',
            '03.01.2.02.1',
            'CASHRECJNL',
            ' ',
            ' ',
            ' ',
            $detail->amount_paid,
            ($detail->amount_paid * -1),
            $formattedDate,
            'BBSI#' . $detail->document_no,
            'Bank Account',
            $bankCode,
            $detail->amount_paid,
            ($detail->amount_paid * -1)
        ];

        $detailLine = [
            'CASH RECEI',
            'BBCOLL',
            ($auto_increment += 10000),
            'Customer',
            $customerNavCode,
            $formattedDate,
            'Payment',
            'BBPY' . $detail->payment_no,
            $detail->customer_name,
            'PHP',
            ($detail->amount_paid * -1),
            ' ',
            $detail->amount_paid,
            ($detail->amount_paid * -1),
            ($detail->amount_paid * -1),
            '1',
            $customerNavCode,
            'INT-TRADE',
            '3',
            '03.01.2.02.1',
            'CASHRECJNL',
            'Invoice',
            'BBSI' . $detail->document_no,
            $formattedDate,
            ($detail->amount_paid * -1),
            $detail->amount_paid,
            $formattedDate,
            'BBSI#' . $detail->document_no,
            'Customer',
            $bankCode,
            ($detail->amount_paid * -1),
            $detail->amount_paid
        ];

        return implode(',', $headerLine) . "\n" . implode(',', $detailLine) . "\n";
    }

    protected function generateJournalVoucherLine(&$auto_increment, $detail, $customerNavCode, $customerCusPosting, $customerCode, $customerName, $accCode, $accCodeName)
    {
        $formattedDate = $this->formatDate($detail->payment_receipt_date);
        $headerLine = [
            'CASH RECEI',
            'BBCOLL',
            ($auto_increment += 10000),
            'G/L Account',
            $accCode,
            $formattedDate,
            'Payment',
            'BBPY' . $detail->payment_no,
            $accCodeName,
            'PHP',
            $detail->amount_paid,
            $detail->amount_paid,
            ' ',
            $detail->amount_paid,
            $detail->amount_paid,
            '1',
            ' ',
            ' ',
            '3',
            '03.01.2.02.1',
            'CASHRECJNL',
            ' ',
            ' ',
            ' ',
            $detail->amount_paid,
            ($detail->amount_paid * -1),
            $formattedDate,
            'BBSI#' . $detail->document_no,
            ' ',
            ' ',
            $detail->amount_paid,
            ($detail->amount_paid * -1)
        ];

        $detailLine = [
            'CASH RECEI',
            'BBCOLL',
            ($auto_increment += 10000),
            'Customer',
            $customerCode,
            $formattedDate,
            'Payment',
            'BBPY' . $detail->payment_no,
            $customerName,
            'PHP',
            ($detail->amount_paid * -1),
            ' ',
            $detail->amount_paid,
            ($detail->amount_paid * -1),
            ($detail->amount_paid * -1),
            '1',
            $customerNavCode,
            $customerCusPosting,
            '3',
            '03.01.2.02.1',
            'CASHRECJNL',
            ' ',
            'BBSI' . $detail->document_no,
            $formattedDate,
            ($detail->amount_paid * -1),
            $detail->amount_paid,
            $formattedDate,
            'BBSI#' . $detail->document_no,
            'Customer',
            $customerCode,
            ($detail->amount_paid * -1),
            $detail->amount_paid
        ];

        return implode(',', $headerLine) . "\n" . implode(',', $detailLine) . "\n";
    }

    protected function generateOnlineDepositLine(&$auto_increment, $detail, $bankCode, $bankName, $customerNavCode, $customerCusPosting, $customerCode, $customerName, $accCode, $accCodeName)
    {
        $formattedDate = $this->formatDate($detail->payment_receipt_date);
        $headerLine = [
            'CASH RECEI',
            'BBCOLL',
            ($auto_increment += 10000),
            'Customer',
            $bankCode,
            $formattedDate,
            ' ',
            'BBPY' . $detail->payment_no,
            $bankName,
            'PHP',
            $detail->amount_paid,
            $detail->amount_paid,
            ' ',
            $detail->amount_paid,
            $detail->amount_paid,
            '1',
            $accCode,
            $customerCusPosting,
            '3',
            '03.01.2.02.1',
            'CASHRECJNL',
            ' ',
            ' ',
            $formattedDate,
            $detail->amount_paid,
            ($detail->amount_paid * -1),
            $formattedDate,
            'BBSI#' . $detail->document_no,
            'Customer',
            $accCode,
            $detail->amount_paid,
            ($detail->amount_paid * -1)
        ];

        $detailLine = [
            'CASH RECEI',
            'BBCOLL',
            ($auto_increment += 10000),
            'Customer',
            $customerNavCode,
            $formattedDate,
            ' ',
            'BBPY' . $detail->payment_no,
            $customerName,
            'PHP',
            ($detail->amount_paid * -1),
            ' ',
            $detail->amount_paid,
            ($detail->amount_paid * -1),
            ($detail->amount_paid * -1),
            '1',
            $customerNavCode,
            $customerCusPosting,
            '3',
            '03.01.2.02.1',
            'CASHRECJNL',
            'Invoice',
            'BBSI' . $detail->document_no,
            $formattedDate,
            ($detail->amount_paid * -1),
            $detail->amount_paid,
            $formattedDate,
            'BBSI#' . $detail->document_no,
            'Customer',
            $customerNavCode,
            ($detail->amount_paid * -1),
            $detail->amount_paid
        ];

        return implode(',', $headerLine) . "\n" . implode(',', $detailLine) . "\n";
    }

    protected function generateWHTLine(&$auto_increment, $detail, $paymentAccountCode, $paymentAccountCodeDescription, $bankCode, $bankName, $customerNavCode, $customerCusPosting, $customerCode, $customerName, $accCode, $accCodeName)
    {
        $formattedDate = $this->formatDate($detail->payment_receipt_date);
        $headerLine = [
            'CASH RECEI',
            'BBCOLL',
            ($auto_increment += 10000),
            'G/L Account',
            $paymentAccountCode,
            $formattedDate,
            'Payment',
            'BBPY' . $detail->payment_no,
            $paymentAccountCodeDescription,
            'PHP',
            $detail->amount_paid,
            $detail->amount_paid,
            ' ',
            $detail->amount_paid,
            $detail->amount_paid,
            '1',
            ' ',
            ' ',
            '3',
            '03.01.2.02.1',
            'CASHRECJNL',
            ' ',
            ' ',
            ' ',
            $detail->amount_paid,
            ($detail->amount_paid * -1),
            $formattedDate,
            'BBSI#' . $detail->document_no,
            ' ',
            ' ',
            $detail->amount_paid,
            ($detail->amount_paid * -1)
        ];

        $detailLine = [
            'CASH RECEI',
            'BBCOLL',
            ($auto_increment += 10000),
            'Customer',
            $customerNavCode,
            $formattedDate,
            'Payment',
            'BBPY' . $detail->payment_no,
            $customerName,
            'PHP',
            ($detail->amount_paid * -1),
            ' ',
            $detail->amount_paid,
            ($detail->amount_paid * -1),
            ($detail->amount_paid * -1),
            '1',
            ' ',
            $customerCusPosting,
            '3',
            '03.01.2.02.1',
            'CASHRECJNL',
            'Invoice',
            'BBSI' . $detail->document_no,
            $formattedDate,
            ($detail->amount_paid * -1),
            $detail->amount_paid,
            $formattedDate,
            'BBSI#' . $detail->document_no,
            'Customer',
            ' ',
            ($detail->amount_paid * -1),
            $detail->amount_paid
        ];

        return implode(',', $headerLine) . "\n" . implode(',', $detailLine) . "\n";
    }

    protected function formatDate($dateString)
    {
        if (empty($dateString)) {
            return '';
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $dateString)->format('d/m/Y');
        } catch (Exception $e) {
            return $dateString; // fallback to original format if parsing fails
        }
    }
    protected function formatDateForName($dateString)
    {
        if (empty($dateString)) {
            return '';
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $dateString)->format('mdy');
        } catch (Exception $e) {
            return $dateString; // fallback to original format if parsing fails
        }
    }

    protected function getPaymentAccCode($paymentTypeCode, GlobalApiServices $globalApi)
    {
        $paymentAccCode = null;

        // From GlobalApiServices
        $apiUrl = $globalApi->GetPaymentAccountCode();
        try {
            $response = file_get_contents($apiUrl);

            if ($response !== false) {
                $data = json_decode($response, true);

                if (isset($data['payment_type_setup'])) {
                    foreach ($data['payment_type_setup'] as $paymentType) {
                        if ($paymentType['payment_type_code'] === $paymentTypeCode) {
                            $paymentAccCode = $paymentType['account_code'];
                            break;
                        }
                    }

                    if ($paymentAccCode === null) {
                        Log::info('error', 'Payment type 5E not found in API response');
                    }
                } else {
                    Log::info('error', 'Invalid API response structure');
                }
            } else {
                Log::info('error', 'Failed to fetch data from API');
            }
        } catch (Exception $e) {
            Log::info('error', 'API request failed: ' . $e->getMessage());
        }
        return $paymentAccCode;
    }

    protected function getPaymentAccCodeDescription($paymentTypeCode, GlobalApiServices $globalApi)
    {
        $paymentAccCodeDescription = null;

        // From GlobalApiServices
        $apiUrl = $globalApi->PaymentAccountCodeDescription();

        try {
            $response = file_get_contents($apiUrl);

            if ($response !== false) {
                $data = json_decode($response, true);

                if (isset($data['payment_type_setup'])) {
                    foreach ($data['payment_type_setup'] as $paymentType) {
                        if ($paymentType['payment_type_code'] === $paymentTypeCode) {
                            $paymentAccCodeDescription = $paymentType['account_description'];
                            break;
                        }
                    }

                    if ($paymentAccCodeDescription === null) {
                        Log::info('error', 'Payment type 5E not found in API response');
                    }
                } else {
                    Log::info('error', 'Invalid API response structure');
                }
            } else {
                Log::info('error', 'Failed to fetch data from API');
            }
        } catch (Exception $e) {
            Log::info('error', 'API request failed: ' . $e->getMessage());
        }
        return $paymentAccCodeDescription;
    }
}
