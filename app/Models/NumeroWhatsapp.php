<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NumeroWhatsapp extends Model
{
    use HasFactory;
    protected $table = 'numero_whatsapp';
    protected $primaryKey = 'idnumero';
    public $timestamps = false;
    protected $guarded = [];
}
