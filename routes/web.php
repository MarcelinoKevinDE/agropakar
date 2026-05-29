<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosaController;
use App\Http\Controllers\ArtikelController;

/*
|--------------------------------------------------------------------------
| Web Routes — AgroPakar Plant Disease Diagnosis System
|--------------------------------------------------------------------------
*/

// Redirect root ke halaman diagnosa
Route::get('/', function () {
    return redirect()->route('diagnosa.index');
});

// Group route untuk sistem diagnosa
Route::prefix('diagnosa')->name('diagnosa.')->group(function () {
    
    // Halaman utama (Menampilkan daftar gejala)
    Route::get('/', [DiagnosaController::class, 'index'])->name('index');
    
    // Proses hitung diagnosa (Pastikan action di form adalah {{ route('diagnosa.hitung') }})
    Route::post('/proses', [DiagnosaController::class, 'diagnosa'])->name('hitung'); 
    
    // Hasil diagnosa
    Route::get('/hasil', [DiagnosaController::class, 'hasil'])->name('hasil');
    
    // Reset sesi diagnosa
    Route::get('/reset', [DiagnosaController::class, 'reset'])->name('reset');
    
    // Artikel detail terkait penyakit
    Route::get('/artikel/{id}', [ArtikelController::class, 'show'])->name('artikel.show');
});