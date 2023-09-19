<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlantillaDescarga extends Model
{
    use HasFactory;
    protected $table = 'plantillasdescarga';
    protected $primaryKey = 'plantillasDescargaid';
    public $timestamps = false;
    protected $guarded = [];
}
