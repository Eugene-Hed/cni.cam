<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ville extends Model
{
    protected $table = 'villes';
    protected $primaryKey = 'VilleID';
    public $timestamps = false;

    protected $fillable = ['DepartementID', 'NomVille'];

    public function departement()
    {
        return $this->belongsTo(Departement::class, 'DepartementID', 'DepartementID');
    }
}
