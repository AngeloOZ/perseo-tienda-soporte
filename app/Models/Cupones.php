<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupones extends Model
{
    use HasFactory;
    protected $table = 'cupones';
    protected $primaryKey = 'cuponid';
    public $timestamps = false;
    protected $guarded = [];
}
