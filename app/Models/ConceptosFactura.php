<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConceptosFactura extends Model
{
    use HasFactory;
    protected $table = 'concepto_factura';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = []; 
}
