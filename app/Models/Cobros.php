<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobros extends Model
{
    use HasFactory;
    protected $table = 'cobros';
    protected $primaryKey = 'cobrosid';
    public $timestamps = false;
    protected $guarded = []; 
}
