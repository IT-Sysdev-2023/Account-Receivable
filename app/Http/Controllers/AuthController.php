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
    public function login(
        Request $request,
        SyncCustomerService $syncService,
        SyncAccCodeService $syncAccCodeService,
        GlobalApiServices $globalApi,
    ) {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'business_unit' => 'required'
        ]);

        try {
            $user = User::on('business_unit')
                ->where('username', $request->username)
                ->first();

            if (!$user || !password_verify($request->password, $user->password)) {
                return back()->withErrors(['username' => 'Invalid Username or Password'])
                    ->onlyInput('username');
            }

            Auth::login($user);
            // if (!Auth::attempt([
            //     'username' => $request->username,
            //     'password' => $request->password,
            // ])) {
            //     return back()->withErrors([
            //         'username' => 'Invalid Username or Password',
            //     ])->onlyInput('username');
            // }

            // $request->session()->regenerate();
            // $user = Auth::user();

            // This check if user is active in hrms 
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
                    'username' => 'Were unable to process your login because your HRMS account has been deactivated',
                ])->onlyInput('username');
            }

            // sync customer and acc code 
            $synced = $syncService->sync($globalApi);
            $syncedAccCode = $syncAccCodeService->sync($globalApi);

            if (!$synced && !$syncedAccCode) {
                session()->flash('warning', 'Login successful, but sync failed.');
            } elseif ($synced && $syncedAccCode) {
                session()->flash('successful', 'Login and sync successfully.');
            }

            // This setup is for authenticating users using the main db regardless of what bu db selected 
            // This set the session if the user successfully login 
            // $businessUnit = $businessUnits->getBusinessUnitConfig($request->business_unit);

            // if ($businessUnit) {
            //     session([
            //         'bu_id' => $businessUnit['bu_id'],
            //         'dashboard_path' => $businessUnit['dashboard_path'],
            //         'name' => $businessUnit['name'],
            //         'database' => $businessUnit['database'],
            //         'host' => $businessUnit['host'],
            //         'port' => $businessUnit['port'],
            //         'username' => $businessUnit['username'],
            //         'password' => $businessUnit['password'],
            //         'business_unit' => $businessUnit['business_unit'],
            //         'business_unit_code' => $businessUnit['business_unit_code'],
            //     ]);

            //     $tempConfig = [
            //         'driver'    => 'mysql',
            //         'host'      => $businessUnit['host'],
            //         'port'      => $businessUnit['port'],
            //         'database'  => $businessUnit['database'],
            //         'username'  => $businessUnit['username'],
            //         'password'  => $businessUnit['password'],
            //         'charset'   => 'utf8mb4',
            //         'collation' => 'utf8mb4_unicode_ci',
            //     ];

            //     Config::set('database.connections.business_unit', $tempConfig);
            //     DB::purge('business_unit');
            //     DB::reconnect('business_unit');
            // }

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
}
