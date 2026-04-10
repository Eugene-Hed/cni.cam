<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalActivite extends Model
{
    protected $table = 'journalactivites';
    protected $primaryKey = 'JournalID';
    public $timestamps = false;

    protected $fillable = [
        'UtilisateurID', 'TypeActivite', 'Description', 'AdresseIP',
    ];

    protected $casts = [
        'DateHeure' => 'datetime',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'UtilisateurID', 'UtilisateurID');
    }
}
