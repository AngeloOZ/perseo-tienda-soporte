<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    protected $table = 'log_crm_firmas';
    protected $primaryKey = 'log_crm_id';
    public $timestamps = false;
    protected $guarded = [];
}
