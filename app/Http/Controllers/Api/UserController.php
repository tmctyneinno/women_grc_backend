<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Get authenticated user profile
     */
    public function profile(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return ApiResponse::error('Unauthorized', [], 401);
            }

            return ApiResponse::success([
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'job_title' => $user->job_title,
                    'company' => $user->company,
                    'profile_picture' => $user->profile_picture,
                    'linkedin_profile' => $user->linkedin_profile,
                    'timezone' => $user->timezone,
                    'status' => $user->status,
                    'is_verified' => $user->is_verified,
                    'email_verified_at' => $user->email_verified_at,
                ]
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to fetch profile', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        try {
            $user = Auth::guard('sanctum')->user();

            if (!$user) {
                return ApiResponse::error('Unauthorized', [], 401);
            }

            $validated = $request->validate([
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'job_title' => 'nullable|string|max:255',
                'company' => 'nullable|string|max:255',
                'timezone' => 'nullable|string|max:255',
                'linkedin_profile' => 'nullable|url',
            ]);

            $user->update($validated);

            return ApiResponse::success([
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number,
                    'job_title' => $user->job_title,
                    'company' => $user->company,
                    'profile_picture' => $user->profile_picture,
                    'linkedin_profile' => $user->linkedin_profile,
                    'timezone' => $user->timezone,
                    'status' => $user->status,
                    'is_verified' => $user->is_verified,
                ]
            ], 'Profile updated successfully');
        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update profile', ['error' => $e->getMessage()], 500);
        }
    }
}
