<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosaController;
use App\Http\Controllers\ArtikelController;

/*
|--------------------------------------------------------------------------
| Web Routes — AgroPakar Plant Disease Diagnosis System
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('diagnosa.index');
});

Route::prefix('diagnosa')->name('diagnosa.')->group(function () {
    Route::get('/', [DiagnosaController::class, 'index'])->name('index');
    
    // Route ini sekarang bernama 'diagnosa.hitung'
    Route::post('/proses', [DiagnosaController::class, 'diagnosa'])->name('hitung'); 
    
    Route::get('/hasil', [DiagnosaController::class, 'hasil'])->name('hasil');
    Route::get('/reset', [DiagnosaController::class, 'reset'])->name('reset');
    Route::get('/artikel/{id}', [ArtikelController::class, 'show'])->name('artikel.show');
});