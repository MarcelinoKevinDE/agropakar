<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Gejala
        Schema::create('gejalas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_gejala')->unique();
            $table->string('nama_gejala');
            $table->timestamps();
        });

        // 2. Tabel Penyakit
        Schema::create('penyakits', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penyakit')->unique();
            $table->string('nama_penyakit');
            $table->text('deskripsi')->nullable();
            $table->text('solusi')->nullable();
            $table->text('pencegahan')->nullable();
            $table->timestamps();
        });

        // 3. Tabel Aturan (Basis Pengetahuan untuk CF)
        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penyakit_id')->constrained()->onDelete('cascade');
            $table->foreignId('gejala_id')->constrained()->onDelete('cascade');
            $table->float('bobot_cf'); // Nilai CF (0.0 - 1.0)
            $table->timestamps();
        });

        // 4. Tabel Riwayat Diagnosa (Termasuk fitur Foto)
        Schema::create('diagnosa_histories', function (Blueprint $table) {
            $table->id();
            $table->string('nama_user')->nullable();
            $table->string('foto_tanaman')->nullable(); // Path foto
            $table->json('hasil_diagnosa'); // Menyimpan hasil akhir JSON
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosa_histories');
        Schema::dropIfExists('rules');
        Schema::dropIfExists('penyakits');
        Schema::dropIfExists('gejalas');
    }
};