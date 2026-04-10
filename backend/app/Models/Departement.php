<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departement extends Model
{
    protected $table = 'departements';
    protected $primaryKey = 'DepartementID';
    public $timestamps = false;

    protected $fillable = ['RegionID', 'NomDepartement'];

    public function region()
    {
        return $this->belongsTo(Region::class, 'RegionID', 'RegionID');
    }

    public function villes()
    {
        return $this->hasMany(Ville::class, 'DepartementID', 'DepartementID');
    }
}
