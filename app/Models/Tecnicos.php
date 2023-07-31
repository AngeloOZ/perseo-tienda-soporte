<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Tecnicos extends Authenticatable
{
    use HasFactory;
    use \Rackbeat\UIAvatars\HasAvatar;

    protected $table = 'tecnicos';
    protected $primaryKey = 'tecnicosid';
    public $timestamps = false;
    protected $guarded = [];

    protected $hidden = [
        'clave'
    ];

    public function getAvatarNameKey()
    {
        return 'nombres';
    }
}
