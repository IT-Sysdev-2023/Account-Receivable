<?php

namespace App\Http\Controllers\TransactionControllers;

use App\Events\NewCreated;
use App\Http\Controllers\Controller;
use App\Models\ReportModels\CustomerLedger;
use App\Models\TransactionModels\BeginningBalance;
use App\Models\TransactionModels\PaymentDetails;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class BeginningBalanceController extends Controller
{
    public function index(Request $request)
    {
        $query = BeginningBalance::query();

        // Search functionality
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('beginningbalance_no', 'like', '%' . $request->search . '%');
            });
        }

        // Date sorting
        if ($request->date_start) {
            $query->whereDate('receipt_date', '>=', $request->date_start);
        }

        if ($request->date_end) {
            $query->whereDate('receipt_date', '<=', $request->date_end);
        }

        if ($request->code_sort) {
            $query->orderBy('beginningbalance_no', $request->code_sort === 'asc' ? 'asc' : 'desc');
        }
        // Default sorting - applies when no code_sort is specified
        else {
            $query->orderBy('balance_amount', 'desc');
        }

        return Inertia::render('BeginningBalance', [
            'beginningbalances' => $query->paginate(10)->withQueryString(),
            'searchTerm' => $request->search,
            'filters' => [
                'code_sort' => $request->code_sort,
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
            ],
            'broadcastChannel' => 'beginningbalances',
        ]);
    }

    public function addBeginningBalance(Request $request)
    {
        // sleep(1);

        //validate
        //dd($request->all());
        $validated = $request->validate(
            [
                'beginningbalance_no' => ['required', 'string'],
                'receipt_date' => ['required', 'date', 'before_or_equal:today'],
                'transaction_date' => ['required', 'date', 'before_or_equal:today'],
                'customer_code' => ['required', 'string'],
                'name' => ['required', 'string'],
                'particular' => ['required', 'string'],
                'balance_amount' => ['required', 'numeric'],
            ],
            [
                'beginningbalance_no.required' => 'Adjustment Number Required',
                'receipt_date.required' => 'Date Required',
                'receipt_date.date' => 'Date Must Be Valid Date',
                'receipt_date.before_or_equal' => 'Date Cannot Be Future',
                'transaction_date.required' => 'Date Required',
                'transaction_date.date' => 'Date Must Be Valid Date',
                'transaction_date.before_or_equal' => 'Date Cannot Be Future',
                'customer_code.required' => 'Customer Code Required',
                'name.required' => 'Customer Name Required',
                'particular.required' => 'Particular Required',
                'balance_amount.required' => 'Amount Required',
                'balance_amount.numeric' => 'Amount Must Be Valid number',
            ]
        );

        DB::transaction(function () use ($validated, $request) {
            $floatingExists = PaymentDetails::where('customer_code', $validated['customer_code'])
                ->where('document_date', '<=', $validated['receipt_date'])
                ->where('status', 'floating')
                ->exists();

            if ($floatingExists) {
                throw ValidationException::withMessages([
                    'general' => "{$validated['name']} still has floating payments. Please clear them first.",
                ]);
            }

            CustomerLedger::where('customer_code', $validated['customer_code'])
                ->where('date', '<=', $validated['receipt_date'])
                ->delete(); 

            CustomerLedger::create([
                'invoice_number' => $validated['beginningbalance_no'],
                'date' => $validated['transaction_date'],
                'type' => "BG",
                'customer_code' => $validated['customer_code'],
                'customer_name' => $validated['name'],
                'currency' => "Php",
                'amount' => $validated['balance_amount'],
                'adjusted_amount' => 0.00,
                'amount_paid' => 0.00,
                'running_balance' => $validated['balance_amount'],
            ]);

            $validated['created_by'] = $request->user()->name;
            BeginningBalance::create($validated);

            event(new NewCreated('beginningbalance'));
            event(new NewCreated('customerledger'));
        });
    }

    public function AddMultipleBeginningBalance(Request $request)
    {
        $validated = $request->validate(
            [
                'receipt_date' => 'required|date|before_or_equal:today',
                'transaction_date' => 'required|date',
                'importFile' => 'required|file',
            ],
            [
                'receipt_date.required' => 'Date Required',
                'receipt_date.date' => 'Date Must Be Valid Date',
                'receipt_date.before_or_equal' => 'Date Cannot Be Future',
                'transaction_date.required' => 'Date Required',
                'transaction_date.date' => 'Date Must Be Valid Date',
                'importFile.required' => 'Please Select Excel File',
            ]
        );

        $extension = strtolower($request->file('importFile')->getClientOriginalExtension());

        if (!in_array($extension, ['xls', 'xlsx'])) {
            return back()->withErrors(['importFile' => 'Only Excel (.xls, .xlsx) files are allowed.']);
        }

        $filename = $request->file('importFile')->getClientOriginalName();
        $directory = 'beginning_balances';
        $disk = 'public';

        // Check if the file already exists
        // if (Storage::disk($disk)->exists("$directory/$filename")) {
        //     return back()->withErrors(['importFile' => 'A file with this name already exists. Please rename your file and try again.']);
        // }

        // Store the file if it doesn't exist
        $filePath = $request->file('importFile')->storeAs($directory, $filename, $disk);

        // Process Excel file
        $data = Excel::toArray([], $request->file('importFile'))[0];

        // Remove header row
        array_shift($data);

        $errors = [];
        $successCount = 0;
        $preparedRecords = [];
        $customerCodes = [];
        $ledgerRecords = [];

        // Generate beginningbalance_no sequence
        $latestBeginningBalance = BeginningBalance::withTrashed()
            ->lockForUpdate()
            ->orderByDesc('beginningbalance_no')
            ->first();

        $nextNumber = $latestBeginningBalance
            ? $latestBeginningBalance->beginningbalance_no + 1
            : 25000001;

        DB::beginTransaction();

        try {
            foreach ($data as $index => $row) {
                $customerCode = $row[0] ?? null;
                $customerName = $row[1] ?? null;
                $fileAmount = $row[2];

                // Validate required fields
                if (!$customerCode || !$customerName || $fileAmount === null) {
                    $errors[] = "Row " . ($index + 2) . ": Missing required fields";
                    continue;
                }

                // Validate amount format
                if (!preg_match('/^\d{1,3}(,\d{3})*(\.\d+)?$|^\d+(\.\d+)?$/', $fileAmount)) {
                    // Log::info('AMOUNT : ' . $fileAmount);
                    $errors[] = "Row " . ($index + 2) . ": Invalid amount format for customer $customerCode";
                    continue;
                }
                $numericAmount = (float) str_replace(',', '', $fileAmount);

                if (in_array($customerCode, $customerCodes)) {
                    $errors[] = "Row " . ($index + 2) . ": Duplicate customer code '$customerCode' in the file.";
                    continue;
                }

                $customerCodes[] = $customerCode;

                // Create beginning balance record for this customer
                $preparedRecords[] = [
                    'beginningbalance_no' => $nextNumber + $index,
                    'receipt_date' => $validated['receipt_date'],
                    'transaction_date' => $validated['transaction_date'],
                    'customer_code' => $customerCode,
                    'name' => $customerName,
                    'particular' => 'N/A',
                    'balance_amount' => $numericAmount,
                    'file' => $filePath,
                    'created_by' => $request->user()->name,
                    'created_at' => now(),
                    'updated_at' => now(),

                ];

                if (!is_null($numericAmount) && $numericAmount != 0) {
                    $ledgerRecords[] = [
                        'invoice_number' => $nextNumber + $index,
                        'date' => $validated['receipt_date'],
                        'type' => "BG",
                        'customer_code' => $customerCode,
                        'customer_name' => $customerName,
                        'currency' => "Php",
                        'amount' => $numericAmount,
                        'adjusted_amount' => 0.00,
                        'amount_paid' => 0.00,
                        'running_balance' => $numericAmount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                Storage::disk($disk)->delete("$directory/$filename");
                return back()->withErrors(['importFile' => $errors]);
            }

            $floatingCustomers = [];

            foreach ($customerCodes as $customerCode) {
                $floatingExists = PaymentDetails::where('customer_code', $customerCode)
                    ->where('document_date', '<=', $validated['receipt_date'])
                    ->where('status', 'floating')
                    ->exists();

                if ($floatingExists) {
                    $floatingCustomers[] = $customerCode;
                }
            }

            if (!empty($floatingCustomers)) {
                throw ValidationException::withMessages([
                    'general' => 'The following customers still have floating payments: ' . implode(', ', $floatingCustomers) . '. Please clear them first.',
                ]);
            }

            // Loop 2: Do deletions only after all validation passes
            foreach ($customerCodes as $customerCode) {
                $deletedCount = CustomerLedger::where('customer_code', $customerCode)
                    ->where('type', '!=', 'BG')
                    ->where('date', '<=', $validated['receipt_date'])
                    ->delete();

                $successCount += $deletedCount;
            }

            CustomerLedger::insert($ledgerRecords);

            // Bulk insert all valid records
            BeginningBalance::insert($preparedRecords);

            event(new NewCreated('beginningbalance'));
            event(new NewCreated('customerledger'));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'importFile' => '' . $e->getMessage()
            ]);
        }
    }


    public function destroy($id)
    {
        $adj = BeginningBalance::findorFail($id);
        $adj->delete();
    }

    public function latest()
    {
        //return Adjustment::orderByDesc('id')->value('adjustment_no'); // returns "25000001" or null
        return DB::transaction(function () {
            $latestBeginningBalance = BeginningBalance::withTrashed()
                ->lockForUpdate() // Prevent concurrent access
                ->orderByDesc('beginningbalance_no')
                ->first();

            $nextNumber = $latestBeginningBalance ? $latestBeginningBalance->beginningbalance_no + 1 : 25000001;

            return response()->json([
                'next_beginningbalance_no' => $nextNumber,
                'is_new_sequence' => !$latestBeginningBalance
            ]);
        });
    }

    public function getAllTransactions(Request $request)
    {
        $customerCode = $request->input('type');

        // First get all invoice numbers for this customer from the Invoice table
        $ledgers = CustomerLedger::where('customer_code', $customerCode)
            ->select('invoice_number', 'date', 'type', 'amount', 'amount_paid', 'running_balance')
            ->get();

        // Format the results
        $result = $ledgers->map(function ($ledger) {
            return [
                'invoice_no' => $ledger->invoice_number,
                'receipt_date' => $ledger->date,
                'type' => $ledger->type,
                'amount' => $ledger->amount,
                'amount_paid' => $ledger->amount_paid,
                'running_balance' => $ledger->running_balance
            ];
        });

        return response()->json($result);
    }
}
