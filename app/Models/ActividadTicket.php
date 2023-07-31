<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadTicket extends Model
{
    use HasFactory;
    protected $table = 'actividad_ticket';
    protected $primaryKey = 'actividadid';
    public $timestamps = false;
    protected $guarded = []; 
}
