<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Factura extends Model
{
    use HasFactory;
    protected $table = 'facturas';
    protected $primaryKey = 'facturaid';
    public $timestamps = false;
    protected $guarded = [];

    // protected $casts = [
    //     'detalle_pagos' => 'object',
    // ];


}
