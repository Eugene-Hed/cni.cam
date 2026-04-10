<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'identifiant' => ['required', 'string'],
            'methode' => ['required', 'in:email,telephone'],
            'code' => ['required', 'string', 'size:6'],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Le code OTP est requis.',
            'code.size' => 'Le code OTP doit comporter exactement 6 chiffres.',
        ];
    }
}
