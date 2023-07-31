<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoporteEspecial extends Model
{
    use HasFactory;
    protected $table = 'soportes_especiales';
    protected $primaryKey = 'soporteid';
    public $timestamps = false;
    protected $guarded = []; 
}
