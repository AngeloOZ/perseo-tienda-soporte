<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;


class Clientes extends Authenticatable
{
    use HasFactory;
    use \Rackbeat\UIAvatars\HasAvatar;
    protected $table = 'clientes';
    protected $primaryKey = 'clientesid';
    public $timestamps = false;
    protected $rememberTokenName = false;
    protected $guarded = [];
    protected $hidden = [
        'clave'
    ];

    public function getAvatarNameKey()
    {
        return 'razonsocial';
    }
}
