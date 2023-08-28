<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plantillas extends Model
{
    protected $table = 'plantillas';
    protected $primaryKey = 'plantillasid';
    public $timestamps = false;
    protected $guarded = [];
}
