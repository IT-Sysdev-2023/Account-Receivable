<?php

namespace App\Http\Controllers\MasterfileControllers;

use App\Events\NewCreated;
use App\Http\Controllers\Controller;

use App\Models\MasterfileModels\PackingType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackingTypeController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('PackingType', [
            // Fetch the users
            'packing_types' => PackingType::when($request->search, function ($query) use ($request) {
                $query
                    ->where('packing_type', 'like', '%' . $request->search . '%');
            })->paginate(10)->withQueryString(),

            'searchTerm' => $request->search,
            'broadcastChannel' => 'packing_types',
        ]);
    }

    public function addPackingType(Request $request)
    {

        $fields = $request->validate([
            'sequence_no' => ['required', 'integer'],
            'packing_type' => ['required', 'string', 'max:255'],
        ]);

        $fields['packing_type'] = ucwords(strtolower($fields['packing_type']));

        $fields['created_by'] =  $request->user()->name;
        PackingType::create($fields);

        event(new NewCreated('packing_type'));
    }

    public function editPackingType(Request $request)
    {
        $fields = $request->validate([
            'sequence_no' => ['required', 'integer'],
            'packing_type' => ['required', 'string', 'max:255'],
        ]);

        $fields['packing_type'] = ucwords(strtolower($fields['packing_type']));

        $pt = PackingType::findOrFail($request->id);

        $fields['created_by'] =  $request->user()->name;
        $pt->update($fields);

        event(new NewCreated('packing_type'));
    }

    public function destroy($id)
    {
        $pt = PackingType::findOrFail($id);
        $pt->delete();

        event(new NewCreated('packing_type'));
    }

    public function latest()
    {
        return PackingType::orderByDesc('id')->value('sequence_no'); // returns "B001" or null
    }
}
