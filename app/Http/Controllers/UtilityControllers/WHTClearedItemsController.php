<?php

namespace App\Http\Controllers\UtilityControllers;

use App\Http\Controllers\Controller;
use App\Models\UtilityModels\WHTClearedItems;
use Illuminate\Http\Request;

class WHTClearedItemsController extends Controller
{
    public function getByWhtClearingNo($wht_clearing_no)
    {
        try {
            // Option 1: Direct query on InvoiceItem model
            $items = WHTClearedItems::where('wht_clearing_no', $wht_clearing_no)
                ->select([
                    'payment_no',
                    'wht_no',
                    'document_no',
                    'receipt_date',
                    'amount',
                    'status',
                    'remarks',
                ])
                ->get();

            return response()->json($items);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch check cleared items',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
