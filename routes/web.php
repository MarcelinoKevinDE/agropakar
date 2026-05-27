<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosaController;

/*
|--------------------------------------------------------------------------
| Web Routes — AgroPakar Plant Disease Diagnosis System
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('diagnosa.index');
});

Route::prefix('diagnosa')->name('diagnosa.')->group(function () {
    Route::get('/',        [DiagnosaController::class, 'index'])->name('index');
    Route::post('/proses', [DiagnosaController::class, 'diagnosa'])->name('proses');
    Route::get('/hasil',   [DiagnosaController::class, 'hasil'])->name('hasil');
    Route::get('/reset',   [DiagnosaController::class, 'reset'])->name('reset');
    Route::get('/artikel/{id}', [App\Http\Controllers\ArtikelController::class, 'show'])->name('artikel.show');
});