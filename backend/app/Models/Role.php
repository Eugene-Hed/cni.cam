<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';
    public $timestamps = false;

    protected $fillable = ['role'];

    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class, 'RoleId', 'id');
    }
}
