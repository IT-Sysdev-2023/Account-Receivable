<?php

namespace App\Http\Controllers\TransactionControllers;

use App\Http\Controllers\Controller;
use App\Models\TransactionModels\Invoice;
use App\Models\TransactionModels\InvoiceItem;
use Illuminate\Http\Request;

class InvoiceItemController extends Controller
{

    public function getByInvoiceNo($invoice_no)
    {
        try {
            // Option 1: Direct query on InvoiceItem model
            $items = InvoiceItem::where('invoice_no', $invoice_no)
                ->select([
                    'item_code',
                    'item_name',
                    'packing',
                    'quantity',
                    'price',
                    'amount'
                ])
                ->get();

            //Option 2: Using the relationship from Invoice model
            // $invoice = Invoice::with('items')->findOrFail($invoice_no);
            // $items = $invoice->items;

            return response()->json($items);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch invoice items',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
