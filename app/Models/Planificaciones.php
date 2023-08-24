<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planificaciones extends Model
{
    use HasFactory;
    
    protected $table = 'planificaciones';
    protected $primaryKey = 'planificacionesid';
    public $timestamps = false;
    protected $guarded = [];
}
