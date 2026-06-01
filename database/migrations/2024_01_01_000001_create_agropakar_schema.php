<?php

// =============================================================================
// FILE: database/migrations/2024_01_02_000001_refactor_agropakar_schema.php
//
// SAFE MIGRATION for existing databases.
//
// Problem: The old 'gejala' table already exists. This migration:
//   1. Creates only NEW tables that do not yet exist (using Schema::hasTable checks).
//   2. Alters the existing 'gejala' table to add the new columns it needs.
//   3. Does NOT drop any existing table — existing data is preserved.
//   4. Uses Supabase/PostgreSQL-safe syntax throughout.
//
// Run order:
//   php artisan migrate
//
// If you need a completely clean slate instead:
//   php artisan migrate:fresh   (DESTROYS ALL DATA — only for dev)
// =============================================================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. plants ─────────────────────────────────────────────────────────
        if (!Schema::hasTable('plants')) {
            Schema::create('plants', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 10)->unique();
                $table->string('nama_tanaman', 120);
                $table->text('deskripsi')->nullable();
                $table->string('gambar')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // ── 2. symptom_categories ─────────────────────────────────────────────
        if (!Schema::hasTable('symptom_categories')) {
            Schema::create('symptom_categories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
                $table->string('nama_kategori', 80);
                $table->string('slug', 80);
                $table->unsignedTinyInteger('urutan')->default(0);
                $table->timestamps();

                $table->unique(['plant_id', 'slug']);
            });
        }

        // ── 3. gejala — ALTER existing table, do not recreate it ──────────────
        //
        // The old table exists with: id, kode, nama_gejala (and possibly others).
        // We ADD the new columns if they don't already exist.
        // This preserves every existing row and index.
        //
        Schema::table('gejala', function (Blueprint $table) {
            // Add category_id FK if it doesn't exist yet
            if (!Schema::hasColumn('gejala', 'category_id')) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('symptom_categories')
                    ->nullOnDelete();
            }

            // Add deskripsi if it doesn't exist yet
            if (!Schema::hasColumn('gejala', 'deskripsi')) {
                $table->text('deskripsi')->nullable()->after('nama_gejala');
            }

            // Add is_active if it doesn't exist yet
            if (!Schema::hasColumn('gejala', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('deskripsi');
            }

            // Ensure timestamps exist
            if (!Schema::hasColumn('gejala', 'created_at')) {
                $table->timestamps();
            }
        });

        // ── 4. diseases ───────────────────────────────────────────────────────
        //
        // The old table was called 'kerusakan'. We create a new 'diseases' table.
        // The migration guide in routes/web.php explains how to copy data across.
        //
        if (!Schema::hasTable('diseases')) {
            Schema::create('diseases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
                $table->string('kode', 15)->unique();
                $table->string('nama_penyakit', 200);
                $table->text('deskripsi')->nullable();
                $table->text('penyebab')->nullable();
                $table->string('gambar')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('plant_id');
            });
        }

        // ── 5. rules — ALTER existing OR create new ───────────────────────────
        //
        // Your old 'rules' table has: id, gejala_id, kerusakan_id, mb, md.
        // We need to ADD disease_id and is_primary.
        // We do NOT drop kerusakan_id yet — the data migration step will fill
        // disease_id from kerusakan_id, then you can drop kerusakan_id manually.
        //
        if (Schema::hasTable('rules')) {
            Schema::table('rules', function (Blueprint $table) {
                if (!Schema::hasColumn('rules', 'disease_id')) {
                    // Nullable at first — filled by the data migration below
                    $table->foreignId('disease_id')
                        ->nullable()
                        ->after('id')
                        ->constrained('diseases')
                        ->cascadeOnDelete();
                }

                if (!Schema::hasColumn('rules', 'is_primary')) {
                    $table->boolean('is_primary')->default(false)->after('md');
                }

                if (!Schema::hasColumn('rules', 'created_at')) {
                    $table->timestamps();
                }
            });
        } else {
            Schema::create('rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('disease_id')->constrained('diseases')->cascadeOnDelete();
                $table->foreignId('gejala_id')->constrained('gejala')->cascadeOnDelete();
                $table->decimal('mb', 4, 2)->default(0.00);
                $table->decimal('md', 4, 2)->default(0.00);
                $table->boolean('is_primary')->default(false);
                $table->timestamps();

                $table->unique(['disease_id', 'gejala_id']);
                $table->index('disease_id');
                $table->index('gejala_id');
            });
        }

        // ── 6. knowledge_base ─────────────────────────────────────────────────
        if (!Schema::hasTable('knowledge_base')) {
            Schema::create('knowledge_base', function (Blueprint $table) {
                $table->id();
                $table->foreignId('disease_id')->constrained('diseases')->cascadeOnDelete();
                $table->string('tipe', 30);
                $table->text('konten');
                $table->unsignedTinyInteger('urutan')->default(0);
                $table->timestamps();

                $table->index(['disease_id', 'tipe']);
            });
        }

        // ── 7. diagnosis_sessions ─────────────────────────────────────────────
        if (!Schema::hasTable('diagnosis_sessions')) {
            Schema::create('diagnosis_sessions', function (Blueprint $table) {
                $table->id();
                $table->string('session_code', 20)->unique();
                $table->string('nama_user', 100)->nullable();
                $table->foreignId('plant_id')
                    ->nullable()
                    ->constrained('plants')
                    ->nullOnDelete();
                $table->string('metode_akhir', 20)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('image_analysis_payload')->nullable();
                $table->string('image_path')->nullable();
                $table->timestamps();

                $table->index('plant_id');
                $table->index('created_at');
            });
        }

        // ── 8. diagnosis_symptoms ─────────────────────────────────────────────
        if (!Schema::hasTable('diagnosis_symptoms')) {
            Schema::create('diagnosis_symptoms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')
                    ->constrained('diagnosis_sessions')
                    ->cascadeOnDelete();
                $table->foreignId('gejala_id')
                    ->constrained('gejala')
                    ->cascadeOnDelete();
                $table->boolean('prefilled_by_image')->default(false);
                $table->timestamps();

                $table->unique(['session_id', 'gejala_id']);
                $table->index('session_id');
            });
        }

        // ── 9. diagnosis_results ──────────────────────────────────────────────
        if (!Schema::hasTable('diagnosis_results')) {
            Schema::create('diagnosis_results', function (Blueprint $table) {
                $table->id();
                $table->foreignId('session_id')
                    ->constrained('diagnosis_sessions')
                    ->cascadeOnDelete();
                $table->foreignId('disease_id')
                    ->constrained('diseases')
                    ->cascadeOnDelete();
                $table->decimal('cf_value', 6, 4)->default(0.0000);
                $table->decimal('cf_percentage', 5, 2)->default(0.00);
                $table->decimal('similarity_score', 6, 4)->default(0.0000);
                $table->string('metode', 20)->default('cf');
                $table->unsignedTinyInteger('peringkat');
                $table->json('cf_steps')->nullable();
                $table->timestamps();

                $table->index(['session_id', 'peringkat']);
                $table->index('disease_id');
            });
        }

        // ── 10. DATA MIGRATION — seed plants, categories, copy kerusakan ──────
        //
        // This runs inline so you don't need a separate seeder command.
        // It is idempotent — safe to run multiple times.
        //
        $this->seedInitialData();
    }

    // -------------------------------------------------------------------------

    public function down(): void
    {
        // Drop only the NEW tables we created.
        // We do NOT drop 'gejala' or 'rules' — those existed before this migration.
        Schema::dropIfExists('diagnosis_results');
        Schema::dropIfExists('diagnosis_symptoms');
        Schema::dropIfExists('diagnosis_sessions');
        Schema::dropIfExists('knowledge_base');
        Schema::dropIfExists('diseases');
        Schema::dropIfExists('symptom_categories');
        Schema::dropIfExists('plants');

        // Reverse the column additions to existing tables
        Schema::table('gejala', function (Blueprint $table) {
            if (Schema::hasColumn('gejala', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }
            if (Schema::hasColumn('gejala', 'deskripsi'))  $table->dropColumn('deskripsi');
            if (Schema::hasColumn('gejala', 'is_active'))  $table->dropColumn('is_active');
        });

        Schema::table('rules', function (Blueprint $table) {
            if (Schema::hasColumn('rules', 'disease_id')) {
                $table->dropForeign(['disease_id']);
                $table->dropColumn('disease_id');
            }
            if (Schema::hasColumn('rules', 'is_primary')) $table->dropColumn('is_primary');
        });
    }

    // -------------------------------------------------------------------------
    // Inline data migration — idempotent
    // -------------------------------------------------------------------------

    private function seedInitialData(): void
    {
        // ── A. Create default plant if none exists ────────────────────────────
        if (DB::table('plants')->count() === 0) {
            DB::table('plants')->insert([
                'kode'          => 'AGRO-01',
                'nama_tanaman'  => 'Tanaman Umum',
                'deskripsi'     => 'Tanaman default untuk sistem pakar AgroPakar.',
                'is_active'     => DB::raw('true'), // FIX: Menggunakan DB::raw
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        $plantId = DB::table('plants')->value('id');

        // ── B. Create symptom categories if none exist ────────────────────────
        if (DB::table('symptom_categories')->count() === 0) {
            $categories = [
                ['nama_kategori' => 'Daun',   'slug' => 'daun',   'urutan' => 1],
                ['nama_kategori' => 'Batang',  'slug' => 'batang', 'urutan' => 2],
                ['nama_kategori' => 'Akar',    'slug' => 'akar',   'urutan' => 3],
                ['nama_kategori' => 'Buah',    'slug' => 'buah',   'urutan' => 4],
                ['nama_kategori' => 'Bunga',   'slug' => 'bunga',  'urutan' => 5],
                ['nama_kategori' => 'Umum',    'slug' => 'umum',   'urutan' => 6],
            ];

            foreach ($categories as $cat) {
                DB::table('symptom_categories')->insert(array_merge($cat, [
                    'plant_id'   => $plantId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        $defaultCategoryId = DB::table('symptom_categories')
            ->where('slug', 'umum')
            ->value('id');

        // ── C. Assign all existing gejala to the default category ─────────────
       if ($defaultCategoryId) {
            DB::table('gejala')
                ->whereNull('category_id')
                ->update([
                    'category_id' => $defaultCategoryId,
                    'is_active'   => DB::raw('true'), // FIX: Menggunakan DB::raw
                ]);
        }

        // ── D. Copy kerusakan → diseases (if kerusakan table still exists) ────
        //
        // We check for the old table name. After verifying the copy is correct,
        // you can drop 'kerusakan' manually: Schema::dropIfExists('kerusakan');
        //
        if (Schema::hasTable('kerusakan') && DB::table('diseases')->count() === 0) {
            $kerusakanRows = DB::table('kerusakan')->get();

            foreach ($kerusakanRows as $k) {
                $kode = property_exists($k, 'kode') && $k->kode
                    ? $k->kode
                    : 'P-' . str_pad($k->id, 3, '0', STR_PAD_LEFT);

                // Insert disease
                $diseaseId = DB::table('diseases')->insertGetId([
                    'plant_id'      => $plantId,
                    'kode'          => $kode,
                    'nama_penyakit' => $k->nama_kerusakan,
                    'deskripsi'     => null,
                    'penyebab'      => null,
                    'is_active'     => DB::raw('true'),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);

                // Migrate solusi into knowledge_base
                $solusi = property_exists($k, 'solusi') ? $k->solusi : null;
                if (!empty($solusi)) {
                    DB::table('knowledge_base')->insert([
                        'disease_id' => $diseaseId,
                        'tipe'       => 'solusi',
                        'konten'     => $solusi,
                        'urutan'     => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // ── E. Back-fill rules.disease_id from old kerusakan_id ───────────────
        //
        // After diseases are populated above, map old kerusakan_id → new disease_id
        // using the kode we set in step D.
        //
        if (Schema::hasColumn('rules', 'disease_id') &&
            Schema::hasColumn('rules', 'kerusakan_id')) {

            $rules = DB::table('rules')
                ->whereNull('disease_id')
                ->get();

            foreach ($rules as $rule) {
                $oldKode   = 'P-' . str_pad($rule->kerusakan_id, 3, '0', STR_PAD_LEFT);
                $diseaseId = DB::table('diseases')->where('kode', $oldKode)->value('id');

                if ($diseaseId) {
                    DB::table('rules')
                        ->where('id', $rule->id)
                        ->update(['disease_id' => $diseaseId]);
                }
            }
        }
    }
};