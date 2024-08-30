<?php

namespace App\Http\Requests\Auth;

use App\Rules\EmailIsVerifiedRule;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
    public function rules(): array
    {
        return [
                'email' => ['required' , 'email' , 'exists:users,email' , new EmailIsVerifiedRule] ,
                'password' => 'required|confirmed' ,
                'password_otp_code' => 'required|string' ,
        ];
    }
}
