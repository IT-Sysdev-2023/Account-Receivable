<?php

namespace App\Http\Controllers\MasterfileControllers;

use App\Http\Controllers\Controller;
use App\Models\MasterfileModels\Item;
use App\Models\MasterfileModels\ItemPacking;
use App\Models\MasterfileModels\PackingType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemPackingController extends Controller
{

    public function show(Item $item)
    {
        return response()->json(
            $item->packings()->select('groupcode', 'packing', 'price', 'quantity', 'status')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|integer|exists:item,id',
            'packings' => 'nullable|array',
            'packings.*.groupcode' => 'required_with:packings|string',
            'packings.*.packing' => 'required_with:packings|string',
            'packings.*.price' => 'required_with:packings|numeric',
            'packings.*.quantity' => 'required_with:packings|integer',
            'packings.*.status' => 'required_with:packings|string',
        ]);

        if (isset($validated['packings'])) {
            $combinations = array_map(function ($packing) {
                return strtolower(trim($packing['groupcode'] . '|' . $packing['packing']));
            }, $validated['packings']);

            if (count($combinations) !== count(array_unique($combinations))) {
                return back()
                    ->withErrors(['packings' => 'Duplicate groupcode and packing combination is not allowed'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($validated, $request) {
            $itemID = $validated['item_id'];

            // If packings is empty or not provided, delete existing records
            if (empty($validated['packings'])) {
                ItemPacking::where('item_id', $itemID)->delete();
                return;
            }

            // Delete old entries
            ItemPacking::where('item_id', $itemID)->delete();

            // Prepare data for batch insert
            $newPackings = array_map(function ($packing) use ($itemID) {
                return [
                    'item_id' => $itemID,
                    'groupcode' => $packing['groupcode'],
                    'packing' => $packing['packing'],
                    'price' => $packing['price'],
                    'quantity' => $packing['quantity'],
                    'status' => $packing['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }, $validated['packings']);

            // Insert all in one go
            ItemPacking::insert($newPackings);

            $item = Item::find($validated['item_id']);
            if ($item) {
                $item->update([
                    'created_by' => $request->user()->name,
                ]);
            }
        });
    }

    public function destroy(ItemPacking $itemPacking)
    {
        $itemPacking->delete();
    }

    public function getPackingTypes(Request $request)
    {
        $item = Item::findOrFail($request->itemId);

        // Now packing is already an array — no need for json_decode
        $packingList = PackingType::whereIn('id', $item->packing ?? [])->pluck('packing_type')->toArray();

        $item->packingList = $packingList;
        // dd($item->packingList);
        return response()->json($item->packingList);
    }
}
