<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $table = 'paiements';
    protected $primaryKey = 'PaiementID';
    public $timestamps = false;

    protected $fillable = [
        'DemandeID', 'Montant', 'StatutPaiement', 'ReferenceTransaction',
    ];

    protected $casts = [
        'Montant' => 'decimal:2',
        'DatePaiement' => 'datetime',
    ];

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'DemandeID', 'DemandeID');
    }
}
