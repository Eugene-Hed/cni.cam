<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Demande extends Model
{
    protected $table = 'demandes';
    protected $primaryKey = 'DemandeID';
    public $timestamps = false;

    protected $fillable = [
        'NumeroReference', 'UtilisateurID', 'TypeDemande', 'SousTypeDemande',
        'Statut', 'DateSoumission', 'DateAchevement', 'MontantPaiement', 'StatutPaiement',
        'SignatureRequise', 'SignatureEnregistree', 'CheminSignature', 'DateSignature',
        'SignatureOfficierRequise', 'SignatureOfficierEnregistree',
        'CheminSignatureOfficier', 'DateSignatureOfficier',
    ];

    protected $casts = [
        'DateSoumission' => 'datetime',
        'DateAchevement' => 'datetime',
        'DateSignature' => 'datetime',
        'DateSignatureOfficier' => 'datetime',
        'MontantPaiement' => 'decimal:2',
        'SignatureRequise' => 'boolean',
        'SignatureEnregistree' => 'boolean',
        'SignatureOfficierRequise' => 'boolean',
        'SignatureOfficierEnregistree' => 'boolean',
    ];

    // ---- Relationships ----

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class, 'UtilisateurID', 'UtilisateurID');
    }

    public function detailsCni()
    {
        return $this->hasOne(DemandeCniDetail::class, 'DemandeID', 'DemandeID');
    }

    public function detailsNationalite()
    {
        return $this->hasOne(DemandeNationaliteDetail::class, 'DemandeID', 'DemandeID');
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'DemandeID', 'DemandeID');
    }

    public function historique()
    {
        return $this->hasMany(HistoriqueDemande::class, 'DemandeID', 'DemandeID');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class, 'DemandeID', 'DemandeID');
    }

    public function carteIdentite()
    {
        return $this->hasOne(CarteIdentite::class, 'DemandeID', 'DemandeID');
    }

    public function certificatNationalite()
    {
        return $this->hasOne(CertificatNationalite::class, 'DemandeID', 'DemandeID');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'DemandeID', 'DemandeID');
    }

    public function reclamations()
    {
        return $this->hasMany(Reclamation::class, 'DemandeID', 'DemandeID');
    }

    public function rendezVous()
    {
        return $this->hasOne(RendezVous::class, 'DemandeID', 'DemandeID');
    }

    // ---- Helpers ----

    public function isCni(): bool
    {
        return $this->TypeDemande === 'CNI';
    }

    public function isNationalite(): bool
    {
        return in_array($this->TypeDemande, ['NATIONALITE', 'CertificatNationalite']);
    }
}
