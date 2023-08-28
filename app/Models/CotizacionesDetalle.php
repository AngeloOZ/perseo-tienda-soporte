<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CotizacionesDetalle extends Model
{

    use HasFactory;
    protected $table = 'cotizaciones_detalle';
    protected $primaryKey = 'detallesid';
    public $timestamps = false;
    protected $guarded = [];
}
