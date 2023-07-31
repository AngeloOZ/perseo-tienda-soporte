<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tarjeta extends Model
{
    use HasFactory;
    protected $table = 'tarjetas';
    protected $primaryKey = 'tarjetasid';
    public $timestamps = false;
    protected $guarded = []; 
}
