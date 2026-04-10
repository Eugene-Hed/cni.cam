<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ethnie extends Model
{
    protected $table = 'ethnies';
    protected $primaryKey = 'EthnieID';
    public $timestamps = false;

    protected $fillable = ['NomEthnie', 'Description', 'RegionPrincipale'];

    public function region()
    {
        return $this->belongsTo(Region::class, 'RegionPrincipale', 'RegionID');
    }
}
