<?php

namespace App\Http\Controllers\MasterfileControllers;

use App\Events\NewCreated;
use App\Http\Controllers\Controller;
use App\Models\MasterfileModels\Permission;
use App\Models\MasterfileModels\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::when($request->search, function ($query) use ($request) {
            $query
                ->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('username', 'like', '%' . $request->search . '%');
        })->paginate(10)->withQueryString();

        $permissions = [];
        foreach ($users as $user) {
            $permissions[$user->id] = Permission::where('user_id', $user->id)->get()->keyBy('role_id')->toArray();
        }

        return Inertia::render('Users', [
            'users' => $users,
            'permissions' => $permissions,
            'searchTerm' => $request->search,
            'broadcastChannel' => 'users',
        ]);
    }

    private function assignPermissions($user, $role)
    {
        $rolePermissions = [
            'Admin' => [
                '0101-CUST',
                '0102-USER',
                '0103-CHKR',
                '0104-ITEM',
                '0104-ITMPCK',
                '0105-ADJRS',
                '0106-CAB',
                '0107-CIT',
                '0108-PCKT',
                '0109-SAMNT',
                '0201-CIT',
                '0202-ADT',
                '0203-PAYT',
                '0204-BGBLT',
                '0301-GNRPRT',
                '0302-CUSLED',
                '0401-CHKCLR',
                '0402-WHTCLR',
                '0403-CNCLPY',
                '0404-EXPRTGL',
                'NOTIFICATIONS',
                'MANAGERKEY'
            ],
            'Invoicing' => ['0201-CIT', '0202-ADT', '0301-GNRPRT', '0302-CUSLED'],
            'Accounting' => ['0203-PAYT', '0401-CHKCLR', '0402-WHTCLR', '0301-GNRPRT', '0302-CUSLED', '0204-BGBLT'],
            'Bookkeeper' => ['0301-GNRPRT', '0404-EXPRTGL'],
            'IAD' => ['0301-GNRPRT'],
        ];

        $roleActions = [
            '0101-CUST' => ['can_view', 'can_update'],
            '0102-USER' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0103-CHKR' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0104-ITEM' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0104-ITMPCK' => ['can_view', 'can_update'],
            '0105-ADJRS' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0106-CAB' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0107-CIT' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0108-PCKT' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0109-SAMNT' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0201-CIT' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
            '0202-ADT' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
            '0203-PAYT' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
            '0204-BGBLT' => ['can_view', 'can_insert'],
            '0301-GNRPRT' => ['can_view'],
            '0302-CUSLED' => ['can_view'],
            '0401-CHKCLR' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
            '0402-WHTCLR' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
            '0403-CNCLPY' => ['can_view', 'can_insert'],
            '0404-EXPRTGL' => ['can_view', 'can_update'],
            'NOTIFICATIONS' => ['can_insert'],
            'MANAGERKEY' => ['can_insert'],
        ];

        $roleIds = $rolePermissions[$role] ?? [];

        foreach ($roleIds as $roleId) {
            $actions = $roleActions[$roleId] ?? [];

            $permissionData = [
                'user_id' => $user->id,
                'role_id' => $roleId,
                'can_view' => in_array('can_view', $actions),
                'can_insert' => in_array('can_insert', $actions),
                'can_update' => in_array('can_update', $actions),
                'can_delete' => in_array('can_delete', $actions),
                'can_print' => in_array('can_print', $actions),
                'can_tag' => in_array('can_tag', $actions),
                'can_reprint' => in_array('can_reprint', $actions),
            ];

            Permission::create($permissionData);
        }
    }


    public function addUser(Request $request)
    {
        $fields = $request->validate([
            'employee_id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => 'required|in:Admin,Invoicing,Accounting,Bookkeeper,IAD',
            'status' => ['required', 'in:Active,Not Active'],
        ]);

        $fields['created_by'] =  $request->user()->name;
        $fields['password'] = bcrypt($fields['password']);
        $fields['bu_assign'] = session('bu_id');

        $user = User::create($fields);

        DB::connection('mysql')->table('users')->insert([
            'employee_id' => $fields['employee_id'],
            'name' => $fields['name'],
            'username' => $fields['username'],
            'password' => $fields['password'],
            'role' => $fields['role'],
            'status' => $fields['status'],
            'bu_assign' => $fields['bu_assign'],
            'created_by' => $fields['created_by'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assignPermissions($user, $fields['role']);

        $rolePermissions = [
            'Admin' => [
                '0101-CUST',
                '0102-USER',
                '0103-CHKR',
                '0104-ITEM',
                '0104-ITMPCK',
                '0105-ADJRS',
                '0106-CAB',
                '0107-CIT',
                '0108-PCKT',
                '0109-SAMNT',
                '0201-CIT',
                '0202-ADT',
                '0203-PAYT',
                '0204-BGBLT',
                '0301-GNRPRT',
                '0302-CUSLED',
                '0401-CHKCLR',
                '0402-WHTCLR',
                '0403-CNCLPY',
                '0404-EXPRTGL',
                'NOTIFICATIONS',
                'MANAGERKEY'
            ],
            'Invoicing' => ['0201-CIT', '0202-ADT', '0301-GNRPRT', '0302-CUSLED'],
            'Accounting' => ['0203-PAYT', '0401-CHKCLR', '0402-WHTCLR', '0301-GNRPRT', '0302-CUSLED', '0204-BGBLT'],
            'Bookkeeper' => ['0301-GNRPRT', '0404-EXPRTGL'],
            'IAD' => ['0301-GNRPRT'],
        ];
        $roleActions = [
            '0101-CUST' => ['can_view', 'can_update'],
            '0102-USER' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0103-CHKR' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0104-ITEM' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0104-ITMPCK' => ['can_view', 'can_update'],
            '0105-ADJRS' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0106-CAB' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0107-CIT' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0108-PCKT' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0109-SAMNT' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
            '0201-CIT' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
            '0202-ADT' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
            '0203-PAYT' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
            '0204-BGBLT' => ['can_view', 'can_insert'],
            '0301-GNRPRT' => ['can_view'],
            '0302-CUSLED' => ['can_view'],
            '0401-CHKCLR' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
            '0402-WHTCLR' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
            '0403-CNCLPY' => ['can_view', 'can_insert'],
            '0404-EXPRTGL' => ['can_view', 'can_update'],
            'NOTIFICATIONS' => ['can_insert'],
            'MANAGERKEY' => ['can_insert'],
        ];

        $roleIds = $rolePermissions[$fields['role']] ?? [];

        foreach ($roleIds as $roleId) {
            $actions = $roleActions[$roleId] ?? [];

            if ($fields['role'] === 'Accounting' && $roleId === '0204-BGBLT') {
                $actions = ['can_view'];
            }

            if ($fields['role'] === 'Bookkeeper' && $roleId === '0404-EXPRTGL') {
                $actions = ['can_view'];
            }

            $permissionData = [
                'user_id' => $user->id,
                'role_id' => $roleId,
                'can_view' => in_array('can_view', $actions),
                'can_insert' => in_array('can_insert', $actions),
                'can_update' => in_array('can_update', $actions),
                'can_delete' => in_array('can_delete', $actions),
                'can_print' => in_array('can_print', $actions),
                'can_tag' => in_array('can_tag', $actions),
                'can_reprint' => in_array('can_reprint', $actions),
            ];

            Permission::create($permissionData);
        }

        event(new NewCreated('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:Admin,Invoicing,Accounting,Bookkeeper,IAD',
            'status' => 'required|in:Active,Not Active',
        ]);

        $user = User::findOrFail($id);

        if ($request->filled('password')) {
            $validatedData['password'] = $request->password;
        } else {
            unset($validatedData['password']);
        }

        if ($user->username !== $request->username) {
            $validatedData['username'] = $request->username;
        }

        $currentRole = $user->role;

        $validatedData['created_by'] =  $request->user()->name;
        $user->update($validatedData);

        if ($currentRole !== $validatedData['role']) {
            $rolePermissions = [
                'Admin' => [
                    '0101-CUST',
                    '0102-USER',
                    '0103-CHKR',
                    '0104-ITEM',
                    '0104-ITMPCK',
                    '0105-ADJRS',
                    '0106-CAB',
                    '0107-CIT',
                    '0108-PCKT',
                    '0109-SAMNT',
                    '0201-CIT',
                    '0202-ADT',
                    '0203-PAYT',
                    '0204-BGBLT',
                    '0301-GNRPRT',
                    '0302-CUSLED',
                    '0401-CHKCLR',
                    '0402-WHTCLR',
                    '0403-CNCLPY',
                    '0404-EXPRTGL',
                    'NOTIFICATIONS',
                    'MANAGERKEY'
                ],
                'Invoicing' => ['0201-CIT', '0202-ADT', '0301-GNRPRT', '0302-CUSLED'],
                'Accounting' => ['0203-PAYT', '0401-CHKCLR', '0402-WHTCLR', '0301-GNRPRT', '0302-CUSLED', '0204-BGBLT'],
                'Bookkeeper' => ['0301-GNRPRT', '0404-EXPRTGL'],
                'IAD' => ['0301-GNRPRT'],
            ];
            $roleActions = [
                '0101-CUST' => ['can_view', 'can_update'],
                '0102-USER' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
                '0103-CHKR' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
                '0104-ITEM' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
                '0104-ITMPCK' => ['can_view', 'can_update'],
                '0105-ADJRS' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
                '0106-CAB' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
                '0107-CIT' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
                '0108-PCKT' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
                '0109-SAMNT' => ['can_view', 'can_insert', 'can_update', 'can_delete'],
                '0201-CIT' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
                '0202-ADT' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
                '0203-PAYT' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
                '0204-BGBLT' => ['can_view', 'can_insert'],
                '0301-GNRPRT' => ['can_view'],
                '0302-CUSLED' => ['can_view'],
                '0401-CHKCLR' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
                '0402-WHTCLR' => ['can_view', 'can_insert', 'can_print', 'can_reprint'],
                '0403-CNCLPY' => ['can_view', 'can_insert'],
                '0404-EXPRTGL' => ['can_view', 'can_update'],
                'NOTIFICATIONS' => ['can_insert'],
                'MANAGERKEY' => ['can_insert'],
            ];

            $roleIds = $rolePermissions[$validatedData['role']] ?? [];

            Permission::where('user_id', $user->id)
                ->whereNotIn('role_id', $roleIds)
                ->delete();

            foreach ($roleIds as $roleId) {
                $actions = $roleActions[$roleId] ?? [];

                if ($validatedData['role'] === 'Accounting' && $roleId === '0204-BGBLT') {
                    $actions = ['can_view'];
                }
                if ($validatedData['role'] === 'Bookkeeper' && $roleId === '0404-EXPRTGL') {
                    $actions = ['can_view'];
                }

                $permissionData = [
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'can_view' => in_array('can_view', $actions),
                    'can_insert' => in_array('can_insert', $actions),
                    'can_update' => in_array('can_update', $actions),
                    'can_delete' => in_array('can_delete', $actions),
                    'can_print' => in_array('can_print', $actions),
                    'can_tag' => in_array('can_tag', $actions),
                    'can_reprint' => in_array('can_reprint', $actions),
                ];

                Permission::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'role_id' => $roleId,
                    ],
                    $permissionData
                );
            }
        }

        event(new NewCreated('user'));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        event(new NewCreated('user'));
    }


    public function assignRolePermissions(Request $request, $userId)
    {
        // Find the user
        $user = User::findOrFail($userId);

        // Loop through roles and permissions from the request
        foreach ($request->roles as $roleId => $permissions) {
            $existingPermission = Permission::where('user_id', $userId)
                ->where('role_id', $roleId)
                ->first();

            $data = [
                'can_view' => $permissions['can_view'] ?? false,
                'can_insert' => $permissions['can_insert'] ?? false,
                'can_update' => $permissions['can_update'] ?? false,
                'can_delete' => $permissions['can_delete'] ?? false,
                'can_print' => $permissions['can_print'] ?? false,
                'can_tag' => $permissions['can_tag'] ?? false,
                'can_reprint' => $permissions['can_reprint'] ?? false,
            ];

            if ($existingPermission) {
                $existingPermission->update($data);
            } else {
                Permission::create(array_merge([
                    'user_id' => $userId,
                    'role_id' => $roleId,
                ], $data));
            }
        }
        event(new NewCreated('user'));
    }

    public function serveImageUserAdd(Request $request, $name)
    {

        try {
            // Call HRMS API to get employee photo path
            $apiUrl = "http://172.16.161.34/api/farms/filter/employee/name?q=" . urlencode($name);
            $apiResponse = Http::get($apiUrl)->json();

            $employeeData = $apiResponse['data']['employee'][0] ?? null;

            if ($employeeData && !empty($employeeData['employee_photo'])) {
                // Clean the path
                $photoPath = preg_replace('/^(\.\.\/)+/', '', $employeeData['employee_photo']);

                // Full image URL
                $imageUrl = "http://172.16.161.34:8080/hrms/" . ltrim($photoPath, '/');

                // Fetch image content
                $response = Http::get($imageUrl);

                if ($response->successful()) {
                    return response($response->body(), 200)
                        ->header('Content-Type', $response->header('Content-Type'));
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch HRMS image: ' . $e->getMessage());
        }

        abort(404, 'Image not found');
    }
}
