<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RendezVous extends Model
{
    protected $table = 'rendezvous';
    protected $primaryKey = 'RendezVousID';
    public $timestamps = false;

    protected $fillable = ['DemandeID', 'DateRendezVous', 'Lieu', 'Statut'];

    protected $casts = [
        'DateRendezVous' => 'datetime',
    ];

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'DemandeID', 'DemandeID');
    }
}
