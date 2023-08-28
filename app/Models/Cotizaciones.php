<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotizaciones extends Model
{
    use HasFactory;
    protected $table = 'cotizaciones';
    protected $primaryKey = 'cotizacionesid';
    public $timestamps = false;
    protected $guarded = [];
}
