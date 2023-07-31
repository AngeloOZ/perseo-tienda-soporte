<?php

use Illuminate\Http\Request;
use App\Models\Tecnicos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('dev')->group(function () {

    Route::get('/login', function (Request $request) {

        $user = Tecnicos::firstWhere('identificacion', '2300368665');

        Auth::guard('admin')->login($user);
        $request->session()->regenerate();

        return redirect()->route('tecnicos.index');

        return "hola tecnico";
    })->name('tecnicos.login');


    Route::group(['prefix' => 'tecnicos', 'middleware' => 'admin'], function () {

        Route::get('/', function () {
            $user = Auth::guard('admin')->user();

            return "hola tecnico {$user->nombres}";
        })->name('tecnicos.index');

    });
});
