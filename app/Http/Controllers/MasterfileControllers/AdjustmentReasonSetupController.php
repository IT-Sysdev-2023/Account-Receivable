<?php

namespace App\Http\Controllers\MasterfileControllers;

use App\Events\NewCreated;
use App\Http\Controllers\Controller;
use App\Models\MasterfileModels\AdjustmentReasonSetup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdjustmentReasonSetupController extends Controller
{
    public function index(Request $request)
    {
        $query = AdjustmentReasonSetup::query();

        // Search functionality
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('acc_code', 'like', '%' . $request->search . '%')
                    ->orWhere('reason_name', 'like', '%' . $request->search . '%');
            });
        }

        // Type filters
        if ($request->type_filters) {
            $types = is_array($request->type_filters)
                ? $request->type_filters
                : explode(',', $request->type_filters);
            $query->whereIn('type', $types);
        }

        // Status filters
        if ($request->status_filters) {
            $statuses = is_array($request->status_filters)
                ? $request->status_filters
                : explode(',', $request->status_filters);
            $query->whereIn('status', $statuses);
        }

        // Code sorting
        if ($request->code_sort) {
            $query->orderBy('acc_code', $request->code_sort === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('id', 'asc');
        }

        return Inertia::render('AdjustmentReasonSetup', [
            'adjustment_reason_setups' => $query->paginate(10)->withQueryString(),
            'searchTerm' => $request->search,
            'filters' => [
                'code_sort' => $request->code_sort,
                'type_filters' => $request->type_filters ? (is_array($request->type_filters) ? $request->type_filters : explode(',', $request->type_filters)) : [],
                'status_filters' => $request->status_filters
                    ? (is_array($request->status_filters)
                        ? $request->status_filters
                        : explode(',', $request->status_filters))
                    : [],
            ],
            'broadcastChannel' => 'adjustment_reason_setups',
        ]);
    }

    public function addAdjustmentReasonSetup(Request $request)
    {
        // sleep(1);

        //validate
        $fields = $request->validate([
            'reason_name' => ['required', 'string', 'max:255'],
            'acc_code' => ['required', 'string'],
            'type' => [
                'required',
                'in:Sales Invoice,Other Income,Payment'
            ],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $fields['reason_name'] = ucwords(strtolower($fields['reason_name']));

        // Add
        $fields['created_by'] =  $request->user()->name;
        AdjustmentReasonSetup::create($fields);

        event(new NewCreated('adjustment_reason_setup'));
    }

    public function editAdjustmentReasonSetup(Request $request)
    {
        // sleep(1);

        //validate
        $fields = $request->validate([
            'reason_name' => ['required', 'string', 'max:255'],
            'acc_code' => ['required', 'string'],
            'type' => [
                'required',
                'in:Sales Invoice,Other Income,Payment'
            ],
            'status' => ['required', 'in:Active,Inactive'],
        ]);

        $fields['reason_name'] = ucwords(strtolower($fields['reason_name']));

        $adjust = AdjustmentReasonSetup::findOrFail($request->id);

        // Add
        $fields['created_by'] =  $request->user()->name;
        $adjust->update($fields);

        event(new NewCreated('adjustment_reason_setup'));
    }

    public function destroy($id)
    {
        $adjust = AdjustmentReasonSetup::findOrFail($id);
        $adjust->delete();

        event(new NewCreated('adjustment_reason_setup'));
    }
}
