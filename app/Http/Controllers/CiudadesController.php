<?php

namespace App\Http\Controllers;

use App\Models\Ciudades;
use Illuminate\Http\Request;

class CiudadesController extends Controller
{
    public function recuperarciudades(Request $request)
    {
        $nuevoid = str_pad($request->id, "2", "0", STR_PAD_LEFT);

        $ciudades = Ciudades::where('ciudadesid', 'like', $nuevoid . '%')->get();
        return $ciudades;
    }
}
