<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ciudades extends Model
{
    use HasFactory;
    protected $table = 'ciudades';
    protected $primaryKey = 'ciudadesid';
    public $timestamps = false;
    protected $guarded = []; 
}
