<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Helpers\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;



class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    

    // /**
    //  * Create a new controller instance.
    //  *
    //  * @return void
    //  */
    // public function __construct()
    // {
    //     $this->middleware('guest')->except('logout');
    //     $this->middleware('auth')->only('logout');
    // }


    public function login(LoginRequest $request)
    {
        $user = User::where('email', strtolower($request->email))->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.'
            ], 401);
        }

        if ($user->locked_until && now()->lessThan($user->locked_until)) {
            return ApiResponse::error(
                'Account locked due to multiple failed attempts. Try again later.',
                [],
                423
            );
        }

        if (!$user || !Hash::check($request->password, $user->password)) {

            if ($user) {
                $user->increment('failed_login_attempts');

                if ($user->failed_login_attempts >= 5) {
                    $user->update([
                        'locked_until' => now()->addMinutes(15),
                        'failed_login_attempts' => 0,
                    ]);
                }
            }

            return ApiResponse::error('Invalid credentials.', [], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return ApiResponse::error(
                'Please verify your email before logging in.',
                [],
                403
            );
        }

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);


        $user->tokens()->delete(); // optional security

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponse::success([
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'status'=>$user->status,
                'linkedin_profile' => $user->linkedin_profile,
            ],
            'token' => $token,
        ], 'Login successful');
    }



    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponse::success([], 'Logged out successfully');
    }
}
