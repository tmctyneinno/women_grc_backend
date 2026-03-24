<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;




class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    

    // use SendsPasswordResetEmails;
    public function sendResetLink(Request $request)
        {
            $request->validate([
                'email' => ['required', 'email'],
            ]);

            try {
                $status = Password::sendResetLink(
                    $request->only('email')
                );

                if ($status === Password::RESET_LINK_SENT) {
                    return ApiResponse::success(
                        [],
                        'Password reset link sent successfully.'
                    );
                }

                return ApiResponse::error(
                    'Unable to send reset link. Please check the email and try again.',
                    [],
                    400
                );
            } catch (\Exception $e) {
                // Optional: log the error for debugging
                \Log::error('Forgot password error: '.$e->getMessage());

                return ApiResponse::error(
                    'Something went wrong while sending the reset link.',
                    ['error' => $e->getMessage()],
                    500
                );
            }
        }




    public function reset(Request $request)
    {
        $token=urlencode($request->token);
        $email=urlencode($request->email);

        return view('auth.passwords.confirm', ['token' => $token, 'email' => $email]);
    }                    

}