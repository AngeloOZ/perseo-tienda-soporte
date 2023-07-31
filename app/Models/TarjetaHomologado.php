<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TarjetaHomologado extends Model
{
    use HasFactory;
    protected $table = 'tarjeta_homologados_distribuidor';
    protected $primaryKey = 'id_tarjeta_homologacion';
    public $timestamps = false;
    protected $guarded = []; 
}
