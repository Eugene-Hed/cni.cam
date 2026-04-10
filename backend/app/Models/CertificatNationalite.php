<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificatNationalite extends Model
{
    protected $table = 'certificatsnationalite';
    protected $primaryKey = 'CertificatID';
    public $timestamps = false;

    protected $fillable = [
        'DemandeID', 'NumeroCertificat', 'DateEmission',
        'CheminPDF', 'SignaturePresidentielle', 'CheminSignaturePresident',
    ];

    protected $casts = [
        'DateEmission' => 'date',
        'SignaturePresidentielle' => 'boolean',
    ];

    public function demande()
    {
        return $this->belongsTo(Demande::class, 'DemandeID', 'DemandeID');
    }
}
