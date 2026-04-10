<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriqueDemande extends Model
{
    protected $table = 'historique_demandes';
    protected $primaryKey = 'HistoriqueID';
    public $timestamps = false;

    protected $fillable = [
        'DemandeID', 'AncienStatut', 'NouveauStatut',
        'DateModification', 'Commentaire', 'ModifiePar',
    ];

    protected $casts = [
        'DateModification' => 'datetime',
    ];

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'DemandeID', 'DemandeID');
    }

    public function modifiePar()
    {
        return $this->belongsTo(Utilisateur::class, 'ModifiePar', 'UtilisateurID');
    }
}
