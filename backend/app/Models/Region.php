<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $table = 'regions';
    protected $primaryKey = 'RegionID';
    public $timestamps = false;

    protected $fillable = ['NomRegion'];

    public function departements()
    {
        return $this->hasMany(Departement::class, 'RegionID', 'RegionID');
    }

    public function ethnies()
    {
        return $this->hasMany(Ethnie::class, 'RegionPrincipale', 'RegionID');
    }
}
