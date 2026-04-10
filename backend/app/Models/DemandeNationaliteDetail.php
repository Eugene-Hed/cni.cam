<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeNationaliteDetail extends Model
{
    protected $table = 'demande_nationalite_details';
    protected $primaryKey = 'DetailID';
    public $timestamps = false;

    protected $fillable = [
        'DemandeID', 'Nom', 'Prenom', 'DateNaissance', 'LieuNaissance', 'Sexe',
        'NomPere', 'NomMere', 'Adresse', 'Ville', 'CodePostal', 'Telephone',
        'EtatCivil', 'Profession', 'NationaliteActuelle', 'Motif',
    ];

    protected $casts = [
        'DateNaissance' => 'date',
    ];

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'DemandeID', 'DemandeID');
    }
}
