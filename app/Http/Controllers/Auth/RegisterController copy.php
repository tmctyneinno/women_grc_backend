<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterControllerCp extends Controller
{
    /**
     * Handle an incoming registration request.
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'linkedin_profile' => $request->linkedin_profile,
                'password' => $request->password,
                'is_google_account' => $request->boolean('is_google_account', false),
                'email_verified_at' => $request->boolean('is_google_account') ? now() : null,
            ]);

            // Fire registered event
            event(new Registered($user));

            // If it's not a Google account, we'll send verification email
            if (!$request->boolean('is_google_account')) {
                $user->sendEmailVerificationNotification();
            }

            // Log the user in
            Auth::login($user);

            // Generate token for API response
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! ' . 
                    ($request->boolean('is_google_account') 
                        ? 'You are now logged in.' 
                        : 'Please check your email to verify your account.'),
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'linkedin_profile' => $user->linkedin_profile,
                        'email_verified' => (bool) $user->email_verified_at,
                    ],
                    'token' => $token,
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Registration failed:', [
                'error' => $e->getMessage(),
                'email' => $request->email,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again later.',
                'errors' => [
                    'general' => ['An unexpected error occurred. Please try again.']
                ]
            ], 500);
        }
    }
}