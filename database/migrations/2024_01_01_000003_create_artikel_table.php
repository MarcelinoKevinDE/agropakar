<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ganti Schema::create('artikel', ...) menjadi:
Schema::create('artikels', function (Blueprint $table) {
    $table->id();
    $table->string('judul');
    $table->string('slug')->unique();
    $table->string('kategori')->default('Agronomi');
    $table->text('ringkasan')->nullable();
    $table->longText('konten')->nullable();
    $table->string('gambar')->nullable();
    $table->timestamp('published_at')->nullable();
    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};