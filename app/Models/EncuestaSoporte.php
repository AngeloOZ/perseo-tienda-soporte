<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EncuestaSoporte extends Model
{
    use HasFactory;
    protected $table = 'encuesta_soporte';
    protected $primaryKey = 'encuesta_soporte_id';
    public $timestamps = false;
    protected $guarded = [];
}
