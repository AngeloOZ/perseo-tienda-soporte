<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanificacionesDetalles extends Model
{
    use HasFactory;
    protected $table = 'planificaciones_detalles';
    protected $primaryKey = 'planificaciones_detallesid';
    public $timestamps = false;
    protected $guarded = [];
}
