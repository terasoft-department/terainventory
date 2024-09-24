<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
     use HasFactory;

    protected $primaryKey = 'permission_id';

    protected $fillable = [
       'permission_description','role_id',
    ];

      public function p()
    {
        return $this->hasMany(User::class, 'role_id', 'role_id');
    }

     public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }
}
