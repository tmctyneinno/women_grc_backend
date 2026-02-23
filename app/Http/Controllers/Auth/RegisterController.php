<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponse;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => strtolower(trim($data['email'])),
            'linkedin_profile' => trim($data['linkedin_profile']??""),
            'password' => Hash::make($data['password']),
            'is_google_account' => $data['is_google_account'] ?? false,
            // 'email_verified_at' => ($data['is_google_account'] ?? false) ? now() : null,
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */



    public function register(RegisterRequest $request)
    {
        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'linkedin_profile' => $request->linkedin_profile ?? "",
                'password'   => Hash::make($request->password),
                'is_google_account' => $request->boolean('is_google_account') ?? false,
            ]);

            // auto login
            $this->guard()->login($user);

            // send verification email if not Google
            if (!$request->boolean('is_google_account')) {
                $user->sendEmailVerificationNotification();
            }

            return ApiResponse::success([
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email,
                    'linkedin_profile' => $user->linkedin_profile,
                ],
                'token' => $user->createToken('auth_token')->plainTextToken
            ], $request->boolean('is_google_account') 
                ? 'Registration successful! You are now logged in.' 
                : 'Registration successful! Please check your email to verify your account.', 201);

        } catch (\Exception $e) {
            return ApiResponse::error('Registration failed. Please try again.', ['error' => $e->getMessage()], 500);
        }
    }







    // public function register(Request $request)
    // {
    //     try {
    //     $this->validator($request->all())->validate();

    //     event(new Registered($user = $this->create($request->all())));

    //     $this->guard()->login($user);

    //     // if ($response = $this->registered($request, $user)) {
    //     //     return $response;
    //     // }

    //     return new JsonResponse([
    //         'success' => true,
    //         'message' => 'Registration successful! ' . 
    //             (($request->boolean('is_google_account') ?? false) 
    //                 ? 'You are now logged in.' 
    //                 : 'Please check your email to verify your account.'),
    //         'data' => [
    //             'user' => [
    //                 'id' => $user->id,
    //                 'first_name' => $user->first_name,
    //                 'last_name' => $user->last_name,
    //                 'email' => $user->email,
    //                 'linkedin_profile' => $user->linkedin_profile,
    //                 'status' => $user->status,
    //             ],
    //             'token' => $user->createToken('auth_token')->plainTextToken,
    //         ]
    //     ], 201);
    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         return new JsonResponse([
    //             'success' => false,
    //             'message' => 'Validation failed.',
    //             'errors' => $e->errors(),
    //         ], 422);
    //     } catch (\Exception $e) {
    //         return new JsonResponse([
    //             'success' => false,
    //             'message' => 'Registration failed. Please try again.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(\Illuminate\Http\Request $request, $user)
    {
        // If it's not a Google account, send verification email
        if (!$request->boolean('is_google_account')) {
            $user->sendEmailVerificationNotification();
        }
    }
}