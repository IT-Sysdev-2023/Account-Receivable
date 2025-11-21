<?php

namespace App\Http\Controllers;

use App\Models\BusinessUnit;
use App\Models\MasterfileModels\User;
use App\Services\BusinessUnitService;
use App\Services\GlobalApiServices;
use App\Services\SyncAccCodeService;
use App\Services\SyncCustomerService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request, BusinessUnitService $buService, SyncCustomerService $syncService, SyncAccCodeService $syncAccCodeService, GlobalApiServices $globalApi)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // 1 check if the user is found in the main database 
        $mainUser = User::where('username', $request->username)->first();

        if (!$mainUser) {
            return back()->withErrors(['username' => "$request->username has no setup in the main database"]);
        }

        $buId = $mainUser->bu_assign;

        // 2. CHECK IF USER EXISTS IN BU DB (NO SESSION YET)
        $buUser = $buService->checkUserInBusinessUnit($buId, $request->username, $request->password);

        if (!$buUser['success']) {
            return back()->withErrors(['username' => $buUser['message']])->onlyInput('username');
        }

        // Safe to set session & connection
        $result = $buService->setBusinessUnitSessionAndConnection($buId);


        // 3. NOW SAFE → SET SESSION AND CONFIG
        $result = $buService->setBusinessUnitSessionAndConnection($buId);

        if (!$result['success']) {
            return back()->withErrors(['username' => $result['message']]);
        }

    
        try {
            // 7. Authenticate using BUSINESS UNIT database
            $user = User::on('business_unit')
                ->where('username', $request->username)
                ->first();

            if (!$user || !password_verify($request->password, $user->password)) {
                return back()->withErrors(['username' => 'Invalid Username or Password'])
                    ->onlyInput('username');
            }

            Auth::login($user);

            // 8. Validate employee status from HR API
            $employeeId = $user->employee_id;
            $response = Http::get("http://172.16.161.34/api/hrms/get/employee/status", [
                'q' => $employeeId
            ]);

            if ($response->failed()) {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'API request failed: ' . $response->status(),
                ])->onlyInput('username');
            }

            $status = $response->json()['employee'][0]['employee_status'] ?? null;

            if ($status !== 'Active') {
                Auth::logout();
                return back()->withErrors([
                    'username' => 'Your account is inactive. Please contact admin.',
                ])->onlyInput('username');
            }

            $request->session()->regenerate();

            // 9. Optional: Sync
            $synced = $syncService->sync($globalApi);
            $syncedAccCode = $syncAccCodeService->sync($globalApi);

            if (!$synced && !$syncedAccCode) {
                session()->flash('warning', 'Login successful, but sync failed.');
            } elseif ($synced && $syncedAccCode) {
                session()->flash('successful', 'Login and sync successfully');
            }

            return redirect('/dashboard');
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            Auth::logout();
            return back()->withErrors([
                'username' => 'Unexpected error occurred. Try again later.',
            ])->onlyInput('username');
        }
    }


    public function logout(Request $request)
    {
        Auth::logout();
        session()->forget(['database', 'host', 'port', 'username', 'password', 'business_unit', 'business_unit_code']);

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing');
    }

    // 4. Authenticate in BU DB
    // Auth::login($buUser);


    // This is the old for selected database manually 
    // public function login(Request $request, SyncCustomerService $syncService, SyncAccCodeService $syncAccCodeService, GlobalApiServices $globalApi)
    // {
    //     $request->validate(
    //         [
    //             'username' => ['required'],
    //             'password' => ['required'],
    //         ],
    //         [
    //             'username.required' => 'Username Required',
    //             'password.required' => 'Password Required',
    //         ]
    //     );

    //     // Get the selected business unit DB info from session
    //     $dbName = session('database');
    //     $dbHost = session('host');
    //     $dbPort = session('port');
    //     $dbUser = session('username');
    //     $dbPass = session('password');
    //     $dbDashboardPath = session('dashboard_path');

    //     if (!$dbName) {
    //         return back()->withErrors(['username' => "No business unit selected or the selected business unit has no configuration yet"])->onlyInput('username');
    //     }

    //     // Dynamically configure business unit DB connection
    //     Config::set('database.connections.business_unit', [
    //         'driver' => 'mysql',
    //         'host' => $dbHost,
    //         'port' => $dbPort,
    //         'database' => $dbName,
    //         'username' => $dbUser,
    //         'password' => $dbPass,
    //         'dashboard_path' => $dbDashboardPath,
    //         'charset' => 'utf8mb4',
    //         'collation' => 'utf8mb4_unicode_ci',
    //         'prefix' => '',
    //         'strict' => false,
    //         'engine' => null,
    //     ]);

    //     config(['auth.providers.users.model' => User::class]);
    //     config(['database.default' => 'business_unit']);

}
