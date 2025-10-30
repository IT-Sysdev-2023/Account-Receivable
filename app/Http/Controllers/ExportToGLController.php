<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateTextFile;
use App\Models\MasterfileModels\CashInBank;
use App\Models\MasterfileModels\Customer;
use App\Models\TransactionModels\Adjustment;
use App\Models\TransactionModels\Invoice;
use App\Models\TransactionModels\Payment;
use App\Models\TransactionModels\PaymentDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExportToGLController extends Controller
{
    public function export(Request $request)
    {
        try {
            $validated = $request->validate([
                "export_type" => "required|string",
                "start_date" => "required|date",
                "end_date" => "required|date|after_or_equal:start_date",
            ]);

            if ($validated["export_type"] == "Other Income") {
                $query = Invoice::with('items')
                    ->whereBetween('receipt_date', [
                        $validated['start_date'],
                        $validated['end_date']
                    ])
                    ->where('exported', false)
                    ->orderBy('receipt_date');
            } else if ($validated['export_type'] == 'Adjustment') {
                $query = Adjustment::whereBetween('receipt_date', [
                    $validated['start_date'],
                    $validated['end_date']
                ])
                    ->where('exported', false)
                    ->orderBy('receipt_date');
            } else if ($validated['export_type'] == 'Payment') {
                $query = Payment::with(['paymentDetails' => function ($q) {
                    $q->where('status', '!=', 'Floating')
                        ->where('status', '!=', 'Cancelled');
                }])
                    ->whereBetween('receipt_date', [
                        $validated['start_date'],
                        $validated['end_date']
                    ])
                    ->where('exported', false)
                    ->orderBy('receipt_date');
            }

            if (!$query->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data found for the selected date range.'
                ], 200);
            }

            $channel = 'textfile-generation.' . Str::random(20);

            GenerateTextFile::dispatch(
                $validated,
                $request->user()->id,
                $channel,
            );

            return response()->json([
                'channel' => 'textfile-generation.' . $request->user()->id,
                'user_id' => $request->user()->id,
                'status' => 'started',
                'message' => 'TextFile generation has started',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function untag(Request $request)
    {
        $validated = $request->validate([
            "export_type" => "required|string|in:Other Income,Adjustment,Payment",
            "start_date" => "required|date",
            "end_date" => "required|date|after_or_equal:start_date",
        ]);

        $table = match ($validated["export_type"]) {
            'Other Income' => 'invoice',
            'Adjustment' => 'adjustment',
            'Payment' => 'payment',
        };

        $count = DB::table($table)
            ->whereBetween('receipt_date', [
                $validated['start_date'],
                $validated['end_date']
            ])
            ->where('exported', true) // Only target exported records
            ->update(['exported' => false]);

        if ($count === 0) {
            throw ValidationException::withMessages([
                'general' => 'No exported records found for the selected date range',
            ]);
        }
    }
}
