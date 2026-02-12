<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules()
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'linkedin_profile' => [
                'nullable', 
                'string', 
                'url', 
                'regex:/https?:\/\/(www\.)?linkedin\.com\/in\/[A-Za-z0-9-]+\/?/'
            ],
            'password'   => [
                'required', 
                'string', 
                'min:8', 
                'confirmed', 
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ],
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required'  => 'Last name is required.',
            'email.required'      => 'Email is required.',
            'email.email'         => 'Email must be valid.',
            'email.unique'        => 'This email is already exist.',
            'linkedin_profile.url'=> 'LinkedIn profile must be a valid URL.',
            'linkedin_profile.regex'=> 'LinkedIn profile must be a valid LinkedIn URL.',
            'password.required'   => 'Password is required.',
            'password.min'        => 'Password must be at least 8 characters.',
            'password.confirmed'  => 'Password confirmation does not match.',
            'password.regex'      => 'Password must contain uppercase, lowercase, number, and special character.',
        ];
    }

    public function validated($key = null, $default = null)
    {
        // Get parent validated data
        $data = parent::validated($key, $default);

        // Clean up inputs
        $data['email'] = strtolower(trim($data['email'] ?? ''));
        $data['linkedin_profile'] = trim($data['linkedin_profile'] ?? '');

        return $data;
    }

    
}

