<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'NotificationID';
    public $timestamps = false;

    protected $fillable = [
        'UtilisateurID', 'DemandeID', 'Contenu', 'TypeNotification', 'EstLue',
    ];

    protected $casts = [
        'EstLue' => 'boolean',
        'DateCreation' => 'datetime',
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
