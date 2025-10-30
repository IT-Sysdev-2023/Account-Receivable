<?php

use App\Http\Controllers\BusinessUnitController;
use App\Http\Controllers\MasterfileControllers\AdjustmentReasonSetupController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportToGLController;
use App\Http\Controllers\GlobalApiController;
use App\Http\Controllers\ManagersKeyController;
use App\Http\Controllers\MasterfileControllers\CashInBankController;
use App\Http\Controllers\MasterfileControllers\ChargeInvoiceTypeController;
use App\Http\Controllers\MasterfileControllers\CheckerController;
use App\Http\Controllers\MasterfileControllers\CustomerController;
use App\Http\Controllers\MasterfileControllers\ItemController;
use App\Http\Controllers\MasterfileControllers\ItemPackingController;
use App\Http\Controllers\MasterfileControllers\ItemWholeSaleController;
use App\Http\Controllers\MasterfileControllers\PackingTypeController;
use App\Http\Controllers\MasterfileControllers\ShortageAmountController;
use App\Http\Controllers\MasterfileControllers\UserController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\PDFGeneratorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportControllers\CustomerLedgerController;
use App\Http\Controllers\ReportControllers\ReportPDFGeneratorController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\TransactionControllers\AdjustmentControllers;
use App\Http\Controllers\TransactionControllers\BeginningBalanceController;
use App\Http\Controllers\TransactionControllers\InvoiceController;
use App\Http\Controllers\TransactionControllers\InvoiceItemController;
use App\Http\Controllers\TransactionControllers\PaymentController;
use App\Http\Controllers\UtilityControllers\CancelPaymentController;
use App\Http\Controllers\UtilityControllers\CancelPaymentItemsController;
use App\Http\Controllers\UtilityControllers\CheckClearedController;
use App\Http\Controllers\UtilityControllers\CheckClearedItemsController;
use App\Http\Controllers\UtilityControllers\WHTClearedController;
use App\Http\Controllers\UtilityControllers\WHTClearedItemsController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\Finder\Glob;

// ROOT REDIRECT - Redirect to login if no session, or to appropriate dashboard if session exists
Route::get('/', function () {
    if (session('dashboard_path')) {
        return redirect('/' . session('dashboard_path'));
    }
    return redirect('/login');
});


// BUSINESS UNIT SELECTION ROUTES (Available without auth)
Route::get('fetch-business-units', [BusinessUnitController::class, 'businessUnits'])->name('businessUnits');
Route::post('selected-business-unit/{id}', [BusinessUnitController::class, 'selectedBu'])->name('selectedBu');
Route::get('current-database', [BusinessUnitController::class, 'currentDatabase'])->name('currentDatabase');



// GUEST ROUTES
Route::middleware('guest')->group(function () {
    Route::get('/landing', function () {
        return Inertia::render('Landing');
    })->name('landing');

    Route::get('/login', function () {
        return Inertia::render('Auth/Login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'login'])->name('authLogin');
});



Route::prefix('')->group(
    function () {
        Broadcast::routes(['middleware' => ['web', 'auth']]);

        Route::middleware('auth')->group(function () {
            // Theme
            Route::post('/preferences/theme', [ThemeController::class, 'setTheme'])->name('setTheme');
            //Profile page
            Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
            Route::put('/profile-username-update/{id}', [ProfileController::class, 'updateUsername'])->name('updateUsername');
            Route::put('/profile-password-update/{id}', [ProfileController::class, 'updatePassword'])->name('updatePassword');
            Route::get('/profile-photo', [ProfileController::class, 'serveImage'])->name('profilePhoto');
            //dashboard page
            Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
            Route::get('/invoice-chart', [DashboardController::class, 'getInvoiceChartData'])->name('getInvoiceChartData');
            Route::get('/invoice-pie', [DashboardController::class, 'getInvoicePieData'])->name('getInvoicePieData');
            Route::get('/getCustomerAccountsSummary', [DashboardController::class, 'getCustomerAccountsSummary'])->name('getCustomerAccountsSummary');

            //ROUTES FOR MASTERFILE**********************************************************************************************************************
            //Customers page
            Route::get('/customers', [CustomerController::class, 'index'])->middleware('check.permission:0101-CUST,view')->name('customer');
            Route::post('/customers/sync', [CustomerController::class, 'syncCustomers'])->name('syncCustomers');
            Route::get('/customer-list', [CustomerController::class, 'getCustomerList'])->name('getCustomerList');
            Route::get('/customers-list-all', [CustomerController::class, 'getAllCustomerList'])->name('customers.getAll');
            //Users page
            Route::get('/users', [UserController::class, 'index'])->middleware('check.permission:0102-USER,view')->name('user');
            Route::post('/addUser', [UserController::class, 'addUser'])->name('addUser');
            Route::put('/updateUser/{id}', [UserController::class, 'updateUser'])->name('updateUser');
            Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('deleteUser');
            Route::post('/users/{user}/assign-role-permissions', [UserController::class, 'assignRolePermissions'])->name('assign.role.permissions');
            Route::get('/users-photo/${name}', [UserController::class, 'serveImageUserAdd'])->name('userPhoto');
            //Checkers page
            Route::get('/checkers', [CheckerController::class, 'index'])->middleware('check.permission:0103-CHKR,view')->name('checker');
            Route::post('/addChecker', [CheckerController::class, 'addChecker'])->name('addChecker'); //Add Checker
            Route::post('/updateChecker/{id}', [CheckerController::class, 'updateChecker'])->name('updateChecker'); //Update Checker
            Route::delete('/deleteChecker/{id}', [CheckerController::class, 'destroy'])->name('deleteChecker'); //delete Checker

            //Items Page
            Route::get('/item', [ItemController::class, 'index'])->middleware('check.permission:0104-ITEM,view')->name('item');
            Route::post('/items/sync', [ItemController::class, 'syncItem'])->name('syncItem');
            Route::post('/addItem', [ItemController::class, 'addItem'])->name('addItem'); //Add Item
            Route::post('/updateItem/{id}', [ItemController::class, 'updateItem'])->name('updateItem'); //Update Item
            Route::delete('/deleteItem/{id}', [ItemController::class, 'destroy'])->name('deleteItem'); //delete Item
            Route::get('/ci-types', [ItemController::class, 'getChargeInvoiceTypes'])->name('ci-types');
            Route::get('/item-list', [ItemController::class, 'getItemList'])->name('getItemList');
            Route::get('/packing-list', [ItemController::class, 'getPackingList'])->name('getPackingList');
            Route::get('/allitemlist', [ItemController::class, 'getAllItemList'])->name('getAllItemList');
            Route::get('/item-packings/{item}', [ItemPackingController::class, 'show'])->name('showItemPackings');
            Route::post('/item-packings', [ItemPackingController::class, 'store'])->name('item-packings.store');
            Route::get('/packing-types', [ItemPackingController::class, 'getPackingTypes'])->name('getPackingTypes');
            //Adjustment Reason Setup Page
            Route::get('/adjustmentreasonsetup', [AdjustmentReasonSetupController::class, 'index'])->middleware('check.permission:0105-ADJRS,view')->name('adjustmentreasonsetup');
            Route::post('/addAdjustmentReasonSetup', [AdjustmentReasonSetupController::class, 'addAdjustmentReasonSetup'])->name('addAdjustmentReasonSetup'); //Add Adjustment Reason Setup
            Route::put('/editAdjustmentReasonSetup/{id}', [AdjustmentReasonSetupController::class, 'editAdjustmentReasonSetup'])->name('editAdjustmentReasonSetup'); //Edit Adjustment Reason Setup
            Route::delete('/deleteAdjustmentReasonSetup/{id}', [AdjustmentReasonSetupController::class, 'destroy'])->name('deleteAdjustmentReasonSetup'); //delete Adjsutment
            //Cash in Bank Page
            Route::get('/cashinbank', [CashInBankController::class, 'index'])->middleware('check.permission:0106-CAB,view')->name('cashinbank');
            Route::post('/addCashInBank', [CashInBankController::class, 'addCashInBank'])->name('addCashInBank');
            Route::put('/editCashInBank/{id}', [CashInBankController::class, 'editCashInBank'])->name('editCashInBank');
            Route::delete('/deleteCashInBank/{id}', [CashInBankController::class, 'destroy'])->name('deleteCashInBank');
            Route::get('/getlatestbankcode', [CashInBankController::class, 'latest'])->name('getlatestbankcode');
            //Charge Invoice Type
            Route::get('/chargeinvoicetype', [ChargeInvoiceTypeController::class, 'index'])->middleware('check.permission:0107-CIT,view')->name('chargeinvoicetype');
            Route::post('/addChargeInvoiceType', [ChargeInvoiceTypeController::class, 'addChargeInvoiceType'])->name('addChargeInvoiceType');
            Route::put('/editChargeInvoiceType/{id}', [ChargeInvoiceTypeController::class, 'editChargeInvoiceType'])->name('editChargeInvoiceType');
            Route::delete('/deleteChargeInvoiceType/{id}', [ChargeInvoiceTypeController::class, 'destroy'])->name('deleteChargeInvoiceType');
            Route::get('/getlatestChargeInvoiceType', [ChargeInvoiceTypeController::class, 'latest'])->name('getlatestChargeInvoiceType');
            //Packing Type
            Route::get('/packingtype', [PackingTypeController::class, 'index'])->middleware('check.permission:0108-PCKT,view')->name('packingtype');
            Route::post('/addPackingType', [PackingTypeController::class, 'addPackingType'])->name('addPackingType');
            Route::put('/editPackingType/{id}', [PackingTypeController::class, 'editPackingType'])->name('editPackingType');
            Route::delete('/deletePackingType/{id}', [PackingTypeController::class, 'destroy'])->name('deletePackingType');
            Route::get('/getlatestPackingType', [PackingTypeController::class, 'latest'])->name('getlatestPackingType');
            //Shortage Amount
            Route::get('/shortageamount', [ShortageAmountController::class, 'index'])->middleware('check.permission:0109-SAMNT,view')->name('shortageamount');
            Route::post('/addShortageAmount', [ShortageAmountController::class, 'addShortageAmount'])->name('addShortageAmount');
            Route::put('/editShortageAmount/{id}', [ShortageAmountController::class, 'editShortageAmount'])->name('editShortageAmount');
            Route::delete('/deleteShortageAmount/{id}', [ShortageAmountController::class, 'destroy'])->name('deleteShortageAmount');

            //ROUTES FOR TRANSACTION**********************************************************************************************************************
            //Invoice Page
            Route::get('/invoice', [InvoiceController::class, 'index'])->middleware('check.permission:0201-CIT,view')->name('invoice');
            Route::post('/addInvoice', [InvoiceController::class, 'addInvoice'])->name('addInvoice');
            Route::put('/editInvoice/{id}', [InvoiceController::class, 'editInvoice'])->name('editInvoice');
            Route::delete('/deleteInvoice/{id}', [InvoiceController::class, 'destroy'])->name('deleteInvoice');
            Route::get('/getlatestinvoiceno', [InvoiceController::class, 'latest'])->name('getlatestinvoiceno');
            Route::get('/invoice-items/{invoice_no}', [InvoiceItemController::class, 'getByInvoiceNo'])->name('getInvoiceItems');
            Route::get('/invoice-list', [InvoiceController::class, 'getInvoiceList'])->name('getInvoiceList');
            Route::get('/invoice-list-payment', [InvoiceController::class, 'getInvoiceListForPayment'])->name('getInvoiceListForPayment');
            Route::get('/invoice-cleared-list', [InvoiceController::class, 'getInvoiceClearedList'])->name('getInvoiceClearedList');
            Route::get('/invoice-customerBegBal-list', [InvoiceController::class, 'getCustomerBegBalList'])->name('getCustomerBegBalList');
            Route::post('/validateInvoiceCashPayment', [InvoiceController::class, 'validateInvoiceCashPayment'])->name('validateInvoiceCashPayment');
            Route::get('/invoice-latest-invoiceNumber', function () { //GETTING INVOICE NUMBER FOR PDF PRINT PURPOSES (NOT REPRINTING!)
                $invNo = session()->pull('invoice_number');
                return response()->json(['invoice_number' => $invNo]);
            })->name('invoice.latest.invoiceNumber');
            //Adjustment Page
            Route::get('/adjustment', [AdjustmentControllers::class, 'index'])->middleware('check.permission:0202-ADT,view')->name('adjustment');
            Route::post('/addAdjustment', [AdjustmentControllers::class, 'addAdjustment'])->name('addAdjustment');
            Route::put('/editAdjustment/{id}', [AdjustmentControllers::class, 'editAdjustment'])->name('editAdjustment');
            Route::delete('/deleteAdjustment/{id}', [AdjustmentControllers::class, 'destroy'])->name('deleteAdjustment');
            Route::get('/getlatestadjustmentno', [AdjustmentControllers::class, 'latest'])->name('getlatestadjustmentno');
            Route::get('/adjustment-reason-setup', [AdjustmentControllers::class, 'getAdjustmentReasonSetup'])->name('getAdjustmentReasonSetup');
            Route::get('/adjustment-latest-adjustmentNumber', function () { //GETTING ADJUSTMENT NUMBER FOR PDF PRINT PURPOSES (NOT REPRINTING!)
                $adjNo = session()->pull('adjustment_number');
                return response()->json(['adjustment_number' => $adjNo]);
            })->name('adjustment.latest.adjustmentNumber');
            //Payment Page
            Route::get('/payment', [PaymentController::class, 'index'])->middleware('check.permission:0203-PAYT,view')->name('payment');
            Route::post('/addPayment', [PaymentController::class, 'addPayment'])->name('addPayment');
            Route::put('/editPayment/{id}', [PaymentController::class, 'editPayment'])->name('editPayment');
            Route::delete('/deletePayment/{id}', [PaymentController::class, 'destroy'])->name('deletePayment');
            Route::get('/getlatestpaymentnoauth', [PaymentController::class, 'latestPaymentNO'])->name('getlatestpaymentno');
            Route::get('/cashinbank-list', [CashInBankController::class, 'getCashInBankList'])->name('getCashInBankList');
            Route::get('/payment-latest-paymentNumber', function () { //GETTING PAYMENT NUMBER FOR PDF PRINT PURPOSES (NOT REPRINTING!)
                $pyNo = session()->pull('payment_number'); // pulls and removes in one go
                return response()->json(['payment_number' => $pyNo]);
            })->name('payment.latest.paymentNumber');
            //Beginning Balance Page
            Route::get('/beginningbalance', [BeginningBalanceController::class, 'index'])->middleware('check.permission:0204-BGBLT,view')->name('beginningbalance');
            Route::post('/addBeginningBalance', [BeginningBalanceController::class, 'addBeginningBalance'])->name('addBeginningBalance');
            Route::post('/addMultipleBeginningBalance', [BeginningBalanceController::class, 'addMultipleBeginningBalance'])->name('addMultipleBeginningBalance');
            Route::put('/editBeginningBalance/{id}', [BeginningBalanceController::class, 'editBeginningBalance'])->name('editBeginningBalance');
            Route::delete('/deleteBeginningBalance/{id}', [BeginningBalanceController::class, 'destroy'])->name('deleteBeginningBalance');
            Route::get('/getlatestbeginningbalanceno', [BeginningBalanceController::class, 'latest'])->name('getlatestbeginningbalanceno');

            //ROUTES FOR REPORTS**********************************************************************************************************************
            //GenerateReport
            Route::get('/generatereport', function () {
                return Inertia::render('GenerateReport');
            })->middleware('check.permission:0301-GNRPRT,view')->name('generatereport');
            Route::post('/invoice-report', [ReportPDFGeneratorController::class, 'invoiceReport'])->name('invoiceReport');
            Route::post('/invoice-report-summary', [ReportPDFGeneratorController::class, 'invoiceReportSummary'])->name('invoiceReportSummary');
            Route::post('/adjustment-report', [ReportPDFGeneratorController::class, 'adjustmentReport'])->name('adjustmentReport');
            Route::post('/payment-report', [ReportPDFGeneratorController::class, 'paymentReport'])->name('paymentReport');
            Route::post('/pdcdc-report', [ReportPDFGeneratorController::class, 'pdcDcReport'])->name('pdcDcReport');
            Route::post('/customerArAging-report', [ReportPDFGeneratorController::class, 'customerArAging'])->name('customerArAging');
            Route::post('/begBal-report', [ReportPDFGeneratorController::class, 'begBalProoflist'])->name('begBalProoflist');
            Route::post('/arOutstandingBalanceAO-report', [ReportPDFGeneratorController::class, 'arOutstandingBalanceAO'])->name('arOutstandingBalanceAO');
            Route::post('/arOutstandingBalanceDR-report', [ReportPDFGeneratorController::class, 'arOutstandingBalanceDR'])->name('arOutstandingBalanceDR');
            Route::post('/salesPerItem-report', [ReportPDFGeneratorController::class, 'salesPerItem'])->name('salesPerItem');
            Route::post('/overageShortage-report', [ReportPDFGeneratorController::class, 'overageShortage'])->name('overageShortage');
            Route::post('/statementOfAccount-report', [ReportPDFGeneratorController::class, 'statementOfAccount'])->name('statementOfAccount');
            Route::post('/statementOfAccountSummary-report', [ReportPDFGeneratorController::class, 'statementOfAccountSummary'])->name('statementOfAccountSummary');
            Route::delete('/pdfdelete', [ReportPDFGeneratorController::class, 'delete'])->name('pdf.delete');
            //Customer Ledger
            Route::get('/customerledger', [CustomerLedgerController::class, 'index'])->middleware('check.permission:0302-CUSLED,view')->name('customerledger');
            Route::get('/payment-history', [CustomerLedgerController::class, 'getPaymentDetails'])->name('paymentHistory');

            //ROUTES FOR UTILITY**********************************************************************************************************************
            //CHECK CLEARING
            Route::get('/clearing', [CheckClearedController::class, 'index'])->middleware('check.permission:0401-CHKCLR,view')->name('clearing');
            Route::get('/getFloatingChecks', [CustomerLedgerController::class, 'getFloatingChecks'])->name('getFloatingChecks');
            Route::post('/check-clearing', [CheckClearedController::class, 'clearChecks'])->name('checkclearing');
            Route::get('/getlatestclearingno', [CheckClearedController::class, 'latest'])->name('getlatestclearingno');
            Route::get('/check-cleared-items/{clearing_no}', [CheckClearedItemsController::class, 'getByClearingNo'])->name('getCheckClearedItems');
            Route::get('/clearing-latest-clearingNumber', function () { //GETTING CLEARING NUMBER FOR PDF PRINT PURPOSES (NOT REPRINTING!)
                $clrNo = session()->pull('clearing_number');
                return response()->json(['clearing_number' => $clrNo]);
            })->name('clearing.latest.clearingNumber');
            //WHT CLEARING
            Route::get('/withholdingtaxclearing', [WHTClearedController::class, 'index'])->middleware('check.permission:0402-WHTCLR,view')->name('withholdingtaxclearing');
            Route::get('/getFloatingWht', [CustomerLedgerController::class, 'getFloatingWht'])->name('getFloatingWht');
            Route::post('/wht-clearing', [WHTClearedController::class, 'clearWht'])->name('whtclearing');
            Route::get('/getlatestwhtno', [WHTClearedController::class, 'latest'])->name('getlatestwhtno');
            Route::get('/wht-cleared-items/{wht_clearing_no}', [WHTClearedItemsController::class, 'getByWhtClearingNo'])->name('getWHTClearedItems');
            Route::get('/whtclearing-latest-whtclearingNumber', function () { //GETTING CLEARING NUMBER FOR PDF PRINT PURPOSES (NOT REPRINTING!)
                $whtclrNo = session()->pull('whtclearing_number');
                return response()->json(['whtclearing_number' => $whtclrNo]);
            })->name('whtclearing.latest.whtclearingNumber');
            //CANCEL PAYMENT
            Route::get('/cancelpayment', [CancelPaymentController::class, 'index'])->middleware('check.permission:0403-CNCLPY,view')->name('cancelpayment');
            Route::get('/getPaymentList', [CustomerLedgerController::class, 'getPaymentList'])->name('payment_list');
            Route::get('/getDocumentsPaidList', [CustomerLedgerController::class, 'getDocumentsPaidList'])->name('documentspaid_list');
            Route::post('/cancel-payment', [CancelPaymentController::class, 'cancelPaymentUsingDocumentNo'])->name('cancel-payment');
            Route::post('/cancel-payment-two', [CancelPaymentController::class, 'cancelPaymentUsingPaymentNo'])->name('cancel-payment-two');
            Route::get('/getlatestcancelpaymentno', [CancelPaymentController::class, 'latest'])->name('getlatestcancelpaymentno');
            Route::get('/cancel-payment-items/{cancellation_no}', [CancelPaymentItemsController::class, 'getByCancellationNo'])->name('getCancelledItems');

            Route::get('/exporttogl', function () {
                return Inertia::render('ExportToGL');
            })->middleware('check.permission:0301-GNRPRT,view')->name('exporttogl');

            //ROUTE FOR PDF GENERATOR***********************************************************************************************************************
            Route::post('/preview-invoice', [PDFGeneratorController::class, 'previewInvoice'])->name('preview-invoice');
            Route::post('/preview-cash-invoice', [PDFGeneratorController::class, 'previewCashInvoice'])->name('preview-cash-invoice');
            Route::post('/preview-adjustment', [PDFGeneratorController::class, 'previewAdjustment'])->name('preview-adjustment');
            Route::post('/preview-payment', [PDFGeneratorController::class, 'previewPayment'])->name('preview-payment');
            Route::post('/preview-check-cleared', [PDFGeneratorController::class, 'previewCheckCleared'])->name('previewCheckCleared');
            Route::post('/preview-wht-cleared', [PDFGeneratorController::class, 'previewWhtCleared'])->name('previewWhtCleared');
            //ROUTE FOR PDF MANAGERKEY**********************************************************************************************************************************************
            Route::post('/validate-manager-key', [ManagersKeyController::class, 'validateManagerKey'])->name('validateManagerKey');
            Route::put('/profile-generateManagersKeyCode/{id}', [ManagersKeyController::class, 'generateManagersKeyCode'])->name('generateManagersKeyCode');

            //Exporttogl
            Route::post('/export-to-gl', [ExportToGLController::class, 'export'])->name('generateTextFile');
            Route::get('/export-to-gl', [ExportToGLController::class, 'export'])
                ->middleware(['web']);
            Route::post('/untag-export', [ExportToGLController::class, 'untag'])->name('untagExport');

            //Notifications
            Route::get('/get-notifications-count', [NotificationsController::class, 'unreadNotifs'])->name('getNotificationsCount');
            Route::get('/get-notifications', [NotificationsController::class, 'index'])->name('getNotifications');
            Route::post('/create-notifications', [NotificationsController::class, 'store'])->name('createNotifications');
            Route::post('/mark-read/{id}', [NotificationsController::class, 'markAsRead'])->name('markAsRead');
            Route::post('/mark-all-read', [NotificationsController::class, 'markAllAsRead'])->name('markAllAsRead');
            Route::delete('/clear-all', [NotificationsController::class, 'clearAll'])->name('clearAll');

            //route for Item Wholesales
            Route::get('/item-wholesales/{item}', [ItemWholeSaleController::class, 'show'])->name('showItemWholeSales');
            Route::post('/item-wholesales', [ItemWholeSaleController::class, 'store'])->name('item-wholesales.store');

            //Route for aboutUs page
            Route::get('/aboutus', function () {
                return Inertia::render('AboutUs');
            })->name('aboutus');

            Route::get('/userguide', function () {
                return Inertia::render('UserGuide');
            })->name('userguide');

            //Route for download export textfile
            Route::get('/download/exports/{filename}', function ($filename) {
                $path = storage_path("app/private/exports/{$filename}");
                if (!file_exists($path)) {
                    Log::error("Download failed. File not found: {$path}");
                    abort(404);
                }
                return response()->download($path, $filename, [
                    'Content-Type' => 'text/plain',
                ]);
            })->name('exports.download');

            //messages
            // Get all users for messaging
            Route::get('/usersformessage', [MessageController::class, 'getUsers'])->name('messages.users');

            // Get conversation with a specific user
            Route::get('/conversation/{user}', [MessageController::class, 'getConversation'])->name('messages.conversation');

            // Send a new message
            Route::post('/send', [MessageController::class, 'sendMessage'])->name('messages.send');

            // Mark messages as read
            Route::post('/mark-message-read/{user}', [MessageController::class, 'markAsRead'])->name('messages.markAsRead');

            // Get unread message count
            Route::get('/unread-count', [MessageController::class, 'getUnreadCount'])->name('messages.unreadCount');

            // Delete a message
            Route::delete('/{message}', [MessageController::class, 'deleteMessage'])->name('messages.delete');

            // Get recent conversations
            Route::get('/recent', [MessageController::class, 'getRecentConversations'])->name('messages.recent');

            Route::post('/markuseroffline', [MessageController::class, 'markAsOffline'])->name('user.markOffline');

            Route::get('fetch-price-group', [GlobalApiController::class, 'fetchPriceGroup'])->name('fetchPriceGroup');
            Route::get('fetch-account-code-list', [GlobalApiController::class, 'accountCodeList'])->name('accountCodeList');
            Route::get('fetch-customer-code-list', [GlobalApiController::class, 'customerCodeList'])->name('customerCodeList');
        });
    }
);


// API ROUTES (External systems call these)
Route::prefix('api')
    ->middleware(['api', 'throttle:10,1'])
    ->group(function () {
        Route::get('/cash-in-bank', [CashInBankController::class, 'cashInBankListAPI']);
        Route::get('/getlatestpaymentno', [PaymentController::class, 'latest']);
        Route::post('/insertcustomerledger', [CustomerLedgerController::class, 'store']);
        Route::put('/updatecustomerledger', [CustomerLedgerController::class, 'update']);
        Route::post('/insertpayment', [PaymentController::class, 'storePaymentAPI']);
        Route::get('/get-customerledger-list', [CustomerLedgerController::class, 'getCustomerLedgerList']);
    });


// GLOBAL LOGOUT ROUTE (outside the dynamic prefix)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ERROR & FALLBACK ROUTES
Route::get('/session-expired', function () {
    return Inertia::render('SessionExpire');
})->name('session.expired');

// Route::fallback(function () {
//     return Inertia::render('PageNotFound');
// })->name('404');


// Route::middleware('api', 'throttle:10,1')->group(function () {
//     Route::get('/api/iceplant/cash-in-bank', [CashInBankController::class, 'cashInBankListAPI']); //API LINK
//     Route::get('/api/iceplant/getlatestpaymentno', [PaymentController::class, 'latest']);
//     Route::post('/api/iceplant/insertcustomerledger', [CustomerLedgerController::class, 'store']);
//     Route::put('/api/iceplant/updatecustomerledger', [CustomerLedgerController::class, 'update']);
//     Route::post('/api/iceplant/insertpayment', [PaymentController::class, 'storePaymentAPI']);
//     Route::get('/api/iceplant/get-customerledger-list', [CustomerLedgerController::class, 'getCustomerLedgerList']);
// });
