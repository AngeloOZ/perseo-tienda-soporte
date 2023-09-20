<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogSoporte extends Model
{
    use HasFactory;
    protected $table = 'log_soporte';
    protected $primaryKey = 'log_soporte_id';
    public $timestamps = false;
    protected $guarded = [];
}
