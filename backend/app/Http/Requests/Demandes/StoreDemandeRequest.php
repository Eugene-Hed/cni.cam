<?php

namespace App\Http\Requests\Demandes;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeRequest extends FormRequest
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
        $rules = [
            'type_demande' => ['required', 'in:CNI,NATIONALITE'],
        ];

        if ($this->type_demande === 'CNI') {
            $rules = array_merge($rules, [
                'sous_type' => ['required', 'in:premiere,renouvellement,perte,naturalisation'],
                'nom' => ['required', 'string', 'max:50'],
                'prenom' => ['required', 'string', 'max:50'],
                'date_naissance' => ['required', 'date'],
                'lieu_naissance' => ['required', 'string', 'max:100'],
                'adresse' => ['required', 'string'],
                'sexe' => ['required', 'in:M,F'],
                'taille' => ['required', 'integer', 'min:50', 'max:250'],
                'profession' => ['required', 'string', 'max:100'],
                'statut_civil' => ['nullable', 'string', 'max:50'],
                'numero_cni_precedente' => ['nullable', 'string'],
                'date_perte_vol' => ['nullable', 'date'],
                'numero_decret_naturalisation' => ['nullable', 'string'],
                'nationalite_pere' => ['nullable', 'string'],
                'nationalite_mere' => ['nullable', 'string'],
            ]);
        } elseif ($this->type_demande === 'NATIONALITE') {
            $rules = array_merge($rules, [
                'nom' => ['required', 'string', 'max:50'],
                'prenom' => ['required', 'string', 'max:50'],
                'date_naissance' => ['required', 'date'],
                'lieu_naissance' => ['required', 'string', 'max:100'],
                'sexe' => ['nullable', 'in:M,F'],
                'nom_pere' => ['required', 'string', 'max:100'],
                'nom_mere' => ['required', 'string', 'max:100'],
                'adresse' => ['required', 'string'],
                'telephone' => ['required', 'string', 'max:20'],
                'motif' => ['required', 'in:naissance,mariage,naturalisation,filiation'],
                'ville' => ['nullable', 'string', 'max:100'],
                'code_postal' => ['nullable', 'string', 'max:20'],
                'etat_civil' => ['nullable', 'string', 'max:50'],
                'profession' => ['nullable', 'string', 'max:100'],
                'nationalite_actuelle' => ['nullable', 'string', 'max:100'],
            ]);
        }

        return $rules;
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'type_demande.required' => 'Le type de demande est requis.',
            'type_demande.in' => 'Le type de demande doit être CNI ou NATIONALITE.',
            'sous_type.required' => 'Le sous-type de demande est requis pour une CNI.',
            'motif.required' => 'Le motif est requis pour une demande de nationalité.',
            'taille.integer' => 'La taille doit être un nombre entier en cm.',
            'taille.min' => 'La taille minimale est de 50 cm.',
            'taille.max' => 'La taille maximale est de 250 cm.',
        ];
    }
}
