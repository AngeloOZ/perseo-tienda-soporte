<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoHomologado extends Model
{
    use HasFactory;
    protected $table = 'productos_homologados_distribuidor2';
    protected $primaryKey = 'productos_homologados_id';
    public $timestamps = false;
    protected $guarded = [];

    public static function getTableName()
    {
        return with(new static)->getTable();
    }
}
