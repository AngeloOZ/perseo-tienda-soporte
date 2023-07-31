<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSoporte extends Model
{
    use HasFactory;
    protected $table = 'usuarios_soporte';
    protected $primaryKey = 'tecnicosid';
    public $timestamps = false;
    protected $guarded = []; 
}
