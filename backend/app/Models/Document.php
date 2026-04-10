<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'documents';
    protected $primaryKey = 'DocumentID';
    public $timestamps = false;

    protected $fillable = [
        'DemandeID', 'TypeDocument', 'CheminFichier', 'StatutValidation',
        'Utilisateurid', 'DateValidation', 'ValidePar',
    ];

    protected $casts = [
        'DateTelechargement' => 'datetime',
        'DateValidation' => 'datetime',
    ];

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'DemandeID', 'DemandeID');
    }

    public function validateur()
    {
        return $this->belongsTo(Utilisateur::class, 'ValidePar', 'UtilisateurID');
    }
}
