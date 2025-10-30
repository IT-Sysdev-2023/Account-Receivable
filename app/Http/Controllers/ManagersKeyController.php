<?php

namespace App\Http\Controllers;

use App\Models\MasterfileModels\Permission;
use App\Models\MasterfileModels\User;
use App\Models\TransactionModels\ManagerKeyEntries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ManagersKeyController extends Controller
{
    public function validateManagerKey(Request $request)
    {
        $validated = $request->validate([
            'managerskeycode' => ['required', 'string'],
        ]);
        // $request->validate([
        //     'username' => ['required', 'string'],
        //     'password' => ['required', 'string'],
        // ]);

        $user = User::where('managers_key_code', $validated['managerskeycode'])  // or 'username' if that's your field
            ->first();

        if ($user && $validated['managerskeycode'] === $user->managers_key_code) {
            // Check for SUPER role in permissions
            $hasSuperPermission = Permission::where('user_id', $user->id)
                ->where('role_id', 'MANAGERKEY')
                ->where('can_insert', 1)
                ->exists();

            if ($hasSuperPermission) {
                // Log the user who entered the manager key successfully
                ManagerKeyEntries::create([
                    'user_id' => $user->id,  // Store the user ID
                    'user_name' => $user->name,  // Store the user ID
                    'entered_at' => now(),   // Store the current timestamp
                ]);
                $user->update([
                    'managers_key_code' => null
                ]);
                return response()->json([
                    'authorized' => true,
                    'user_name' => $user->name,
                    'message' => 'Access granted.'
                ]);
            } else {
                return response()->json([
                    'authorized' => false,
                    'message' => 'User does not have full SUPER permissions.'
                ]);
            }
        }


        return response()->json([
            'authorized' => false,
            'message' => 'Invalid or Expired Managers Key Code'
        ]);
    }

    public function generateManagersKeyCode(Request $request, $id)
    {
        $validated = $request->validate(
            [
                'ungeneratedCode' => 'required|string|max:8',
            ],
        );

        if (User::where('managers_key_code', $validated['ungeneratedCode'])->exists()) {
            throw ValidationException::withMessages([
                'general' => 'Error Generating Please Try Again',
            ]);
        }

        $user = User::findOrFail($id);

        // Update the user's record
        $user->update([
            'managers_key_code' => $validated['ungeneratedCode']
        ]);
    }
}
