<?php

use App\Models\Gejala;
use Illuminate\Support\Facades\Route;

Route::get('/debug-gejala', function () {
    $count = Gejala::count();
    $first = Gejala::first();

    return response()->json([
        'total_gejala' => $count,
        'data_pertama' => $first,
        'status' => $count > 0 ? 'Data ditemukan' : 'Database kosong'
    ]);
});

use App\Http\Controllers\DiagnosaController;
use App\Http\Controllers\Admin\AdminDashboardController;

Route::get('/', fn() => redirect()->route('diagnosa.index'));

Route::prefix('diagnosa')->name('diagnosa.')->group(function () {
    Route::get('/',       [DiagnosaController::class, 'index']) ->name('index');

    // The POST route is named 'diagnosa.hitung' — matches what the form uses.
    // The GET fallback silently redirects stale browser history to the form.
    Route::post('/hitung', [DiagnosaController::class, 'hitung'])->name('hitung');
    Route::get('/hitung',   fn() => redirect()->route('diagnosa.index'));
});

Route::get('/about', [DiagnosaController::class, 'about'])->name('about');

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard',                  [AdminDashboardController::class, 'index'])      ->name('dashboard');
    Route::get('/history',                    [AdminDashboardController::class, 'history'])    ->name('history');
    Route::get('/history/{diagnosisHistory}', [AdminDashboardController::class, 'historyShow'])->name('history.show');
});