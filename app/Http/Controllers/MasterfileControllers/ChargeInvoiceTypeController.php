<?php

namespace App\Http\Controllers\MasterfileControllers;

use App\Events\NewCreated;
use App\Http\Controllers\Controller;
use App\Models\MasterfileModels\ChargeInvoiceType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChargeInvoiceTypeController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('ChargeInvoiceType', [
            // Fetch the users
            'charge_invoice_types' => ChargeInvoiceType::when($request->search, function ($query) use ($request) {
                $query
                    ->where('ci_type', 'like', '%' . $request->search . '%');
            })->paginate(10)->withQueryString(),

            'searchTerm' => $request->search,
            'broadcastChannel' => 'charge_invoice_types',
        ]);
    }

    public function addChargeInvoiceType(Request $request)
    {

        $fields = $request->validate([
            'sequence_no' => ['required', 'integer'],
            'ci_type' => ['required', 'string', 'max:255'],
            'nav_code' => ['required', 'string', 'max:255'],
        ]);

        $fields['ci_type'] = ucwords(strtolower($fields['ci_type']));

        $fields['created_by'] =  $request->user()->name;
        ChargeInvoiceType::create($fields);

        event(new NewCreated('charge_invoice_type'));
    }

    public function editChargeInvoiceType(Request $request)
    {
        $fields = $request->validate([
            'sequence_no' => ['required', 'integer'],
            'ci_type' => ['required', 'string', 'max:255'],
            'nav_code' => ['required', 'string', 'max:255'],
        ]);

        $fields['ci_type'] = ucwords(strtolower($fields['ci_type']));

        $cit = ChargeInvoiceType::findOrFail($request->id);

        $fields['created_by'] =  $request->user()->name;
        $cit->update($fields);

        event(new NewCreated('charge_invoice_type'));
    }

    public function destroy($id)
    {
        $cit = ChargeInvoiceType::findOrFail($id);
        $cit->delete();

        event(new NewCreated('charge_invoice_type'));
    }

    public function latest()
    {
        return ChargeInvoiceType::orderByDesc('id')->value('sequence_no'); // returns "B001" or null
    }
}
