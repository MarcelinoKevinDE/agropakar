<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosaController;
use App\Http\Controllers\ArtikelController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('diagnosa.index');
});

/*
|--------------------------------------------------------------------------
| Diagnosa
|--------------------------------------------------------------------------
*/

Route::prefix('diagnosa')->name('diagnosa.')->group(function () {

    // halaman diagnosa
    Route::get('/', [DiagnosaController::class, 'index'])
        ->name('index');

    // proses diagnosa
    Route::post('/proses', [DiagnosaController::class, 'diagnosa'])
        ->name('proses');

    // hasil diagnosa
    Route::get('/hasil', [DiagnosaController::class, 'hasil'])
        ->name('hasil');

    // reset session
    Route::get('/reset', [DiagnosaController::class, 'reset'])
        ->name('reset');
});

/*
|--------------------------------------------------------------------------
| Artikel
|--------------------------------------------------------------------------
*/

Route::get('/artikel/{id}', [ArtikelController::class, 'show'])
    ->name('artikel.show');