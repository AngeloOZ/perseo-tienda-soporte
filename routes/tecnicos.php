<?php

use Illuminate\Support\Facades\Route;

Route::prefix('dev')->group(function () {

    Route::get('/estado-firma', function () {

        return "ok";
    });
});
