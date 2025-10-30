<?php

namespace App\Http\Controllers\MasterfileControllers;

use App\Events\NewCreated;
use App\Http\Controllers\Controller;
use App\Models\MasterfileModels\CashInBank;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CashInBankController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('CashInBank', [
            // Fetch the users
            'cash_in_banks' => CashInBank::when($request->search, function ($query) use ($request) {
                $query
                    ->where('bank_code', 'like', '%' . $request->search . '%')
                    ->orWhere('bank_name', 'like', '%' . $request->search . '%');
            })->paginate(10)->withQueryString(),

            'searchTerm' => $request->search,
            'broadcastChannel' => 'cash_in_banks',
        ]);
    }
    public function cashInBankListAPI()
    {
        $cashInBanks = CashInBank::where('bank_name', 'Cash On Hand')->get();

        return response()->json([
            'data' => $cashInBanks
        ]);
    }

    public function addCashInBank(Request $request)
    {

        $fields = $request->validate([
            'bank_code' => ['required', 'string'],
            'bank_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'acc_code' => ['required', 'string'],
            'acc_classification' => ['required', 'string'],
        ]);

        $fields['bank_name'] = ucwords(strtolower($fields['bank_name']));

        $fields['created_by'] =  $request->user()->name;
        CashInBank::create($fields);

        event(new NewCreated('cash_in_bank'));
    }

    public function editCashInBank(Request $request)
    {
        $fields = $request->validate([
            'bank_code' => ['required', 'string'],
            'bank_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'acc_code' => ['required', 'string'],
            'acc_classification' => ['required', 'string'],
        ]);

        $fields['bank_name'] = ucwords(strtolower($fields['bank_name']));

        $cab = CashInBank::findOrFail($request->id);

        $fields['created_by'] =  $request->user()->name;
        $cab->update($fields);

        event(new NewCreated('cash_in_bank'));
    }

    public function destroy($id)
    {
        $cab = CashInBank::findOrFail($id);
        $cab->delete();

        event(new NewCreated('cash_in_bank'));
    }

    public function latest()
    {
        return CashInBank::orderByDesc('id')->value('bank_code'); // returns "B001" or null
    }

    public function getCashInBankList()
    {
        $list = CashInBank::select('bank_code', 'bank_name', 'acc_code')
            ->get();
        return response()->json($list);
    }
}
