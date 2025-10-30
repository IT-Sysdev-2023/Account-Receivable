<?php

namespace App\Http\Controllers\MasterfileControllers;

use App\Http\Controllers\Controller;
use App\Models\MasterfileModels\Item;
use App\Models\MasterfileModels\ItemWholeSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemWholeSaleController extends Controller
{
    public function show(Item $item)
    {
        return response()->json(
            $item->wholesales()->select('groupcode', 'packing', 'price')->get()
        );
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|integer|exists:item,id',
            'wholesales' => 'nullable|array',
            'wholesales.*.groupcode' => 'required_with:wholesales|string',
            'wholesales.*.packing' => 'required_with:wholesales|string',
            'wholesales.*.price' => 'required_with:wholesales|numeric',
        ]);

        DB::transaction(function () use ($validated) {
            $itemID = $validated['item_id'];

            // If wholesales is empty or not provided, delete existing records
            if (empty($validated['wholesales'])) {
                ItemWholeSale::where('item_id', $itemID)->delete();
                return;
            }

            // Delete old entries
            ItemWholeSale::where('item_id', $itemID)->delete();

            // Prepare data for batch insert
            $newWholeSales = array_map(function ($wholesale) use ($itemID) {
                return [
                    'item_id' => $itemID,
                    'groupcode' => $wholesale['groupcode'],
                    'packing' => $wholesale['packing'],
                    'price' => $wholesale['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $validated['wholesales']);

            // Insert all in one go
            ItemWholeSale::insert($newWholeSales);
        });
    }
}
