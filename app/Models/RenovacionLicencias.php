<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RenovacionLicencias extends Model
{
    use HasFactory;
    protected $table = 'renovacion_licencias';
    protected $primaryKey = 'renovacionid';
    public $timestamps = false;
    protected $guarded = [];
}
