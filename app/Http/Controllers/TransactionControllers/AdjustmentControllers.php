<?php

namespace App\Http\Controllers\TransactionControllers;

use App\Events\NewCreated;
use App\Http\Controllers\Controller;
use App\Models\MasterfileModels\AdjustmentReasonSetup;
use App\Models\ReportModels\CustomerLedger;
use App\Models\TransactionModels\Adjustment;
use App\Models\TransactionModels\Invoice;
use App\Services\AdjustmentNumberService;
use App\Services\GlobalApiServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AdjustmentControllers extends Controller
{

    public function index(Request $request)
    {
        $query = Adjustment::query();

        // Search functionality
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('adjustment_no', 'like', '%' . $request->search . '%');
            });
        }

        // Date sorting
        if ($request->date_start) {
            $query->whereDate('receipt_date', '>=', $request->date_start);
        }

        if ($request->date_end) {
            $query->whereDate('receipt_date', '<=', $request->date_end);
        }


        // Type filters
        if ($request->type_filters) {
            $types = is_array($request->type_filters)
                ? $request->type_filters
                : explode(',', $request->type_filters);
            $query->whereIn('type', $types);
        }

        // Apply To filters
        if ($request->type_filtersApplyTo) {
            $types = is_array($request->type_filtersApplyTo)
                ? $request->type_filtersApplyTo
                : explode(',', $request->type_filtersApplyTo);
            $query->whereIn('apply_to', $types);
        }



        // Code sorting
        if ($request->code_sort) {
            $query->orderBy('adjustment_no', $request->code_sort === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('adjustment_no', 'desc');
        }

        return Inertia::render('Adjustment', [
            'adjustments' => $query->paginate(10)->withQueryString(),
            'searchTerm' => $request->search,
            'filters' => [
                'code_sort' => $request->code_sort,
                'type_filters' => $request->type_filters ?
                    (is_array($request->type_filters) ?
                        $request->type_filters :
                        explode(',', $request->type_filters)) :
                    [],
                'type_filtersApplyTo' => $request->type_filtersApplyTo ?
                    (is_array($request->type_filtersApplyTo) ?
                        $request->type_filtersApplyTo :
                        explode(',', $request->type_filtersApplyTo)) :
                    [],
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
            ],
            'broadcastChannel' => 'adjustments',
        ]);
    }

    public function addAdjustment(Request $request, AdjustmentNumberService $adjustmentNumberService, GlobalApiServices $globalApi)
    {
        $cl_type = $request->input('_cl_type', '');

        $validated = $request->validate(
            [
                'adjustment_no' => ['required', 'string'],
                'receipt_date' => ['required', 'date', 'before_or_equal:today'],
                'transaction_date' => ['required', 'date', 'before_or_equal:today'],
                'customer_code' => ['required', 'string'],
                'name' => ['required', 'string'],
                'type' => ['required', 'string'],
                'apply_to' => ['required', 'in:Sales Invoice,Other Income'],
                'invoice_no' => ['required', 'string'],
                'balance' => ['required', 'numeric'],
                'adjustment_reason' => ['required', 'string'],
                'particulars' => ['required', 'string'],
                'amount' => [
                    'required',
                    'numeric',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->type === 'Negative' && $value > $request->balance) {
                            $fail('Amount Should Not Be Greater Than Available Balance');
                        }
                    },
                ],
            ],
            [
                'adjustment_no.required' => 'Adjustment Number Required',
                'receipt_date.required' => 'Date Required',
                'receipt_date.date' => 'Date Must Be Valid Date',
                'receipt_date.before_or_equal' => 'Date Cannot Be Future',
                'transaction_date.required' => 'Date Required',
                'transaction_date.date' => 'Date Must Be Valid Date',
                'transaction_date.before_or_equal' => 'Date Cannot Be Future',
                'customer_code.required' => 'Customer Code Required',
                'name.required' => 'Customer Name Required',
                'type.required' => 'Type Required',
                'apply_to.required' => 'Apply To Required',
                'apply_to.in' => 'Apply To Required',
                'invoice_no.required' => 'Document Number Required',
                'adjustment_reason.required' => 'Adjustment Reason Required',
                'particulars.required' => 'Particular Required',
                'amount.required' => 'Amount Required',
                'amount.numeric' => 'Amount Must Be Valid number',
            ]
        );

        $adjNo = DB::transaction(function () use ($validated, $request, $cl_type, $adjustmentNumberService, $globalApi) {
            $adjustmentNumber = $adjustmentNumberService->generate();

            if (Adjustment::where('adjustment_no', $adjustmentNumber)->exists()) {
                throw ValidationException::withMessages([
                    'general' => 'Error Please Try Again',
                ]);
            }

            $ledger = CustomerLedger::where('invoice_number', $validated['invoice_no'])->where('type', $cl_type)->firstOrFail();

            $currentRunningBalance = $ledger->running_balance;
            $currentAmount = $ledger->adjusted_amount;

            if (strtolower($validated['type']) === 'positive') {
                $newAmount = $currentRunningBalance + $validated['amount'];
                $newAdjustmentAmount = $validated['amount'] + $currentAmount;
            } elseif (strtolower($validated['type']) === 'negative') {
                $newAmount = $currentRunningBalance - $validated['amount'];
                $newAmount = max($newAmount, 0);
                $newAdjustmentAmount = $currentAmount - $validated['amount'];
            }


            $ledger->update([
                'running_balance' => $newAmount,
                'adjusted_amount' => $newAdjustmentAmount
            ]);

            $dbData = collect($validated)
                ->except(['_cl_type'])
                ->all();
            $dbData['adjustment_no'] = $adjustmentNumber;
            $dbData['created_by'] = $request->user()->name;
            Adjustment::create($dbData);

            // This update or send status of Invoice system of the adjustment made in the ar system mother father
            if ($cl_type === 'Sales Invoice') {
                $response = $globalApi->AddAdjustment($newAdjustmentAmount, $validated['invoice_no']);

                if (!$response['success']) {
                    throw ValidationException::withMessages([
                        'general' => $response['message'] ?? 'Error Please Try Again',
                    ]);
                }
            }
            return $adjustmentNumber;
        });

        event(new NewCreated('adjustment'));
        event(new NewCreated('customerledger'));

        session()->put('adjustment_number', $adjNo);
        return redirect()->back();
    }

    public function destroy($id)
    {
        $adj = Adjustment::findorFail($id);
        $adj->delete();
    }

    public function latest()
    {
        //return Adjustment::orderByDesc('id')->value('adjustment_no'); // returns "25000001" or null
        return DB::transaction(function () {
            $latestAdjustment = Adjustment::withTrashed()
                ->lockForUpdate() // Prevent concurrent access
                ->orderByDesc('adjustment_no')
                ->first();

            $nextNumber = $latestAdjustment ? $latestAdjustment->adjustment_no + 1 : 25000001;

            return response()->json([
                'next_adjustment_no' => $nextNumber,
                'is_new_sequence' => !$latestAdjustment
            ]);
        });
    }

    public function getAdjustmentReasonSetup(Request $request)
    {
        $apply_to = $request->input('apply_to');

        $types = AdjustmentReasonSetup::where('type', $apply_to)->pluck('reason_name');
        return response()->json($types);
    }
}
