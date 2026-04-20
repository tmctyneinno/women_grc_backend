<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Helpers\ApiResponse;

class ResetPasswordController extends Controller
{
    /**
     * Handle the password reset request.
     */
    public function reset(Request $request)
    {
        // Validate request
        $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).+$/'
            ],
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'token.required' => 'Reset token is required.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
        ]);


        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    // Update user password and reset login attempts
                    $user->forceFill([
                        'password' => Hash::make($password),
                        'remember_token' => Str::random(60),
                        'failed_login_attempts' => 0,
                        'locked_until' => null,
                    ])->save();

                    // Invalidate old API tokens
                    $user->tokens()->delete();
                }
            );

            if ($status === Password::PASSWORD_RESET) {
                // Retrieve updated user to issue new token
                $user = \App\Models\User::where('email', $request->email)->first();

                $token = $user->createToken('auth_token')->plainTextToken;

                return ApiResponse::success([
                    'user' => [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'linkedin_profile' => $user->linkedin_profile,
                    ],
                    'token' => $token,
                ], 'Password reset successful.');
            }

            return ApiResponse::error('Invalid or expired token.', [], 400);

        } catch (\Exception $e) {
            \Log::error('Reset password error: ' . $e->getMessage());

            return ApiResponse::error(
                'Something went wrong while resetting the password.',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
