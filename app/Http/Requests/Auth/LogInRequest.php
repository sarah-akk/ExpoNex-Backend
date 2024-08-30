<?php

namespace App\Http\Requests\Auth;


use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class LogInRequest extends FormRequest
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
            'username' => [Rule::requiredIf(!request()->has('email')) , 'string' , 'exists:users,username' ] ,
            'email' => [Rule::requiredIf(!request()->has('username')) , 'email' , 'exists:users,email' , 'string' , 'max:255'],
            'password' => ['required' , 'string'],
        ];
    }
}
