<?php

namespace App\Http\Controllers\TransactionControllers;

use App\Events\NewCreated;
use App\Events\NotificationEvent;
use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationsController;
use App\Models\MasterfileModels\Customer;
use App\Models\MasterfileModels\User;
use App\Models\ReportModels\CustomerLedger;
use App\Models\TransactionModels\Invoice;
use App\Models\TransactionModels\Payment;
use App\Models\TransactionModels\PaymentDetails;
use App\Services\CustomerService;
use App\Services\GlobalApiServices;
use App\Services\InvoiceNumberService;
use App\Services\InvoiceService;
use App\Services\PaymentNumberService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $cashPaymentFromInvoiceDirect = false;

    public function index(Request $request)
    {
        $query = Payment::query();

        // Search functionality
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('payment_no', 'like', '%' . $request->search . '%');
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
        if ($request->type_filtersPaymentType) {
            $types = is_array($request->type_filtersPaymentType)
                ? $request->type_filtersPaymentType
                : explode(',', $request->type_filtersPaymentType);
            $query->whereIn('payment_type', $types);
        }

        // Code sorting
        if ($request->code_sort) {
            $query->orderBy('payment_no', $request->code_sort === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('payment_no', 'desc');
        }

        return Inertia::render('Payment', [
            'payments' => $query->paginate(10)->withQueryString(),
            'searchTerm' => $request->search,
            'filters' => [
                'code_sort' => $request->code_sort,
                'type_filters' => $request->type_filters ?
                    (is_array($request->type_filters) ?
                        $request->type_filters :
                        explode(',', $request->type_filters)) :
                    [],
                'type_filtersPaymentType' => $request->type_filtersPaymentType ?
                    (is_array($request->type_filtersPaymentType) ?
                        $request->type_filtersPaymentType :
                        explode(',', $request->type_filtersPaymentType)) :
                    [],
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
            ],
            'broadcastChannel' => 'payments',
        ]);
    }

    public function addPayment(Request $request, PaymentNumberService $paymentNumberService, InvoiceNumberService $invoiceNumberService, GlobalApiServices $globalApi)
    {
        $odConfirmed = $request->input('_od_confirmation', false);
        $checkConfirmed = $request->input('_check_confirmation', false);
        $cashConfirmed = $request->input('_cash_confirmation', false);
        $cl_type = $request->input('_cl_type', '');

        $customMessages = [
            // Base rules messages
            'payment_no.required' => 'Payment Number Required',
            'receipt_date.required' => 'Receipt Date Required',
            'receipt_date.before_or_equal' => 'Receipt Date Cannot Be Future',
            'transaction_date.required' => 'Transaction Date Required',
            'transaction_date.before_or_equal' => 'Transaction Date Cannot Be Future',
            'customer_code.required' => 'Customer Code Required',
            'name.required' => 'Customer Name Required',
            'payment_type.required' => 'Payment Type Required',
            'type.required' => 'Transaction Type Required',
            'document_no.required' => 'Document Number Required',
            'document_date.required' => 'Document Date Required',
            'total_amount.required' => 'Total Amount Required',
            'amount_paid.required' => 'Amount Paid Required',

            // Payment type specific messages
            'ds_no.required' => 'DS Number Required',
            'cash_in_bank.required' => 'Cash in Bank Required',
            'reference_no.required' => 'Reference Number Required',
            'acc_code.required' => 'Account Code Required',
            'cust_code.required' => 'Customer Code Required',
            'check_type.required' => 'Check Type Required',
            'aging_basis.required' => 'Aging Basis Required',
            'aging_days.required' => 'Aging Days Required',
            'acc_name_address.required' => 'Account Name/Address Required',
            'referral_name.required' => 'Referral Name Required',
            'acc_number.required' => 'Account Number Required',
            'due_date.required' => 'Due Date Required',
            'withBIR.required' => 'With BIR Status Required',
            'witholdingtax.required' => 'Withholding Tax Information Required',
        ];

        // Common validation rules for all payment types
        $baseRules = [
            'payment_no' => ['required', 'string'],
            'receipt_date' => ['required', 'date', 'before_or_equal:today'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'customer_code' => ['required', 'string'],
            'name' => ['required', 'string'],
            'payment_type' => ['required', 'in:5A - Cash,5B - Journal Voucher,5C - Online Deposit,5D - Check,5E - Creditable(WHT)'],
            'type' => ['required'],
            'document_no' => ['required', 'string'],
            'document_date' => ['required', 'date'],
            'advanced_payment_balance' => ['nullable', 'string'],
            'total_amount' => ['required', 'string'],
            'amount_paid' => [
                'required',
                'numeric',
                'between:0,99999999.99',
                function ($attribute, $value, $fail) use ($request) {
                    if (!empty($request->selectedDocuments)) {
                        if ($value > (float)preg_replace('/[^0-9.]/', '', $request->total_amount)) {
                            $fail('Amount Should Not Be Greater Than Available Balance');
                        }
                    }
                },
            ],
            'selectedDocuments' => ['nullable', 'array'],
        ];

        // Payment type specific rules
        $typeSpecificRules = [
            '5A - Cash' => [
                'ds_no' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        if ($value === 'DS#') {
                            $fail('DS Number is required');
                        }
                    },
                ],
                'cash_in_bank' => 'required|string',
            ],
            '5B - Journal Voucher' => [
                'reference_no' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        if ($value === 'JV#') {
                            $fail('Reference Number is required');
                        }
                    },
                ],
                'acc_code' => [
                    'nullable',
                    'string',
                    'required_without:cust_code',
                ],
                'cust_code' => [
                    'nullable',
                    'string',
                    'required_without:acc_code',
                ],
            ],
            '5C - Online Deposit' => $odConfirmed ? [
                'ds_no' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        if ($value === 'DS#') {
                            $fail('DS Number is required');
                        }
                    },
                ],
                'acc_code' => [
                    'nullable',
                    'string',
                    'required_without:cust_code',
                ],
                'cust_code' => [
                    'nullable',
                    'string',
                    'required_without:acc_code',
                ],
            ] : [
                'ds_no' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        if ($value === 'DS#') {
                            $fail('DS Number is required');
                        }
                    },
                ],
                'cash_in_bank' => 'required|string',
            ],
            '5D - Check' => $checkConfirmed ? [
                'reference_no' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        if ($value === 'CHK#') {
                            $fail('Reference Number is required');
                        }
                    },
                ],
                'cust_code' => 'required|string',
                'check_type' => 'required|string',
                'aging_basis' => 'required|string',
                'aging_days' => 'required|integer|min:0',
                'acc_name_address' => 'required|string',
                'referral_name' => 'required|string',
                'acc_number' => 'required|string',
                'due_date' => 'required|date',
            ] : [
                'reference_no' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        if ($value === 'CHK#') {
                            $fail('Reference Number is required');
                        }
                    },
                ],
                'cash_in_bank' => 'required|string',
                'check_type' => 'required|string',
                'aging_basis' => 'required|string',
                'aging_days' => 'required|integer|min:0',
                'acc_name_address' => 'required|string',
                'referral_name' => 'required|string',
                'acc_number' => 'required|string',
                'due_date' => 'required|date',
            ],
            '5E - Creditable(WHT)' => [
                'reference_no' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        if ($value === 'WHT#') {
                            $fail('Reference Number is required');
                        }
                    },
                ],
                'withBIR' => 'required|boolean',
                'witholdingtax' => 'required|string',
            ],
        ];

        // Merge base rules with payment type specific rules
        $validationRules = array_merge(
            $baseRules,
            $typeSpecificRules[$request->payment_type] ?? []
        );

        $validated = $request->validate($validationRules, $customMessages);

        $notificationsController = new NotificationsController();

        // Payment Process
        switch ($validated['payment_type']) {
            case '5A - Cash':
                if ($cashConfirmed) {
                    $processedDocuments = [];
                    $validated['total_amount'] = (float)preg_replace('/[^0-9.]/', '', $validated['total_amount']);
                    $validated['advanced_payment_balance'] = 0;
                    $processedDocuments[] = [
                        'document_no' => $validated['document_no'],
                        'type' => $validated['type'],
                        'amount' => $validated['total_amount'],
                        'balance' => $validated['total_amount'],
                        'amount_applied' => $validated['total_amount'],
                        'document_date' => $validated['document_date'],
                    ];
                    $validated['selectedDocuments'] = $processedDocuments;
                    $pyNo = $this->createPaymentRecords($validated, $request, 'Paid', $paymentNumberService, 'Cash Confirmed', $processedDocuments);
                } else {
                    $pyNo = $this->processPayment($validated, $request, 'Paid', $cl_type, $paymentNumberService);
                }

                break;

            case '5B - Journal Voucher':
                $pyNo = DB::transaction(function () use ($validated, $request, $cl_type, $invoiceNumberService, $paymentNumberService) {
                    $pynum = $this->processPayment($validated, $request, 'Paid', $cl_type, $paymentNumberService);
                    if ($validated['cust_code']) {
                        $this->createArRecords($validated, $request, $invoiceNumberService);
                    }
                    return $pynum;
                });

                // Compare total amount vs. amount paid
                $totalAmount = (float)preg_replace('/[^0-9.]/', '', $validated['total_amount'] ?? 0);
                $amountPaid = (float)preg_replace('/[^0-9.]/', '', $validated['amount_paid'] ?? 0);
                $status = $amountPaid < $totalAmount ? 'Partial' : 'Paid';

                // Send update to invoice system
                $globalApi->AddPayment($status, $validated['document_no'], $pyNo);

                break;

            case '5C - Online Deposit':
                if ($odConfirmed) {
                    $pyNo = DB::transaction(function () use ($validated, $request, $cl_type, $invoiceNumberService, $paymentNumberService) {
                        $pynum = $this->processPayment($validated, $request, 'Paid', $cl_type, $paymentNumberService);
                        if ($validated['cust_code']) {
                            $this->createArRecords($validated, $request, $invoiceNumberService);
                        }
                        return $pynum;
                    });
                } else {
                    $pyNo = $this->processPayment($validated, $request, 'Paid', $cl_type, $paymentNumberService);
                }

                // Compare total amount vs. amount paid
                $totalAmount = (float)preg_replace('/[^0-9.]/', '', $validated['total_amount'] ?? 0);
                $amountPaid = (float)preg_replace('/[^0-9.]/', '', $validated['amount_paid'] ?? 0);
                $status = $amountPaid < $totalAmount ? 'Partial' : 'Paid';

                // Send update to invoice system
                $globalApi->AddPayment($status, $validated['document_no'], $pyNo);

                break;

            case '5D - Check':
                if ($checkConfirmed) {
                    $pyNo = DB::transaction(function () use ($validated, $request, $invoiceNumberService, $paymentNumberService) {
                        $pynum = $this->createDirectPaymentRecords($validated, $request, $paymentNumberService);
                        $this->createArRecords($validated, $request, $invoiceNumberService);
                        return $pynum;
                    });
                } else {
                    $pyNo = $this->createDirectPaymentRecords($validated, $request, $paymentNumberService);
                }
                $notificationsController->index($request);

                $userIds = User::whereIn('role', ['Admin', 'Accounting'])
                    ->pluck('id')
                    ->unique();

                foreach ($userIds as $userId) {
                    $channel = 'notification-update.' . Str::random(20);
                    broadcast(new NotificationEvent($userId, $channel));
                }

                // Compare total amount vs. amount paid
                $totalAmount = (float)preg_replace('/[^0-9.]/', '', $validated['total_amount'] ?? 0);
                $amountPaid = (float)preg_replace('/[^0-9.]/', '', $validated['amount_paid'] ?? 0);
                $status = $amountPaid < $totalAmount ? 'Partial' : 'Paid';

                // Send update to invoice system
                $globalApi->AddPayment($status, $validated['document_no'], $pyNo);


                break;

            case '5E - Creditable(WHT)':
                if ($validated['withBIR'] === '1' || $validated['withBIR'] === true || $validated['withBIR'] === 1) {
                    $pyNo = $this->processPayment($validated, $request, 'Paid', $cl_type, $paymentNumberService);
                } else {
                    $pyNo = $this->createDirectPaymentRecords($validated, $request, $paymentNumberService);
                    $notificationsController->index($request);

                    $userIds = User::whereIn('role', ['Admin', 'Accounting'])
                        ->pluck('id')
                        ->unique();

                    foreach ($userIds as $userId) {
                        $channel = 'notification-update.' . Str::random(20);
                        broadcast(new NotificationEvent($userId, $channel));
                    }
                }

                // Compare total amount vs. amount paid
                $totalAmount = (float)preg_replace('/[^0-9.]/', '', $validated['total_amount'] ?? 0);
                $amountPaid = (float)preg_replace('/[^0-9.]/', '', $validated['amount_paid'] ?? 0);
                $status = $amountPaid < $totalAmount ? 'Partial' : 'Paid';

                // Send update to invoice system
                $globalApi->AddPayment($status, $validated['document_no'], $pyNo);


                break;
        }

        event(new NewCreated('payment'));
        session()->put('payment_number', $pyNo);
        return redirect()->back();
    }

    //THERE IS UPDATE IN CUSTOMER LEDGER
    private function processPayment($validated, $request, $status, $cl_type, $paymentNumberService)
    {
        return DB::transaction(function () use ($validated, $request, $status, $cl_type, $paymentNumberService) {
            $processedDocuments = [];

            if (!empty($validated['selectedDocuments'])) { //MANUAL PAYMENT
                if ($validated['payment_type'] === '5E - Creditable(WHT)' && $validated['witholdingtax'] !== 'Custom Amount') {
                    foreach ($validated['selectedDocuments'] as $doc) {
                        // Find the specific ledger row by document_no and type
                        $ledger = CustomerLedger::where('customer_code', $validated['customer_code'])
                            ->where('invoice_number', $doc['docunumber'])
                            ->where('type', $doc['type'])
                            ->lockForUpdate()
                            ->first();

                        // Preload all floating amounts grouped by document_no AND type
                        $floatingAmounts = PaymentDetails::where('customer_code', $validated['customer_code'])
                            ->where('status', 'Floating')
                            ->selectRaw('document_no, type, SUM(amount_paid) as total_floating')
                            ->groupBy('document_no', 'type')
                            ->get()
                            ->groupBy(['document_no', 'type']);

                        if (!$ledger) {
                            throw ValidationException::withMessages([
                                'general' => 'Error Please Try Again',
                            ]);
                        }

                        // Get floating amount for this doc
                        $floatingAmount = $floatingAmounts
                            ->get($doc['docunumber'], collect())
                            ->get($doc['type'], collect())
                            ->first();

                        if ($floatingAmount?->total_floating) {
                            continue;
                        }

                        if ($validated['witholdingtax'] === '1%') {
                            $wht = 0.01;
                        } else if ($validated['witholdingtax'] === '2%') {
                            $wht = 0.02;
                        } else if ($validated['witholdingtax'] === '5%') {
                            $wht = 0.05;
                        }

                        $floatingValue = $floatingAmount?->total_floating ?? 0;
                        $amountToApply = $ledger->running_balance * $wht;

                        $docbalance = $ledger->running_balance  - $amountToApply;

                        $ledger->update([
                            'running_balance' => $ledger->running_balance  - $amountToApply,
                            'amount_paid' => $ledger->amount_paid + $amountToApply,
                        ]);

                        $processedDocuments[] = [
                            'document_no' => $doc['docunumber'],
                            'type' => $doc['type'],
                            'amount' => $doc['amount'],
                            'balance' => $docbalance,
                            'amount_applied' => $amountToApply,
                            'document_date' => $ledger->date,
                            'floating_deducted' => $floatingValue,
                        ];
                    }
                } else {
                    foreach ($validated['selectedDocuments'] as $doc) {
                        $amountToApply = floatval($doc['amountToPay']); // Directly use amountToPay
                        if ($amountToApply <= 0) {
                            throw ValidationException::withMessages([
                                'general' => 'Error Please Try Again',
                            ]);
                        }

                        // Find the specific ledger row by document_no and type
                        $ledger = CustomerLedger::where('customer_code', $validated['customer_code'])
                            ->where('invoice_number', $doc['docunumber'])
                            ->where('type', $doc['type'])
                            ->lockForUpdate()
                            ->first();

                        // Preload all floating amounts grouped by document_no AND type
                        $floatingAmounts = PaymentDetails::where('customer_code', $validated['customer_code'])
                            ->where('status', 'Floating')
                            ->selectRaw('document_no, type, SUM(amount_paid) as total_floating')
                            ->groupBy('document_no', 'type')
                            ->get()
                            ->groupBy(['document_no', 'type']);

                        if (!$ledger) {
                            throw ValidationException::withMessages([
                                'general' => 'Error Please Try Again',
                            ]);
                        }

                        // Get floating amount for this doc
                        $floatingAmount = $floatingAmounts
                            ->get($doc['docunumber'], collect())
                            ->get($doc['type'], collect())
                            ->first();

                        $floatingValue = $floatingAmount?->total_floating ?? 0;
                        $effectiveBalance = $ledger->running_balance - $floatingValue;

                        // Don't overpay beyond effective balance
                        if ($amountToApply > $effectiveBalance) {
                            throw ValidationException::withMessages([
                                'amount_paid' => "Amount to pay ({$amountToApply}) exceeds available balance ({$effectiveBalance}) for document {$doc['docunumber']}",
                            ]);
                        }

                        $docbalance = $ledger->running_balance  - $amountToApply;

                        $ledger->update([
                            'running_balance' => $ledger->running_balance  - $amountToApply,
                            'amount_paid' => $ledger->amount_paid + $amountToApply,
                        ]);

                        $processedDocuments[] = [
                            'document_no' => $doc['docunumber'],
                            'type' => $doc['type'],
                            'amount' => $doc['amount'],
                            'balance' => $docbalance,
                            'amount_applied' => $amountToApply,
                            'document_date' => $ledger->date,
                            'floating_deducted' => $floatingValue,
                        ];
                    }
                }
            } else {
                //OLDEST TO NEWEST PAYMENT
                if ($validated['payment_type'] === '5E - Creditable(WHT)') {
                    // Get all customer ledgers ordered by date and ID (oldest first)
                    $ledgers = CustomerLedger::where('customer_code', $validated['customer_code'])
                        ->where('amount_paid', 0)
                        ->orderBy('date')
                        ->orderBy('created_at')
                        ->lockForUpdate()
                        ->get();

                    // Preload all floating amounts grouped by document_no AND type
                    $floatingAmounts = PaymentDetails::where('customer_code', $validated['customer_code'])
                        ->where('status', 'Floating')
                        ->selectRaw('document_no, type, SUM(amount_paid) as total_floating')
                        ->groupBy('document_no', 'type')
                        ->get()
                        ->groupBy(['document_no', 'type']);

                    foreach ($ledgers as $ledger) {
                        // Get floating amount for this specific document AND type
                        $floatingAmount = $floatingAmounts
                            ->get($ledger->invoice_number, collect())
                            ->get($ledger->type, collect())
                            ->first();

                        if ($floatingAmount?->total_floating) {
                            continue;
                        }

                        // Calculate effective balance after floating deduction
                        if ($validated['witholdingtax'] === '1%') {
                            $wht = 0.01;
                        } else if ($validated['witholdingtax'] === '2%') {
                            $wht = 0.02;
                        } else if ($validated['witholdingtax'] === '5%') {
                            $wht = 0.05;
                        }
                        $amountToApply = $ledger->running_balance * $wht;

                        $docbalance = $ledger->running_balance  - $amountToApply;
                        $ledger->update([
                            'running_balance' => $ledger->running_balance - $amountToApply,
                            'amount_paid' => $ledger->amount_paid + $amountToApply,
                        ]);

                        $processedDocuments[] = [
                            'document_no' => $ledger->invoice_number,
                            'type' => $ledger->type,
                            'amount' => $ledger->amount,
                            'balance' => $docbalance,
                            'amount_applied' => $amountToApply,
                            'document_date' => $ledger->date,
                            'floating_deducted' => $floatingAmount?->total_floating ?? 0
                        ];
                    }
                } else {
                    // $remainingAmount = $validated['amount_paid'] + (float)preg_replace('/[^0-9.]/', '', $validated['advanced_payment_balance']);
                    $cust = Customer::where('cus_code', $validated['customer_code'])->lockForUpdate()->firstOrFail();

                    $remainingAdvPayment = $cust->advanced_payment_balance;
                    $remainingAmountPaid = $validated['amount_paid'];

                    // Get all customer ledgers ordered by date and ID (oldest first)
                    $ledgers = CustomerLedger::where('customer_code', $validated['customer_code'])
                        ->where('running_balance', '!=', 0)
                        ->orderBy('date')
                        ->orderBy('created_at')
                        ->lockForUpdate()
                        ->get();

                    // Preload all floating amounts grouped by document_no AND type
                    $floatingAmounts = PaymentDetails::where('customer_code', $validated['customer_code'])
                        ->where('status', 'Floating')
                        ->selectRaw('document_no, type, SUM(amount_paid) as total_floating')
                        ->groupBy('document_no', 'type')
                        ->get()
                        ->groupBy(['document_no', 'type']);

                    foreach ($ledgers as $ledger) {
                        if ($remainingAdvPayment <= 0 && $remainingAmountPaid <= 0) {
                            break;
                        }

                        // Get floating amount for this specific document AND type
                        $floatingAmount = $floatingAmounts
                            ->get($ledger->invoice_number, collect())
                            ->get($ledger->type, collect())
                            ->first();


                        // Calculate effective balance after floating deduction
                        $effectiveBalance = $ledger->running_balance - ($floatingAmount?->total_floating ?? 0);

                        // Only proceed if there's positive balance after floating deduction
                        if ($effectiveBalance > 0) {
                            // 1️⃣ Apply advance payment first
                            $fromAdv = min($remainingAdvPayment, $effectiveBalance);
                            $remainingAdvPayment -= $fromAdv;

                            // 2️⃣ Apply normal payment for remaining balance
                            $remainingDocBalance = $effectiveBalance - $fromAdv;
                            $fromPaid = min($remainingAmountPaid, $remainingDocBalance);
                            $remainingAmountPaid -= $fromPaid;

                            // Total applied to this document
                            $amountToApply = $fromAdv + $fromPaid;

                            if ($amountToApply > 0) {
                                $docbalance = $ledger->running_balance  - $amountToApply;
                                $ledger->update([
                                    'running_balance' => $ledger->running_balance - $amountToApply,
                                    'amount_paid' => $ledger->amount_paid + $amountToApply,
                                ]);

                                $processedDocuments[] = [
                                    'document_no' => $ledger->invoice_number,
                                    'type' => $ledger->type,
                                    'amount' => $ledger->amount,
                                    'balance' => $docbalance,
                                    'amount_applied' => $amountToApply,
                                    'advpy_amount_applied' => $fromAdv,
                                    'document_date' => $ledger->date,
                                    'floating_deducted' => $floatingAmount?->total_floating ?? 0
                                ];
                            }
                        }
                    }

                    $cust->update([
                        'advanced_payment_balance' => $remainingAdvPayment + $remainingAmountPaid,
                    ]);

                    // if ($remainingAmount > 0) {
                    //     throw ValidationException::withMessages([
                    //         'amount_paid' => 'Payment amount exceeds total outstanding balance after floating deductions',
                    //     ]);
                    // }
                }
            }

            return $this->createPaymentRecords($validated, $request, $status, $paymentNumberService, '', $processedDocuments);
        });
    }

    //NO UPDATE IN CUSTOMER LEDGER
    private function createDirectPaymentRecords($validated, $request, $paymentNumberService)
    {
        return DB::transaction(function () use ($validated, $request, $paymentNumberService) {
            $processedDocuments = [];

            if (!empty($validated['selectedDocuments'])) { //MANUAL PAYMENT
                if ($validated['payment_type'] === '5E - Creditable(WHT)' && $validated['witholdingtax'] !== 'Custom Amount') {
                    foreach ($validated['selectedDocuments'] as $doc) {
                        // Find the specific ledger row by document_no and type
                        $ledger = CustomerLedger::where('customer_code', $validated['customer_code'])
                            ->where('invoice_number', $doc['docunumber'])
                            ->where('type', $doc['type'])
                            ->lockForUpdate()
                            ->first();

                        // Preload all floating amounts grouped by document_no AND type
                        $floatingAmounts = PaymentDetails::where('customer_code', $validated['customer_code'])
                            ->where('status', 'Floating')
                            ->selectRaw('document_no, type, SUM(amount_paid) as total_floating')
                            ->groupBy('document_no', 'type')
                            ->get()
                            ->groupBy(['document_no', 'type']);

                        if (!$ledger) {
                            throw ValidationException::withMessages([
                                'general' => 'Error Please Try Again',
                            ]);
                        }

                        // Get floating amount for this doc
                        $floatingAmount = $floatingAmounts
                            ->get($doc['docunumber'], collect())
                            ->get($doc['type'], collect())
                            ->first();

                        if ($floatingAmount?->total_floating) {
                            continue;
                        }

                        if ($validated['witholdingtax'] === '1%') {
                            $wht = 0.01;
                        } else if ($validated['witholdingtax'] === '2%') {
                            $wht = 0.02;
                        } else if ($validated['witholdingtax'] === '5%') {
                            $wht = 0.05;
                        }

                        $floatingValue = $floatingAmount?->total_floating ?? 0;
                        $amountToApply = $ledger->running_balance * $wht;

                        $processedDocuments[] = [
                            'document_no' => $doc['docunumber'],
                            'type' => $doc['type'],
                            'amount' => $doc['amount'],
                            'balance' => $ledger->running_balance - $amountToApply,
                            'amount_applied' => $amountToApply,
                            'document_date' => $ledger->date,
                            'floating_deducted' => $floatingValue,
                        ];
                    }
                } else {

                    foreach ($validated['selectedDocuments'] as $doc) {
                        $amountToApply = floatval($doc['amountToPay']); // Directly use amountToPay
                        if ($amountToApply <= 0) {
                            throw ValidationException::withMessages([
                                'general' => 'Error Please Try Again',
                            ]);
                        }

                        // Find the specific ledger row by document_no and type
                        $ledger = CustomerLedger::where('customer_code', $validated['customer_code'])
                            ->where('invoice_number', $doc['docunumber'])
                            ->where('type', $doc['type'])
                            ->lockForUpdate()
                            ->first();

                        // Preload all floating amounts grouped by document_no AND type
                        $floatingAmounts = PaymentDetails::where('customer_code', $validated['customer_code'])
                            ->where('status', 'Floating')
                            ->selectRaw('document_no, type, SUM(amount_paid) as total_floating')
                            ->groupBy('document_no', 'type')
                            ->get()
                            ->groupBy(['document_no', 'type']);

                        if (!$ledger) {
                            throw ValidationException::withMessages([
                                'general' => 'Error Please Try Again',
                            ]);
                        }

                        // Get floating amount for this doc
                        $floatingAmount = $floatingAmounts
                            ->get($doc['docunumber'], collect())
                            ->get($doc['type'], collect())
                            ->first();

                        $floatingValue = $floatingAmount?->total_floating ?? 0;
                        $effectiveBalance = $ledger->running_balance - $floatingValue;

                        // Don't overpay beyond effective balance
                        if ($amountToApply > $effectiveBalance) {
                            throw ValidationException::withMessages([
                                'amount_paid' => "Amount to pay ({$amountToApply}) exceeds available balance ({$effectiveBalance}) for document {$doc['docunumber']}",
                            ]);
                        }

                        $processedDocuments[] = [
                            'document_no' => $doc['docunumber'],
                            'type' => $doc['type'],
                            'amount' => $doc['amount'],
                            'balance' => $ledger->running_balance - $amountToApply,
                            'amount_applied' => $amountToApply,
                            'document_date' => $ledger->date,
                            'floating_deducted' => $floatingValue,
                        ];
                    }
                }
            } else { //OLDEST TO NEWEST PAYMENT
                if ($validated['payment_type'] === '5E - Creditable(WHT)') {
                    // Get all customer ledgers ordered by date and ID (oldest first)
                    $ledgers = CustomerLedger::where('customer_code', $validated['customer_code'])
                        ->where('amount_paid', 0)
                        ->orderBy('date')
                        ->orderBy('created_at')
                        ->lockForUpdate()
                        ->get();

                    // Preload all floating amounts grouped by document_no AND type
                    $floatingAmounts = PaymentDetails::where('customer_code', $validated['customer_code'])
                        ->where('status', 'Floating')
                        ->selectRaw('document_no, type, SUM(amount_paid) as total_floating')
                        ->groupBy('document_no', 'type')
                        ->get()
                        ->groupBy(['document_no', 'type']);

                    foreach ($ledgers as $ledger) {
                        // Get floating amount for this specific document AND type
                        $floatingAmount = $floatingAmounts
                            ->get($ledger->invoice_number, collect())
                            ->get($ledger->type, collect())
                            ->first();

                        if ($floatingAmount?->total_floating) {
                            continue;
                        }

                        // Calculate effective balance after floating deduction
                        if ($validated['witholdingtax'] === '1%') {
                            $wht = 0.01;
                        } else if ($validated['witholdingtax'] === '2%') {
                            $wht = 0.02;
                        } else if ($validated['witholdingtax'] === '5%') {
                            $wht = 0.05;
                        }
                        $amountToApply = $ledger->running_balance * $wht;

                        $processedDocuments[] = [
                            'document_no' => $ledger->invoice_number,
                            'type' => $ledger->type,
                            'amount' => $ledger->amount,
                            'balance' => $ledger->running_balance - $amountToApply,
                            'amount_applied' => $amountToApply,
                            'document_date' => $ledger->date,
                            'floating_deducted' => $floatingAmount?->total_floating ?? 0
                        ];
                    }
                } else {

                    $cust = Customer::where('cus_code', $validated['customer_code'])->lockForUpdate()->firstOrFail();

                    $remainingAdvPayment = $cust->advanced_payment_balance;
                    $remainingAmountPaid = $validated['amount_paid'];

                    // Get all customer ledgers ordered by date and ID (oldest first)
                    $ledgers = CustomerLedger::where('customer_code', $validated['customer_code'])
                        ->orderBy('date')
                        ->orderBy('created_at')
                        ->lockForUpdate()
                        ->get();

                    // Preload all floating amounts grouped by document_no AND type
                    $floatingAmounts = PaymentDetails::where('customer_code', $validated['customer_code'])
                        ->where('status', 'Floating')
                        ->selectRaw('document_no, type, SUM(amount_paid) as total_floating')
                        ->groupBy('document_no', 'type')
                        ->get()
                        ->groupBy(['document_no', 'type']);

                    foreach ($ledgers as $ledger) {
                        if ($remainingAdvPayment <= 0 && $remainingAmountPaid <= 0) {
                            break;
                        }

                        // Get floating amount for this specific document AND type
                        $floatingAmount = $floatingAmounts
                            ->get($ledger->invoice_number, collect())
                            ->get($ledger->type, collect())
                            ->first();



                        // Calculate effective balance after floating deduction
                        $effectiveBalance = $ledger->running_balance - ($floatingAmount?->total_floating ?? 0);

                        // Only proceed if there's positive balance after floating deduction
                        if ($effectiveBalance > 0) {
                            // 1️⃣ Apply advance payment first
                            $fromAdv = min($remainingAdvPayment, $effectiveBalance);
                            $remainingAdvPayment -= $fromAdv;

                            // 2️⃣ Apply normal payment for remaining balance
                            $remainingDocBalance = $effectiveBalance - $fromAdv;
                            $fromPaid = min($remainingAmountPaid, $remainingDocBalance);
                            $remainingAmountPaid -= $fromPaid;

                            // Total applied to this document
                            $amountToApply = $fromAdv + $fromPaid;

                            if ($amountToApply > 0) {
                                $processedDocuments[] = [
                                    'document_no' => $ledger->invoice_number,
                                    'type' => $ledger->type,
                                    'amount' => $ledger->amount,
                                    'balance' => $ledger->running_balance - $amountToApply,
                                    'amount_applied' => $amountToApply,
                                    'advpy_amount_applied' => $fromAdv,
                                    'document_date' => $ledger->date,
                                    'floating_deducted' => $floatingAmount?->total_floating ?? 0
                                ];
                            }
                        }
                    }

                    $cust->update([
                        'advanced_payment_balance' => $remainingAdvPayment + $remainingAmountPaid,
                    ]);
                    // if ($remainingAmount > 0) {
                    //     throw ValidationException::withMessages([
                    //         'amount_paid' => 'Payment amount exceeds total outstanding balance with/without floating deductions',
                    //     ]);
                    // }
                }
            }
            return $this->createPaymentRecords($validated, $request, 'Floating', $paymentNumberService, '', $processedDocuments);
        });
    }

    private function createPaymentRecords($validated, $request, $status, $paymentNumberService, $cashConfirm, $processedDocuments)
    {
        return DB::transaction(function () use ($validated, $request, $status, $paymentNumberService, $cashConfirm, $processedDocuments) {
            $nextNumber = $paymentNumberService->generate();

            // Validate the payment number is unique
            if (Payment::where('payment_no', $nextNumber)->exists()) {
                throw ValidationException::withMessages([
                    'general' => 'Error Please Try Again',
                ]);
            }

            $documentNumbers = collect($processedDocuments)
                ->pluck('document_no')
                ->unique()
                ->implode(',');


            $dbData = collect($validated)
                ->except(['_od_confirmation', '_check_confirmation', '_cl_type'])
                ->all();

            $dbData['total_amount'] = (float)preg_replace('/[^0-9.]/', '', $dbData['total_amount']);
            $dbData['created_by'] = $request->user()->name;
            $dbData['payment_no'] = $nextNumber;
            $dbData['document_no'] = $documentNumbers;
            if (empty($validated['selectedDocuments'])) {
                $dbData['advpy_amount_paid'] = (float)preg_replace('/[^0-9.]/', '', $dbData['advanced_payment_balance']);
            } else {
                $dbData['advpy_amount_paid'] = 0;
            }
            Payment::create($dbData);

            $checkno = null;
            if ($cashConfirm != 'Cash Confirmed' && $validated['payment_type'] === '5D - Check') {
                if ($validated['check_type']) {
                    $checkno = $validated['reference_no'];
                }
            } else if ($cashConfirm != 'Cash Confirmed' && $validated['payment_type'] === '5E - Creditable(WHT)') {
                if ($validated['witholdingtax']) {
                    $checkno = $validated['reference_no'];
                }
            }

            if (empty($validated['selectedDocuments'])) { //OLDEST TO NEWEST
                foreach ($processedDocuments as $doc) {
                    PaymentDetails::create([
                        'payment_no' => $nextNumber,
                        'check_no' => $checkno,
                        'document_no' => $doc['document_no'],
                        'document_date' => $doc['document_date'],
                        'payment_receipt_date' => $validated['receipt_date'],
                        'payment_date' => $validated['transaction_date'],
                        'payment_type' => substr($validated['payment_type'], 5),
                        'type' => $doc['type'],
                        'customer_code' => $validated['customer_code'],
                        'customer_name' => $validated['name'],
                        'check_type' => $status === 'Floating' ? ($validated['check_type'] ?? 'N/A') : 'N/A',
                        'advpy_amount_paid' => $doc['advpy_amount_applied'],
                        'amount' => $doc['amount'],
                        'balance' => $doc['balance'],
                        'amount_paid' => $doc['amount_applied'],
                        'due_date' => $validated['due_date'] ?? null,
                        'status' => $status,
                        'created_by' => $request->user()->name,
                    ]);
                }
            } else { //MANUAL PAYMENT
                foreach ($processedDocuments as $doc) {
                    PaymentDetails::create([
                        'payment_no' => $nextNumber,
                        'check_no' => $checkno,
                        'document_no' => $doc['document_no'],
                        'document_date' => $doc['document_date'],
                        'payment_receipt_date' => $validated['receipt_date'],
                        'payment_date' => $validated['transaction_date'],
                        'payment_type' => substr($validated['payment_type'], 5),
                        'type' => $doc['type'],
                        'customer_code' => $validated['customer_code'],
                        'customer_name' => $validated['name'],
                        'check_type' => $status === 'Floating' ? ($validated['check_type'] ?? 'N/A') : 'N/A',
                        'advpy_amount_paid' => 0,
                        'amount' => $doc['amount'],
                        'balance' => $doc['balance'],
                        'amount_paid' => $doc['amount_applied'],
                        'due_date' => $validated['due_date'] ?? null,
                        'status' => $status,
                        'created_by' => $request->user()->name,
                    ]);
                }
            }

            return $nextNumber;
        });
    }

    private function createArRecords($validated, $request, $invoiceNumberService)
    {
        DB::transaction(function () use ($validated, $request, $invoiceNumberService) {
            $customer = CustomerService::getCustomerByCode($validated['cust_code']);
            $invoiceNumber = $invoiceNumberService->generate();

            if (Invoice::where('invoice_no', $invoiceNumber)->exists()) {
                throw ValidationException::withMessages([
                    'general' => 'Error Please Try Again',
                ]);
            }

            CustomerLedger::create([
                'invoice_number' => $invoiceNumber,
                'date' => $validated['transaction_date'],
                'type' => "Payment",
                'customer_code' => $validated['cust_code'],
                'customer_name' => $customer->cus_name,
                'currency' => "Php",
                'amount' => $validated['amount_paid'],
                'adjusted_amount' => 0.00,
                'amount_paid' => 0.00,
                'running_balance' => $validated['amount_paid'],
            ]);

            Invoice::create([
                'invoice_no' => $invoiceNumber,
                'receipt_date' => $validated['transaction_date'],
                'transaction_date' => $validated['transaction_date'],
                'customer_code' => $validated['cust_code'],
                'name' => $customer->cus_name,
                'price_group' => $customer->cus_type,
                'payment_mode' => "Account Receivables",
                'chargeinvoice_type' => "N/A",
                'particular' => "N/A",
                'reference_no' => "N/A",
                'total_amount' => $validated['amount_paid'],
                'created_by' => $request->user()->name,
            ]);
        });
    }

    public function destroy($id)
    {
        $adj = Payment::findorFail($id);
        $adj->delete();
    }

    public function latest(PaymentNumberService $paymentNumberService)
    {
        //return Payment::orderByDesc('id')->value('payment_no'); // returns "25000001" or null
        return DB::transaction(function () use ($paymentNumberService) {
            $nextNumber = $paymentNumberService->generate();
            // $latestPayment = Payment::withTrashed()
            //     ->lockForUpdate() // Prevent concurrent access
            //     ->orderByDesc('payment_no')
            //     ->first();

            // $nextNumber = $latestPayment ? $latestPayment->payment_no + 1 : 25000001;

            return response()->json([
                'next_payment_no' => $nextNumber,
            ]);
        });
    }

    public function latestPaymentNO()
    {
        return DB::transaction(function () {
            // Get the latest payment number from local database
            $latestPayment = Payment::withTrashed()
                ->lockForUpdate()
                ->orderByDesc('payment_no')
                ->first();

            $localNextNumber = $latestPayment ? $latestPayment->payment_no + 1 : 25000001;

            // Get the latest payment number from external API
            try {
                $response = Http::get('http://10.233.1.60:81/farms-invoicing-breeder/transactionController/SalesInvoiceController/getLatestPaymentNo');
                if ($response->successful()) {
                    $apiNextNumber = $response->json()['next_payment_no'];
                    $nextNumber = max($localNextNumber, $apiNextNumber);
                } else {
                    // If API fails, fall back to local number
                    $nextNumber = $localNextNumber;
                }
            } catch (\Exception $e) {
                // If there's any exception (connection error, etc.), use local number
                $nextNumber = $localNextNumber;
            }

            return response()->json([
                'next_payment_no' => $nextNumber,
            ]);
        });
    }

    public function generateNextPaymentNumber()
    {
        // Lock and get the highest payment number
        $latestPayment = Payment::withTrashed()
            ->lockForUpdate()
            ->orderByDesc('payment_no')
            ->first();

        // Extract numeric part (assuming payment_no is numeric)
        $localNextNumber = $latestPayment ? $latestPayment->payment_no + 1 : 25000001;

        // Get the latest payment number from external API
        try {
            // $response = Http::get('http://10.233.1.60:81/farms-invoicing-breeder/transactionController/SalesInvoiceController/getLatestPaymentNo');
            $response = Http::timeout(3) // 3 second timeout
                ->retry(2, 100) // Retry twice with 100ms delay
                ->get('http://10.233.1.60:81/farms-invoicing-breeder/transactionController/SalesInvoiceController/getLatestPaymentNo');
            if ($response->successful()) {
                $apiData = $response->json();
                $apiNextNumber = $apiData['next_payment_no'] ?? 0;
                $nextNumber = max($localNextNumber, $apiNextNumber);
            } else {
                $nextNumber = $localNextNumber;
            }
        } catch (\Exception $e) {
            $nextNumber = $localNextNumber;
        }

        return $nextNumber; // Return the number directly, not a JSON response
    }

    public function storePaymentAPI(Request $request, PaymentNumberService $paymentNumberService)
    {
        try {
            $validated = $request->validate([
                'payment_no' => ['required', 'string'], // Payment number
                'receipt_date' => ['required', 'date', 'before_or_equal:today'], // Receipt Date
                'transaction_date' => ['required', 'date', 'before_or_equal:today'], // Transaction Date
                'customer_code' => ['required', 'string'], // Customer Code
                'name' => ['required', 'string'], // Custome Name
                'payment_type' => ['required', 'in:5A - Cash,5B - Journal Voucher,5C - Online Deposit,5D - Check,5E - Creditable(WHT)'], // Payment Type EX: 5A - Cash,
                'type' => ['required', 'in:Sales Invoice,Charge Invoice,Payment,BG'], // Value must be Sales Invoice always
                'document_no' => ['required', 'string'], //Document No (Sales Invoice No)
                'document_date' => ['required', 'date'], //Document Date (Sales Invoice Receipt Date)
                'advpy_amount_paid' => ['required', 'numeric'],
                'total_amount' => ['required', 'string'], //Total AMount TO be Paid
                'amount_paid' => [
                    'required',
                    'numeric',
                    'between:0,99999999.99',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value > (float)preg_replace('/[^0-9.]/', '', $request->total_amount)) {
                            $fail('Amount Should Not Be Greater Than Available Balance');
                        }
                    },
                ], //AMOUNT PAID
                'created_by' => ['required', 'string'], //Surname, First Name (User) 
            ]);

            $pyNo = DB::transaction(function () use ($validated, $request, $paymentNumberService) {

                $nextNumber = $paymentNumberService->generate();

                // Validate the payment number is unique (just in case)
                if (Payment::where('payment_no', $nextNumber)->where('type', $validated['type'])->exists()) {
                    throw ValidationException::withMessages([
                        'general' => 'Duplicate payment number generated. Please retry.',
                    ]);
                }

                $validated['payment_no'] = $nextNumber;
                $validated['total_amount'] = (float)preg_replace('/[^0-9.]/', '', $validated['total_amount']);
                Payment::create($validated);

                PaymentDetails::create([
                    'payment_no' => $nextNumber,
                    'document_no' => $validated['document_no'],
                    'document_date' => $validated['document_date'],
                    'payment_receipt_date' => $validated['receipt_date'],
                    'payment_date' => $validated['transaction_date'],
                    'payment_type' => substr($validated['payment_type'], 5),
                    'type' => $validated['type'],
                    'customer_code' => $validated['customer_code'],
                    'customer_name' => $validated['name'],
                    'check_type' => 'N/A',
                    'advpy_amount_paid' => $validated['advpy_amount_paid'],
                    'amount' => $validated['total_amount'],
                    'balance' => $validated['total_amount'],
                    'amount_paid' => $validated['amount_paid'],
                    'status' => 'Paid',
                    'created_by' => $validated['created_by'],
                ]);

                return $nextNumber;
            });

            event(new NewCreated('payment'));
            event(new NewCreated('customerledger'));

            return response()->json([
                'success' => true,
                'message' => 'Payment recorded successfully.',
                'data' => [
                    'payment_no' => $pyNo
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(), // Optional: for debugging only
            ], 500);
        }
    }
}
