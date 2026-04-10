<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeCniDetail extends Model
{
    protected $table = 'demande_cni_details';
    protected $primaryKey = 'DetailID';
    public $timestamps = false;

    protected $fillable = [
        'DemandeID', 'TypeDemande', 'Nom', 'Prenom', 'DateNaissance', 'LieuNaissance',
        'Adresse', 'Sexe', 'Taille', 'Profession', 'StatutCivil',
        'NumeroCNIPrecedente', 'DatePerteVol', 'NumeroDecretNaturalisation',
        'NationalitePere', 'NationaliteMere',
    ];

    protected $casts = [
        'DateNaissance' => 'date',
        'DatePerteVol' => 'date',
    ];

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'DemandeID', 'DemandeID');
    }
}
