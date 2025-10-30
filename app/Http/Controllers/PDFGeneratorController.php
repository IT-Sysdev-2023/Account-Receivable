<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateTransactionPDFJob;
use App\Models\ReportModels\ReprintLog;
use App\Models\TransactionModels\Payment;
use App\Models\TransactionModels\PaymentDetails;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NumberFormatter;
use Illuminate\Support\Str;

class PDFGeneratorController extends Controller
{
    public function previewInvoice(Request $request)
    {
        $data = $request->all();
        $reprintconfirmation = $request->input('_reprint_confirmation', false);
        $personauthored = $request->input('_person_authored', null);

        $data['receipt_date'] = Carbon::parse($data['receipt_date'])->format('m/d/Y');
        $data['transaction_date'] = Carbon::parse($data['transaction_date'])->format('m/d/Y');

        $data['total_amount'] = (float)preg_replace('/[^0-9.]/', '', $data['total_amount']);

        $channel = 'transaction-pdf-generation.' . Str::random(20);

        GenerateTransactionPDFJob::dispatch(
            $data,
            $request->user()->id,
            $channel,
            $reprintconfirmation,
            $personauthored ?? "",
            strtoupper($request->user()->name),
            'invoicetransaction'
        );

        return response()->json([
            'channel' => 'transaction-pdf-generation.' . $request->user()->id,
            'user_id' => $request->user()->id,
            'status' => 'started',
            'message' => 'PDF generation has started',
        ]);
    }

    public function previewCashInvoice(Request $request)
    {
        // Prepare the data (coming from your frontend form)
        $data = $request->all();
        $reprintconfirmation = $request->input('_reprint_confirmation', false);
        $personauthored = $request->input('_person_authored', null);

        $data['receipt_date'] = Carbon::parse($data['receipt_date'])->format('m/d/Y');
        $data['transaction_date'] = Carbon::parse($data['transaction_date'])->format('m/d/Y');

        $data['total_amount'] = (float)preg_replace('/[^0-9.]/', '', $data['total_amount']);

        $channel = 'transaction-pdf-generation.' . Str::random(20);

        GenerateTransactionPDFJob::dispatch(
            $data,
            $request->user()->id,
            $channel,
            $reprintconfirmation,
            $personauthored ?? "",
            strtoupper($request->user()->name),
            'invoicecashtransaction'
        );

        return response()->json([
            'channel' => 'transaction-pdf-generation.' . $request->user()->id,
            'user_id' => $request->user()->id,
            'status' => 'started',
            'message' => 'PDF generation has started',
        ]);
    }

    public function previewAdjustment(Request $request)
    {
        $data = $request->all();
        $reprintconfirmation = $request->input('_reprint_confirmation', false);
        $personauthored = $request->input('_person_authored', null);

        $data['receipt_date'] = Carbon::parse($data['receipt_date'])->format('m/d/Y');
        $data['transaction_date'] = Carbon::parse($data['transaction_date'])->format('m/d/Y');
        $data['preparedBy'] = strtoupper($request->user()->name);

        $channel = 'transaction-pdf-generation.' . Str::random(20);

        GenerateTransactionPDFJob::dispatch(
            $data,
            $request->user()->id,
            $channel,
            $reprintconfirmation,
            $personauthored ?? "",
            strtoupper($request->user()->name),
            'adjustmenttransaction'
        );

        return response()->json([
            'channel' => 'transaction-pdf-generation.' . $request->user()->id,
            'user_id' => $request->user()->id,
            'status' => 'started',
            'message' => 'PDF generation has started',
        ]);
    }

    public function previewPayment(Request $request)
    {
        $data = $request->all();
        $reprintconfirmation = $request->input('_reprint_confirmation', false);
        $personauthored = $request->input('_person_authored', null);

        $channel = 'transaction-pdf-generation.' . Str::random(20);

        GenerateTransactionPDFJob::dispatch(
            $data,
            $request->user()->id,
            $channel,
            $reprintconfirmation,
            $personauthored ?? "",
            strtoupper($request->user()->name),
            'paymenttransaction'
        );

        return response()->json([
            'channel' => 'transaction-pdf-generation.' . $request->user()->id,
            'user_id' => $request->user()->id,
            'status' => 'started',
            'message' => 'PDF generation has started',
        ]);
    }


    public function previewCheckCleared(Request $request)
    {
        $data = $request->all();
        $reprintconfirmation = $request->input('_reprint_confirmation', false);
        $personauthored = $request->input('_person_authored', null);

        $channel = 'transaction-pdf-generation.' . Str::random(20);

        GenerateTransactionPDFJob::dispatch(
            $data,
            $request->user()->id,
            $channel,
            $reprintconfirmation,
            $personauthored ?? "",
            strtoupper($request->user()->name),
            'checkclearedtransaction'
        );

        return response()->json([
            'channel' => 'transaction-pdf-generation.' . $request->user()->id,
            'user_id' => $request->user()->id,
            'status' => 'started',
            'message' => 'PDF generation has started',
        ]);
    }

    public function previewWhtCleared(Request $request)
    {
        $data = $request->all();
        $reprintconfirmation = $request->input('_reprint_confirmation', false);
        $personauthored = $request->input('_person_authored', null);

        $channel = 'transaction-pdf-generation.' . Str::random(20);

        GenerateTransactionPDFJob::dispatch(
            $data,
            $request->user()->id,
            $channel,
            $reprintconfirmation,
            $personauthored ?? "",
            strtoupper($request->user()->name),
            'whtclearedtransaction'
        );

        return response()->json([
            'channel' => 'transaction-pdf-generation.' . $request->user()->id,
            'user_id' => $request->user()->id,
            'status' => 'started',
            'message' => 'PDF generation has started',
        ]);
    }






    ///////////////HELPER///////////////////////////////////////////////////////////
    public function amountToWords($amount)
    {
        $f = new NumberFormatter('en', NumberFormatter::SPELLOUT);
        $amount = number_format($amount, 2, '.', '');
        [$pesos, $centavos] = explode('.', $amount);

        $words = '';

        if ((int)$pesos > 0) {
            $words .= $this->capitalizeWordsWithHyphens($f->format($pesos)) . ' Peso' . ((int)$pesos > 1 ? 's' : '');
        }

        if ((int)$centavos > 0) {
            if ($words !== '') {
                $words .= ' and ';
            }
            $words .= $this->capitalizeWordsWithHyphens($f->format($centavos)) . ' Centavo' . ((int)$centavos > 1 ? 's' : '');
        }

        $words .= ' Only';

        return $words;
    }

    public function capitalizeWordsWithHyphens($text)
    {
        // Capitalize normally
        $text = ucwords($text);
        // Capitalize after hyphens manually
        return preg_replace_callback('/-([a-z])/', function ($matches) {
            return '-' . strtoupper($matches[1]);
        }, $text);
    }
}
