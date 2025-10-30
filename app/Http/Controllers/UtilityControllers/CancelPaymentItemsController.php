<?php

namespace App\Http\Controllers\UtilityControllers;

use App\Http\Controllers\Controller;
use App\Models\UtilityModels\CancelPaymentItems;
use Illuminate\Http\Request;

class CancelPaymentItemsController extends Controller
{
    public function getByCancellationNo($cancellation_no)
    {
        try {
            // Option 1: Direct query on InvoiceItem model
            $items = CancelPaymentItems::where('cancellation_no', $cancellation_no)
                ->select([
                    'document_no',
                    'payment_no',
                    'receipt_date',
                    'payment_type',
                    'amount',
                    'remarks',
                ])
                ->get();

            return response()->json($items);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch cancel payment items',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
