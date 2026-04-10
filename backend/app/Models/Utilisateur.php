<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Utilisateur extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'utilisateurs';
    protected $primaryKey = 'UtilisateurID';
    public $timestamps = false;

    protected $fillable = [
        'Codeutilisateur', 'Email', 'NumeroTelephone', 'Prenom', 'Nom',
        'DateNaissance', 'Adresse', 'RoleId', 'PhotoUtilisateur', 'Genre',
        'IsActive', 'RegionNaissanceID', 'DepartementNaissanceID', 'VilleNaissanceID',
        'RegionResidenceID', 'DepartementResidenceID', 'VilleResidenceID',
        'EthnieID', 'Profession', 'CodeOTP', 'ExpirationOTP',
    ];

    protected $hidden = [
        'CodeOTP', 'ExpirationOTP',
    ];

    protected $casts = [
        'DateNaissance' => 'date',
        'DateCreation' => 'datetime',
        'DateMiseAJour' => 'datetime',
        'ExpirationOTP' => 'datetime',
        'IsActive' => 'boolean',
    ];

    // ---- Relationships ----

    public function role()
    {
        return $this->belongsTo(Role::class, 'RoleId', 'id');
    }

    public function regionNaissance()
    {
        return $this->belongsTo(Region::class, 'RegionNaissanceID', 'RegionID');
    }

    public function departementNaissance()
    {
        return $this->belongsTo(Departement::class, 'DepartementNaissanceID', 'DepartementID');
    }

    public function villeNaissance()
    {
        return $this->belongsTo(Ville::class, 'VilleNaissanceID', 'VilleID');
    }

    public function regionResidence()
    {
        return $this->belongsTo(Region::class, 'RegionResidenceID', 'RegionID');
    }

    public function departementResidence()
    {
        return $this->belongsTo(Departement::class, 'DepartementResidenceID', 'DepartementID');
    }

    public function villeResidence()
    {
        return $this->belongsTo(Ville::class, 'VilleResidenceID', 'VilleID');
    }

    public function ethnie()
    {
        return $this->belongsTo(Ethnie::class, 'EthnieID', 'EthnieID');
    }

    public function demandes()
    {
        return $this->hasMany(Demande::class, 'UtilisateurID', 'UtilisateurID');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'UtilisateurID', 'UtilisateurID');
    }

    public function cartesIdentite()
    {
        return $this->hasMany(CarteIdentite::class, 'UtilisateurID', 'UtilisateurID');
    }

    public function reclamations()
    {
        return $this->hasMany(Reclamation::class, 'UtilisateurID', 'UtilisateurID');
    }

    public function journalActivites()
    {
        return $this->hasMany(JournalActivite::class, 'UtilisateurID', 'UtilisateurID');
    }

    // ---- Helpers ----

    public function isAdmin(): bool
    {
        return $this->RoleId === 1;
    }

    public function isCitoyen(): bool
    {
        return $this->RoleId === 2;
    }

    public function isOfficier(): bool
    {
        return $this->RoleId === 3;
    }

    public function isPresident(): bool
    {
        return $this->RoleId === 4;
    }

    public function getNomCompletAttribute(): string
    {
        return trim(($this->Prenom ?? '') . ' ' . ($this->Nom ?? ''));
    }

    public function getInitialesAttribute(): string
    {
        return strtoupper(
            mb_substr($this->Prenom ?? 'U', 0, 1) . mb_substr($this->Nom ?? 'N', 0, 1)
        );
    }
}
