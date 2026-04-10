<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reclamation extends Model
{
    protected $table = 'reclamations';
    protected $primaryKey = 'ReclamationID';
    public $timestamps = false;

    protected $fillable = [
        'UtilisateurID', 'DemandeID', 'TypeReclamation', 'Description', 'Statut',
    ];

    protected $casts = [
        'DateCreation' => 'datetime',
        'DateMiseAJour' => 'datetime',
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
