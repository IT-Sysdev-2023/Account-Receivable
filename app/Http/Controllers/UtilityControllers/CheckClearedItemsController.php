<?php

namespace App\Http\Controllers\UtilityControllers;

use App\Http\Controllers\Controller;
use App\Models\UtilityModels\CheckClearedItems;
use Illuminate\Http\Request;

class CheckClearedItemsController extends Controller
{
    public function getByClearingNo($clearing_no)
    {
        try {
            // Option 1: Direct query on InvoiceItem model
            $items = CheckClearedItems::where('clearing_no', $clearing_no)
                ->select([
                    'payment_no',
                    'check_no',
                    'document_no',
                    'due_date',
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
