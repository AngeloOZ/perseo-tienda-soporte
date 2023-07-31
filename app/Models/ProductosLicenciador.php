<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductosLicenciador extends Model
{
    use HasFactory;
    protected $table = 'productos_homologados_licenciador';
    protected $primaryKey = 'id_homologado';
    public $timestamps = false;
    protected $guarded = [];
}
