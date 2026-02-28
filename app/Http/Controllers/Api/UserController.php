<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Timezone;

class UserController extends Controller
{

    public function timezone(){
        $timezone = Timezone::orderBy('timezone', 'asc')->get();

        return ApiResponse::success($timezone);
    }
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
                    'profile_picture' => $user->profile_picture
                        ?  asset('storage/'. $user->profile_picture)
                        : null,
                    'linkedin_profile' => $user->linkedin_profile,
                    'timezone_id' => $user->timezone_id,
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

            // Validate incoming fields
            $validated = $request->validate([
                'first_name' => 'nullable|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'phone_number' => 'nullable|string|max:20',
                'job_title' => 'nullable|string|max:255',
                'company' => 'nullable|string|max:255',
                'timezone_id' => 'nullable|integer|exists:timezone,id',
                'linkedin_profile' => [
                    'nullable', 
                    'string', 
                    'url', 
                    'regex:/https?:\/\/(www\.)?linkedin\.com\/in\/[A-Za-z0-9-]+\/?/'
                ],
                'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            ]);

            // Handle profile picture upload
            if ($request->hasFile('profile_picture')) {
                $path = $request->file('profile_picture')->store('profile_pictures', 'public');
                $validated['profile_picture'] = $path;
            }

            // Update user
            $user->update($validated);
            // dd($validated, $user->fresh());


            // Return full user info, using accessor for profile_picture
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
                    'timezone_id' => $user->timezone_id,
                    'profile_picture' => $user->profile_picture
                        ?  asset('storage/'. $user->profile_picture)
                        : null,                    'linkedin_profile' => $user->linkedin_profile,
                    'is_google_account' => $user->is_google_account,
                    'email_verified_at' => $user->email_verified_at,
                    'status' => $user->status,
                    'failed_login_attempts' => $user->failed_login_attempts,
                    'locked_until' => $user->locked_until,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ], 'Profile updated successfully');

        } catch (\Exception $e) {
            return ApiResponse::error('Failed to update profile', ['error' => $e->getMessage()], 500);
        }
    }


}
