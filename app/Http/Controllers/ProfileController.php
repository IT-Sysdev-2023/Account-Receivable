<?php

namespace App\Http\Controllers;

use App\Models\MasterfileModels\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProfileController extends Controller
{

    public function profile()
    {
        $userData = $this->getUserProfile();

        return Inertia::render('Profile', [
            'user' => $userData['user'],
            'hrmsData' => $userData['hrmsData'],
        ]);
    }

    public function getUserProfile()
    {
        $user = Auth::user();

        if (!$user) {
            return [
                'user' => null,
                'hrmsData' => null,
            ];
        }

        // Get all user fields needed by the Vue component
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
        ];


        // Call external API to get HRMS data
        $apiResponse = null;

        if ($userData['name']) {
            try {
                $apiUrl = "http://172.16.161.34/api/farms/filter/employee/name?q=" . urlencode($userData['name']);
                $apiResponse = Http::get($apiUrl)->json();
            } catch (\Exception $e) {
                Log::error('Failed to fetch HRMS data: ' . $e->getMessage());
                $apiResponse = ['error' => 'Failed to fetch HRMS data'];
            }
        }

        return [
            'user' => $userData,
            'hrmsData' => $apiResponse,
        ];
    }

    public function serveImage(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        try {
            // Call HRMS API to get employee photo path
            $apiUrl = "http://172.16.161.34/api/farms/filter/employee/name?q=" . urlencode($user->name);
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

    public function updateUsername(Request $request, $id)
    {
        $validated = $request->validate(
            [
                'username' => 'required|string|max:255',
            ],
            [
                'username.required' => 'Username Required',
            ]
        );

        $user = User::findOrFail($id);

        // If the username is not changed, don't update it
        if ($user->username !== $request->username) {
            $validated['username'] = $request->username;
        }

        $user->update($validated);
    }

    public function updatePassword(Request $request, $id)
    {
        // Validate input
        $validated = $request->validate(
            [
                'current_password' => 'required|string|min:8',
                'password' => 'required|string|min:8|confirmed',
            ],
            [
                // Custom messages for current_password
                'current_password.required' => 'Current password is required.',
                'current_password.min' => 'Current password must be at least 8 characters.',

                // Custom messages for new password
                'password.required' => 'New password is required.',
                'password.min' => 'New password must be at least 8 characters.',
                'password.confirmed' => 'New password does not match the confirmation.',
            ]
        );


        // Find the user
        $user = User::findOrFail($id);

        // Check if current password matches
        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'general' => 'The current password is incorrect',
            ]);
        }

        // Update with new password
        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }
}
