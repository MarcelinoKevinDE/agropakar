<?php

// =============================================================================
// FILE: routes/web.php  (relevant section — merge with your existing routes)
// =============================================================================

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DiagnosaController;
use App\Http\Controllers\Admin\AdminDashboardController;

// ── Root ─────────────────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('diagnosa.plant'));

// ── Public diagnosis wizard ───────────────────────────────────────────────────
Route::prefix('diagnosa')->name('diagnosa.')->group(function () {

    // Step 1: plant selection
    Route::get('/tanaman', [DiagnosaController::class, 'selectPlant'])
        ->name('plant');

    // Step 2: symptom selection (scoped to plant)
    Route::get('/{plantId}', [DiagnosaController::class, 'index'])
        ->name('index')
        ->where('plantId', '[0-9]+');

    // Step 3: run diagnosis (POST → PRG redirect)
    Route::post('/hitung', [DiagnosaController::class, 'hitung'])
        ->name('hitung');

    // Catch stale GET on /hitung (browser history / refresh)
    Route::get('/hitung', fn () => redirect()->route('diagnosa.plant'));

    // Step 4: show results
    Route::get('/hasil/{sessionCode}', [DiagnosaController::class, 'hasil'])
        ->name('hasil')
        ->where('sessionCode', 'DIAG-[0-9A-Z\-]+');

    // Image analysis entry point (architecture-ready)
    Route::post('/analyze-image', [DiagnosaController::class, 'analyzeImage'])
        ->name('analyze-image');

    // Re-diagnose from a previous session
    Route::get('/rediagnose/{sessionCode}', [DiagnosaController::class, 'rediagnose'])
        ->name('rediagnose');
});

// ── Admin ─────────────────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/history',   [AdminDashboardController::class, 'history'])->name('history');
    Route::get('/history/{diagnosisSession}', [AdminDashboardController::class, 'show'])->name('history.show');
});

// =============================================================================
// FILE: app/Providers/AppServiceProvider.php  (add to the register() method)
//
// Bind DiagnosisService as a singleton so the same instance is reused
// within a single request. No state is stored between requests since
// Laravel boots fresh on every HTTP request anyway.
// =============================================================================
//
// use App\Services\DiagnosisService;
//
// public function register(): void
// {
//     $this->app->singleton(DiagnosisService::class);
// }

// =============================================================================
// DATA MIGRATION GUIDE
// Moving from the old schema (gejala, kerusakan, rules) to the new one
// =============================================================================
//
// Run this as a one-time artisan command or a DatabaseSeeder migration class.
//
// STEP 1 — Create the default plant record
//
//   DB::table('plants')->insert([
//       'kode'          => 'TANAMAN-01',
//       'nama_tanaman'  => 'Tanaman Umum',
//       'is_active'     => true,
//       'created_at'    => now(),
//       'updated_at'    => now(),
//   ]);
//   $plantId = DB::getPdo()->lastInsertId();
//
// STEP 2 — Create default symptom categories
//
//   $categories = ['Daun', 'Batang', 'Akar', 'Buah', 'Bunga', 'Umum'];
//   foreach ($categories as $i => $name) {
//       DB::table('symptom_categories')->insert([
//           'plant_id'      => $plantId,
//           'nama_kategori' => $name,
//           'slug'          => Str::slug($name),
//           'urutan'        => $i,
//           'created_at'    => now(),
//           'updated_at'    => now(),
//       ]);
//   }
//   $defaultCategoryId = DB::table('symptom_categories')
//       ->where('slug', 'umum')->value('id');
//
// STEP 3 — Migrate gejala (columns id, kode, nama_gejala are preserved)
//
//   DB::table('gejala')->get()->each(function ($old) use ($defaultCategoryId) {
//       DB::table('gejala')->where('id', $old->id)->update([
//           'category_id' => $defaultCategoryId,
//           'is_active'   => true,
//       ]);
//   });
//
// STEP 4 — Migrate kerusakan → diseases
//
//   DB::table('kerusakan')->get()->each(function ($k) use ($plantId) {
//       DB::table('diseases')->insert([
//           'plant_id'      => $plantId,
//           'kode'          => $k->kode ?? 'P-' . $k->id,
//           'nama_penyakit' => $k->nama_kerusakan,
//           'deskripsi'     => null,
//           'penyebab'      => null,
//           'is_active'     => true,
//           'created_at'    => now(),
//           'updated_at'    => now(),
//       ]);
//
//       // Migrate solusi into knowledge_base
//       if (!empty($k->solusi)) {
//           $diseaseId = DB::table('diseases')->where('kode', 'P-' . $k->id)->value('id');
//           DB::table('knowledge_base')->insert([
//               'disease_id' => $diseaseId,
//               'tipe'       => 'solusi',
//               'konten'     => $k->solusi,
//               'urutan'     => 0,
//               'created_at' => now(),
//               'updated_at' => now(),
//           ]);
//       }
//   });
//
// STEP 5 — Migrate rules (old kerusakan_id → new disease_id)
//
//   DB::table('rules')->get()->each(function ($r) {
//       // Map old kerusakan_id to new disease_id
//       // The mapping depends on whether you used the kode above
//       $newDiseaseId = DB::table('diseases')
//           ->where('kode', 'P-' . $r->kerusakan_id)
//           ->value('id');
//
//       if (!$newDiseaseId) return; // skip orphaned rules
//
//       DB::table('rules')
//           ->where('id', $r->id)
//           ->update([
//               'disease_id' => $newDiseaseId,
//               // mb and md are already correct if your old table had them
//               // If your old table had a single 'cf' column:
//               // 'mb' => $r->cf,
//               // 'md' => 0.0,
//           ]);
//   });
//
// STEP 6 — Verify
//
//   php artisan tinker
//   >>> App\Models\Disease::with('rules')->first()->rules->count()
//   >>> App\Models\Gejala::first()->category->nama_kategori