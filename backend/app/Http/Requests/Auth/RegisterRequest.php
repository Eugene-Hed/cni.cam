<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

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
     */
    public function rules(): array
    {
        return [
            'prenom' => ['required', 'string', 'max:50'],
            'nom' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'unique:utilisateurs,Email'],
            'telephone' => ['required', 'string', 'max:20', 'unique:utilisateurs,NumeroTelephone'],
            'date_naissance' => [
                'required', 
                'date', 
                function ($attribute, $value, $fail) {
                    if (Carbon::parse($value)->age < 18) {
                        $fail('Vous devez avoir au moins 18 ans pour vous inscrire.');
                    }
                }
            ],
            'genre' => ['required', 'in:M,F'],
            'adresse' => ['required', 'string'],
            'profession' => ['required', 'string', 'max:100'],
            'region_naissance_id' => ['required', 'exists:regions,RegionID'],
            'departement_naissance_id' => ['required', 'exists:departements,DepartementID'],
            'ville_naissance_id' => ['required', 'exists:villes,VilleID'],
            'region_residence_id' => ['required', 'exists:regions,RegionID'],
            'departement_residence_id' => ['required', 'exists:departements,DepartementID'],
            'ville_residence_id' => ['required', 'exists:villes,VilleID'],
            'ethnie_id' => ['required', 'exists:ethnies,EthnieID'],
            'force_creation' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'date_naissance.required' => 'La date de naissance est requise.',
            'date_naissance.date' => 'La date de naissance n\'est pas valide.',
            'genre.in' => 'Le genre doit être M (Masculin) ou F (Féminin).',
            'region_naissance_id.required' => 'La région de naissance est requise.',
            'departement_naissance_id.required' => 'Le département de naissance est requis.',
            'ville_naissance_id.required' => 'La ville de naissance est requise.',
            'ethnie_id.required' => 'L\'ethnie est requise.',
        ];
    }
}
