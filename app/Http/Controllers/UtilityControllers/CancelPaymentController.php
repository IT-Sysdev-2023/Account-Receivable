<?php

namespace App\Http\Controllers\UtilityControllers;

use App\Events\NewCreated;
use App\Http\Controllers\Controller;
use App\Models\MasterfileModels\Customer;
use App\Models\ReportModels\CustomerLedger;
use App\Models\UtilityModels\CancelPayment;
use App\Models\UtilityModels\CancelPaymentItems;
use App\Services\CancelPaymentNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CancelPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = CancelPayment::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('customer_name', 'like', '%' . $request->search . '%')
                    ->orWhere('document_no', 'like', '%' . $request->search . '%');
            });
        }

        $query->orderBy('created_at', 'desc');

        return Inertia::render('CancelPayment', [
            'cancel_payments' => $query->paginate(10)->withQueryString(),
            'searchTerm' => $request->search,
            'broadcastChannel' => 'cancel_payments',
        ]);
    }

    public function cancelPaymentUsingDocumentNo(Request $request, CancelPaymentNumberService $cancelPaymentNumberService)
    {
        $validated = $request->validate([
            'document_no' => 'required|string',
            'type' => 'required|string',
            'customer_code' => 'required|string',
            'customer_name' => 'required|string',
            'payment_details' => 'required|array',
            'payment_details.*.id' => 'required|numeric',
            'payment_details.*.payment_no' => 'required|string',
            'payment_details.*.receipt_date' => 'required|date',
            'payment_details.*.payment_type' => 'required|string',
            'payment_details.*.advpy_amount_paid' => 'required|numeric',
            'payment_details.*.amount' => 'required|numeric',
            'payment_details.*.remarks' => 'nullable|string'
        ]);

        DB::transaction(function () use ($validated, $request, $cancelPaymentNumberService) {
            $cancellationNo = $cancelPaymentNumberService->generate();
            // Validate the payment number is unique (just in case)
            if (CancelPayment::where('cancellation_no', $cancellationNo)->exists()) {
                throw ValidationException::withMessages([
                    'cancellation_no' => 'Error Please Try Again',
                ]);
            }
            CancelPayment::create([
                'cancellation_no' => $cancellationNo,
                'document_no' => $validated['document_no'],
                'type' => $validated['type'],
                'customer_code' => $validated['customer_code'],
                'customer_name' => $validated['customer_name'],
                'created_by' => $request->user()->name,
            ]);

            $cancelPaymentItems = array_map(function ($item) use ($cancellationNo) {
                return [
                    'cancellation_no' => $cancellationNo,
                    'payment_no' => $item['payment_no'],
                    'receipt_date' => $item['receipt_date'],
                    'payment_type' => $item['payment_type'],
                    'amount' => $item['amount'],
                    'remarks' => 'Cancelled',
                ];
            }, $validated['payment_details']);

            CancelPaymentItems::insert($cancelPaymentItems);

            $paymentItemsAdvPy = 0;
            foreach ($validated['payment_details'] as $payment) {
                // Update the original payment status
                DB::table('payment_details')
                    ->where('id', $payment['id'])
                    ->update([
                        'status' => 'Cancelled',
                        'remarks' => 'Cancelled',
                    ]);
                $paymentItemsAdvPy += $payment['advpy_amount_paid'];
            }
            $ledger = CustomerLedger::where('invoice_number', $validated['document_no'])->where('type', $validated['type'])->firstOrFail();

            $ledger->update([
                'running_balance' => ($ledger->amount + $ledger->adjusted_amount) + ($ledger->overage - $ledger->shrinkage) - $ledger->return,
                'amount_paid' => 0.00,
            ]);

            $cust = Customer::where('cus_code', $validated['customer_code'])
                ->lockForUpdate()
                ->first();

            $cust->update([
                'advanced_payment_balance' => $paymentItemsAdvPy + $cust->advanced_payment_balance,
            ]);

            event(new NewCreated('cancel_payment'));
        });
    }

    public function cancelPaymentUsingPaymentNo(Request $request, CancelPaymentNumberService $cancelPaymentNumberService)
    {
        $validated = $request->validate([
            'payment_no' => 'required|string',
            'type' => 'required|string',
            'customer_code' => 'required|string',
            'customer_name' => 'required|string',
            'payment_details' => 'required|array',
            'payment_details.*.id' => 'required|numeric',
            'payment_details.*.document_no' => 'required|string',
            'payment_details.*.receipt_date' => 'required|date',
            'payment_details.*.payment_type' => 'required|string',
            'payment_details.*.type' => 'required|string',
            'payment_details.*.advpy_amount_paid' => 'required|numeric',
            'payment_details.*.amount' => 'required|numeric',
            'payment_details.*.remarks' => 'nullable|string'
        ]);

        DB::transaction(function () use ($validated, $request, $cancelPaymentNumberService) {
            $cancellationNo = $cancelPaymentNumberService->generate();
            // Validate the payment number is unique (just in case)
            if (CancelPayment::where('cancellation_no', $cancellationNo)->exists()) {
                throw ValidationException::withMessages([
                    'cancellation_no' => 'Error Please Try Again',
                ]);
            }
            CancelPayment::create([
                'cancellation_no' => $cancellationNo,
                'payment_no' => $validated['payment_no'],
                'type' => $validated['type'],
                'customer_code' => $validated['customer_code'],
                'customer_name' => $validated['customer_name'],
                'created_by' => $request->user()->name,
            ]);

            $cancelPaymentItems = array_map(function ($item) use ($cancellationNo) {
                return [
                    'cancellation_no' => $cancellationNo,
                    'document_no' => $item['document_no'],
                    'receipt_date' => $item['receipt_date'],
                    'payment_type' => $item['payment_type'],
                    'amount' => $item['amount'],
                    'remarks' => 'Cancelled',
                ];
            }, $validated['payment_details']);

            CancelPaymentItems::insert($cancelPaymentItems);

            foreach ($validated['payment_details'] as $payment) {
                // Update the original payment status
                DB::table('payment_details')
                    ->where('id', $payment['id'])
                    ->update([
                        'status' => 'Cancelled',
                        'remarks' => 'Cancelled',
                    ]);

                $ledger = CustomerLedger::where('invoice_number', $payment['document_no'])->where('type', $payment['type'])->firstOrFail();

                $ledger->update([
                    'running_balance' => ($ledger->amount + $ledger->adjusted_amount) + ($ledger->overage - $ledger->shrinkage) - $ledger->return,
                    'amount_paid' => $ledger->amount_paid - $payment['amount'],
                ]);

                $cust = Customer::where('cus_code', $validated['customer_code'])
                    ->lockForUpdate()
                    ->first();

                $cust->update([
                    'advanced_payment_balance' => $payment['advpy_amount_paid'] + $cust->advanced_payment_balance,
                ]);
            }


            event(new NewCreated('cancel_payment'));
        });
    }

    public function latest()
    {
        return DB::transaction(function () {
            $latestCancellationNo = CancelPayment::lockForUpdate()
                ->orderByDesc('cancellation_no')
                ->first();

            $nextNumber = $latestCancellationNo ? $latestCancellationNo->cancellation_no + 1 : 25000001;

            return response()->json([
                'next_clearing_no' => $nextNumber,
                'is_new_sequence' => !$latestCancellationNo
            ]);
        });
    }
}
