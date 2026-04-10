<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'identifiant.required' => 'L\'identifiant (email ou téléphone) est requis.',
            'methode.required' => 'La méthode de connexion est requise.',
            'methode.in' => 'La méthode doit être soit "email" soit "telephone".',
        ];
    }
}
