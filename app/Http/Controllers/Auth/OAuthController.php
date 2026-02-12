<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Helpers\ApiResponse;

class OAuthController extends Controller
{
    /**
     * Redirect user to provider
     */
    public function redirect($provider)
    {
        $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
        return ApiResponse::success(['url' => $url], 'Redirect to OAuth provider');
    }

    /**
     * Handle callback
     */
    public function callback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();

            // Check if user exists
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'first_name' => $socialUser->getName() ?? 'NoName',
                    'last_name' => '',
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(16)), // random password
                    'is_google_account' => true,
                    'email_verified_at' => now(),
                    'linkedin_profile' => '',
                ]);
            }

            // Create token
            $token = $user->createToken('auth_token')->plainTextToken;

            return ApiResponse::success([
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                ],
                'token' => $token,
            ], 'Login successful');

        } catch (\Exception $e) {
            return ApiResponse::error('OAuth login failed', ['error' => $e->getMessage()], 500);
        }
    }
}