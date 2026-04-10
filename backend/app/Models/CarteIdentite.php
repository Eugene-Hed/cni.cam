<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarteIdentite extends Model
{
    protected $table = 'cartesidentite';
    protected $primaryKey = 'CarteID';
    public $timestamps = false;

    protected $fillable = [
        'UtilisateurID', 'DemandeID', 'NumeroCarteIdentite',
        'DateEmission', 'DateExpiration', 'CodeQR', 'CheminFichier', 'Statut',
    ];

    protected $casts = [
        'DateEmission' => 'date',
        'DateExpiration' => 'date',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'UtilisateurID', 'UtilisateurID');
    }

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'DemandeID', 'DemandeID');
    }
}
