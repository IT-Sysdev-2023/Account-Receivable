<?php

namespace App\Http\Controllers\MasterfileControllers;

use App\Events\NewCreated;
use App\Http\Controllers\Controller;
use App\Models\MasterfileModels\ShortageAmount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShortageAmountController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('ShortageAmount', [
            // Fetch the users
            'shortage_amounts' => ShortageAmount::when($request->search, function ($query) use ($request) {
                $query
                    ->where('shortage_amnt', 'like', '%' . $request->search . '%');
            })->paginate(10)->withQueryString(),

            'searchTerm' => $request->search,
            'broadcastChannel' => 'shortage_amounts',
        ]);
    }
    public function addShortageAmount(Request $request)
    {
        $fields = $request->validate([
            'shortage_amnt' => ['required', 'numeric'],
        ]);

        $fields['created_by'] =  $request->user()->name;
        ShortageAmount::create($fields);

        event(new NewCreated('shortage_amount'));
    }

    public function editShortageAmount(Request $request)
    {
        $fields = $request->validate([
            'shortage_amnt' => ['required', 'numeric'],
        ]);

        $sa = ShortageAmount::findOrFail($request->id);
        $fields['created_by'] =  $request->user()->name;
        $sa->update($fields);

        event(new NewCreated('shortage_amount'));
    }

    public function destroy($id)
    {
        $sa = ShortageAmount::findOrFail($id);
        $sa->delete();

        event(new NewCreated('shortage_amount'));
    }
}
