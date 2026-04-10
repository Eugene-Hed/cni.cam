<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LieuRetrait extends Model
{
    protected $table = 'lieuxretrait';
    protected $primaryKey = 'LieuID';
    public $timestamps = false;

    protected $fillable = ['NomLieu', 'Adresse', 'NumeroContact'];
}
