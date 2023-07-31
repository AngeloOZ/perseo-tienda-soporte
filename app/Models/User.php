<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'usuariosid';
    public $timestamps = false;
    protected $rememberTokenName = false;

    protected $hidden = [
        'clave'
    ];

    //Especificar que el campo clave es contrasena
    public function getAuthPassword()
    {
        return $this->contrasena;
    }

   
}
