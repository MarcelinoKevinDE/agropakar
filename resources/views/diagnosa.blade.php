@extends('layouts.app')
@php use Illuminate\Support\Facades\Storage; use Illuminate\Support\Str; @endphp

@section('title', 'Diagnosis Penyakit Tanaman — AgroPakar')
@section('meta_description', 'Deteksi penyakit tanaman secara akurat menggunakan metode Certainty Factor. Pilih gejala yang Anda amati dan dapatkan hasil diagnosis instan.')

@push('styles')
<style>
    /* ---- Symptom Checkbox Cards ---- */
    .gejala-card {
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1.5px solid rgba(255,255,255,0.08);
        background: linear-gradient(135deg, rgba(255,255,255,0.04), rgba(255,255,255,0.01));
        backdrop-filter: blur(12px);
    }
    .gejala-card:hover {
        border-color: rgba(34,197,94,0.4);
        background: linear-gradient(135deg, rgba(34,197,94,0.08), rgba(16,185,129,0.03));
        transform: translateY(-2px);
        box-shadow: 0 8px 32px rgba(34,197,94,0.12);
    }
    .gejala-card.selected {
        border-color: rgba(34,197,94,0.7);
        background: linear-gradient(135deg, rgba(34,197,94,0.15), rgba(16,185,129,0.08));
        box-shadow: 0 0 0 1px rgba(34,197,94,0.3), 0 8px 32px rgba(34,197,94,0.18);
    }
    .gejala-card.selected .checkmark {
        opacity: 1;
        transform: scale(1);
    }
    .gejala-card.selected .gejala-icon {
        background: linear-gradient(135deg, #16a34a, #15803d);
    }
    .checkmark {
        opacity: 0;
        transform: scale(0.5);
        transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .gejala-icon {
        transition: all 0.25s ease;
        background: rgba(255,255,255,0.06);
    }

    /* ---- Counter Badge ---- */
    .counter-badge {
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .counter-badge.bump {
        animation: counterBump 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes counterBump {
        0%   { transform: scale(1); }
        50%  { transform: scale(1.3); }
        100% { transform: scale(1); }
    }

    /* ---- Upload Zone ---- */
    .upload-zone {
        border: 2px dashed rgba(255,255,255,0.12);
        transition: all 0.3s ease;
    }
    .upload-zone:hover,
    .upload-zone.drag-over {
        border-color: rgba(34,197,94,0.5);
        background: rgba(34,197,94,0.05);
    }
    .upload-zone.has-file {
        border-color: rgba(34,197,94,0.6);
        background: rgba(34,197,94,0.08);
    }

    /* ---- Diagnose Button ---- */
    .diagnose-btn {
        background: linear-gradient(135deg, #16a34a 0%, #15803d 50%, #166534 100%);
        background-size: 200% auto;
        transition: all 0.4s ease;
        position: relative;
        overflow: hidden;
    }
    .diagnose-btn::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, transparent, rgba(255,255,255,0.08), transparent);
        transform: translateX(-100%);
        transition: transform 0.5s ease;
    }
    .diagnose-btn:hover::before { transform: translateX(100%); }
    .diagnose-btn:hover {
        background-position: right center;
        box-shadow: 0 8px 40px rgba(22,163,74,0.45), 0 0 0 1px rgba(34,197,94,0.3);
        transform: translateY(-2px);
    }
    .diagnose-btn:active  { transform: translateY(0); }
    .diagnose-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* ---- Loading Overlay ---- */
    #loadingOverlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(6, 14, 6, 0.85);
        backdrop-filter: blur(8px);
    }
    #loadingOverlay.active { display: flex; }

    .loading-ring {
        width: 64px;
        height: 64px;
        border: 3px solid rgba(34,197,94,0.15);
        border-top-color: #22c55e;
        border-radius: 50%;
        animation: spin-smooth 0.8s linear infinite;
    }
    @keyframes spin-smooth { to { transform: rotate(360deg); } }

    /* ---- Section Fade In ---- */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .fade-in-up {
        animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        opacity: 0;
    }
    .delay-100 { animation-delay: 0.10s; }
    .delay-200 { animation-delay: 0.20s; }
    .delay-300 { animation-delay: 0.30s; }
    .delay-400 { animation-delay: 0.40s; }

    /* ---- Artikel Card ---- */
    .artikel-card { transition: all 0.3s ease; }
    .artikel-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 60px rgba(0,0,0,0.4);
    }

    /* ---- Artikel Image: explicit size so object-cover works ---- */
    .artikel-img-wrap {
        position: relative;
        width: 100%;
        height: 176px; /* 44 * 4px = 176px, same as h-44 */
        overflow: hidden;
        background: linear-gradient(135deg, rgba(20,83,45,0.4), rgba(6,78,59,0.2));
        flex-shrink: 0;
    }
    .artikel-img-wrap img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.5s ease;
    }
    .artikel-img-wrap img:hover { transform: scale(1.05); }
    .artikel-img-placeholder {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ---- Search Input ---- */
    .search-input:focus {
        outline: none;
        border-color: rgba(34,197,94,0.5);
        box-shadow: 0 0 0 3px rgba(34,197,94,0.1);
    }
</style>
@endpush

@section('content')

{{-- ============================================================
     LOADING OVERLAY
============================================================ --}}
<div id="loadingOverlay" class="items-center justify-center flex-col gap-6">
    <div class="glass-card rounded-2xl p-10 flex flex-col items-center gap-6 border border-white/10">
        <div class="loading-ring"></div>
        <div class="text-center">
            <p class="text-lg font-semibold text-white mb-1">Menganalisis Gejala...</p>
            <p class="text-sm text-gray-400">Sistem sedang menghitung Certainty Factor</p>
        </div>
        <div class="flex gap-1.5">
            @for ($i = 0; $i < 5; $i++)
            <div class="w-2 h-2 rounded-full bg-brand-500 animate-bounce" style="animation-delay: {{ $i * 0.12 }}s"></div>
            @endfor
        </div>
    </div>
</div>

{{-- ============================================================
     HERO SECTION
============================================================ --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-10">
    <div class="fade-in-up text-center mb-14">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass-card-light border border-brand-500/30 text-brand-400 text-xs font-semibold tracking-widest uppercase mb-6">
            <span class="w-1.5 h-1.5 rounded-full bg-brand-400 animate-pulse-slow"></span>
            Sistem Pakar Berbasis Certainty Factor
        </div>
        <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-bold text-white leading-tight mb-5">
            Diagnosis Penyakit<br>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 via-emerald-300 to-brand-500">
                Tanaman Cerdas
            </span>
        </h1>
        <p class="text-gray-400 text-lg max-w-2xl mx-auto leading-relaxed">
            Pilih gejala yang Anda amati pada tanaman, lalu biarkan sistem pakar kami menganalisis dan mendiagnosis penyakit secara akurat menggunakan algoritma <em class="text-brand-400 not-italic">Certainty Factor</em>.
        </p>
    </div>

    {{-- Stats Row --}}
    <div class="fade-in-up delay-100 grid grid-cols-3 gap-4 max-w-lg mx-auto mb-14">
        @foreach ([
            ['98%', 'Akurasi Sistem'],
            ['50+', 'Jenis Gejala'],
            ['15+', 'Penyakit Terdata'],
        ] as $stat)
        <div class="glass-card rounded-2xl p-4 text-center border border-white/5">
            <div class="font-display text-2xl font-bold text-brand-400 mb-1">{{ $stat[0] }}</div>
            <div class="text-xs text-gray-400">{{ $stat[1] }}</div>
        </div>
        @endforeach
    </div>
</section>

{{-- ============================================================
     DIAGNOSIS FORM
============================================================ --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
    <form action="{{ route('diagnosa.proses') }}" method="POST" enctype="multipart/form-data" id="diagnosaForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- ---- LEFT PANEL: Symptoms ---- --}}
            <div class="lg:col-span-2 space-y-6 fade-in-up delay-200">

                <div class="glass-card rounded-2xl p-6 border border-white/8">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-white flex items-center gap-2.5 mb-1">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-600 to-brand-800 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                Pilih Gejala Tanaman
                            </h2>
                            <p class="text-sm text-gray-400">Centang semua gejala yang Anda temukan pada tanaman</p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-xs text-gray-500 mb-0.5">Dipilih</p>
                                <div class="flex items-center gap-2">
                                    <span id="selectedCount" class="counter-badge font-display text-3xl font-bold text-brand-400">0</span>
                                    <span class="text-gray-500 text-sm">gejala</span>
                                </div>
                            </div>
                            <button type="button" id="clearAllBtn"
                                    class="hidden px-3 py-1.5 rounded-lg text-xs font-medium text-red-400 border border-red-500/30 hover:bg-red-500/10 transition-all">
                                Reset Semua
                            </button>
                        </div>
                    </div>

                    <div class="mt-5 relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input type="text" id="gejalaSearch" placeholder="Cari gejala..."
                               class="search-input w-full pl-10 pr-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-sm text-gray-200 placeholder-gray-500 transition-all">
                    </div>
                </div>

                {{-- Symptoms Grid --}}
                <div id="gejalaGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @forelse ($gejalas as $gejala)
                    <label class="gejala-card rounded-xl p-4 flex items-start gap-4 cursor-pointer select-none"
                           data-nama="{{ strtolower($gejala->nama_gejala) }}">
                        <input type="checkbox" name="gejala[]" value="{{ $gejala->kode }}"
                               class="gejala-checkbox sr-only"
                               {{ in_array($gejala->kode, old('gejala', [])) ? 'checked' : '' }}>
                        <div class="gejala-icon flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center mt-0.5">
                            <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-100 leading-snug">{{ $gejala->nama_gejala }}</p>
                            @if(!empty($gejala->deskripsi))
                                <p class="text-xs text-gray-500 mt-1 leading-relaxed line-clamp-2">{{ $gejala->deskripsi }}</p>
                            @endif
                            <span class="inline-block mt-1.5 text-xs font-mono text-gray-600">{{ $gejala->kode }}</span>
                        </div>
                        <div class="checkmark flex-shrink-0 w-5 h-5 rounded-full bg-brand-500 flex items-center justify-center mt-0.5">
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </label>
                    @empty
                    <div class="sm:col-span-2 py-16 text-center glass-card rounded-2xl border border-white/5">
                        <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-gray-400 font-medium mb-1">Data gejala belum tersedia</p>
                        <p class="text-gray-600 text-sm">Jalankan <code class="text-brand-700">php artisan db:seed</code> terlebih dahulu.</p>
                    </div>
                    @endforelse

                    <div id="noSearchResult" class="sm:col-span-2 py-10 text-center hidden">
                        <p class="text-gray-500 text-sm">Tidak ada gejala yang cocok dengan "<span id="searchQuery" class="text-gray-300"></span>"</p>
                    </div>
                </div>
            </div>

            {{-- ---- RIGHT PANEL: Upload & Action ---- --}}
            <div class="space-y-6 fade-in-up delay-300">

                {{-- Upload Image --}}
                <div class="glass-card rounded-2xl p-6 border border-white/8">
                    <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Foto Tanaman
                        <span class="text-gray-500 text-xs font-normal ml-1">(opsional)</span>
                    </h3>

                    <div id="uploadZone" class="upload-zone rounded-xl p-6 text-center cursor-pointer"
                         onclick="document.getElementById('gambarInput').click()">
                        <div id="uploadDefault">
                            <div class="w-14 h-14 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-7 h-7 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-300 mb-1">Klik atau seret foto ke sini</p>
                            <p class="text-xs text-gray-500">PNG, JPG, WEBP — maks. 5 MB</p>
                        </div>
                        <div id="uploadPreview" class="hidden">
                            <img id="previewImg" src="" alt="Preview"
                                 class="w-full rounded-lg mb-3"
                                 style="height:160px;object-fit:cover;display:block;">
                            <p id="previewName" class="text-xs text-brand-400 truncate"></p>
                            <button type="button" onclick="clearUpload(event)"
                                    class="mt-2 text-xs text-red-400 hover:text-red-300 transition-colors">
                                Hapus foto
                            </button>
                        </div>
                    </div>
                    <input type="file" id="gambarInput" name="gambar"
                           accept="image/jpeg,image/png,image/webp" class="hidden">
                    @error('gambar')
                        <p class="mt-2 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Panduan --}}
                <div id="panduan" class="glass-card rounded-2xl p-6 border border-white/8">
                    <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Panduan Penggunaan
                    </h3>
                    <ol class="space-y-3">
                        @foreach ([
                            ['1', 'Amati tanaman secara seksama'],
                            ['2', 'Centang semua gejala yang terlihat'],
                            ['3', 'Unggah foto tanaman (opsional)'],
                            ['4', 'Klik tombol "Diagnosa Sekarang"'],
                            ['5', 'Baca hasil dan rekomendasi penanganan'],
                        ] as $step)
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-brand-900/60 border border-brand-700/50 flex items-center justify-center text-xs font-bold text-brand-400">{{ $step[0] }}</span>
                            <span class="text-sm text-gray-300 pt-0.5">{{ $step[1] }}</span>
                        </li>
                        @endforeach
                    </ol>
                </div>

                {{-- Diagnose Button --}}
                <div class="fade-in-up delay-400">
                    <button type="submit" id="diagnoseBtn"
                            class="diagnose-btn w-full py-4 px-6 rounded-2xl text-white font-bold text-base flex items-center justify-center gap-3 shadow-xl shadow-brand-900/40">
                        <span id="btnIcon">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </span>
                        <span id="btnText">Diagnosa Sekarang</span>
                    </button>
                    <p class="text-center text-xs text-gray-500 mt-3">Pilih minimal 1 gejala untuk memulai diagnosis</p>
                    @error('gejala')
                        <p class="text-center text-xs text-red-400 mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

        </div>
    </form>
</section>

{{-- ============================================================
     ARTIKEL SECTION
============================================================ --}}
@if(isset($artikels) && $artikels->isNotEmpty())
<section id="artikel" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
    <div class="mb-10 text-center fade-in-up">
        <h2 class="font-display text-3xl font-bold text-white mb-2">Artikel Agronomi</h2>
        <p class="text-gray-400 text-sm">Informasi terkini seputar kesehatan dan perawatan tanaman</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach ($artikels as $idx => $artikel)
        <article class="artikel-card glass-card rounded-2xl overflow-hidden border border-white/5 fade-in-up flex flex-col"
                 style="animation-delay: {{ $idx * 0.1 }}s">

            {{-- Image wrapper — explicit pixel height prevents h-full:0 collapse --}}
            <div class="artikel-img-wrap">
                @if(!empty($artikel->gambar))
                    <img
                        src="{{ Storage::url($artikel->gambar) }}"
                        alt="{{ $artikel->judul }}"
                        loading="lazy"
                        onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                    >
                    {{-- Fallback shown if image fails to load --}}
                    <div class="artikel-img-placeholder" style="display:none;">
                        <svg class="w-16 h-16 text-brand-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @else
                    {{-- No image in DB — show placeholder --}}
                    <div class="artikel-img-placeholder">
                        <svg class="w-16 h-16 text-brand-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                @endif
            </div>

            <div class="p-5 flex flex-col flex-1">
                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-brand-900/60 text-brand-400 border border-brand-700/40 mb-3 self-start">
                    {{ $artikel->kategori ?? 'Agronomi' }}
                </span>
                <h3 class="font-bold text-white text-base leading-snug mb-2 line-clamp-2">
                    {{ $artikel->judul }}
                </h3>
                <p class="text-sm text-gray-400 line-clamp-3 leading-relaxed mb-4 flex-1">
                    {{ $artikel->ringkasan ?? Str::limit(strip_tags($artikel->konten ?? ''), 120) }}
                </p>
                <div class="flex items-center justify-between text-xs text-gray-500 mt-auto">
                    <span>{{ optional($artikel->created_at)->format('d M Y') ?? '-' }}</span>
                    <span class="text-gray-600">AgroPakar</span>
                </div>
            </div>
        </article>
        @endforeach
    </div>
</section>
@endif

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const form           = document.getElementById('diagnosaForm');
    const btnText        = document.getElementById('btnText');
    const btnIcon        = document.getElementById('btnIcon');
    const diagnoseBtn    = document.getElementById('diagnoseBtn');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const selectedCount  = document.getElementById('selectedCount');
    const clearAllBtn    = document.getElementById('clearAllBtn');
    const gejalaSearch   = document.getElementById('gejalaSearch');
    const gejalaGrid     = document.getElementById('gejalaGrid');
    const noSearchResult = document.getElementById('noSearchResult');
    const searchQuery    = document.getElementById('searchQuery');
    const uploadZone     = document.getElementById('uploadZone');
    const gambarInput    = document.getElementById('gambarInput');
    const uploadDefault  = document.getElementById('uploadDefault');
    const uploadPreview  = document.getElementById('uploadPreview');
    const previewImg     = document.getElementById('previewImg');
    const previewName    = document.getElementById('previewName');

    // Restore checked state after validation failure (old input)
    let count = 0;
    document.querySelectorAll('.gejala-checkbox:checked').forEach(cb => {
        cb.closest('.gejala-card')?.classList.add('selected');
        count++;
    });
    updateCounter(count);

    // Symptom card toggle
    document.querySelectorAll('.gejala-card').forEach(card => {
        card.addEventListener('click', e => {
            if (e.target.closest('button')) return;
            const cb = card.querySelector('.gejala-checkbox');
            if (!cb) return;
            cb.checked = !cb.checked;
            card.classList.toggle('selected', cb.checked);
            updateCounter(document.querySelectorAll('.gejala-checkbox:checked').length);
        });
    });

    function updateCounter(n) {
        selectedCount.textContent = n;
        selectedCount.classList.remove('bump');
        void selectedCount.offsetWidth;
        selectedCount.classList.add('bump');
        clearAllBtn?.classList.toggle('hidden', n === 0);
    }

    // Clear all
    clearAllBtn?.addEventListener('click', () => {
        document.querySelectorAll('.gejala-checkbox:checked').forEach(cb => {
            cb.checked = false;
            cb.closest('.gejala-card')?.classList.remove('selected');
        });
        updateCounter(0);
    });

    // Search / filter
    gejalaSearch?.addEventListener('input', () => {
        const q = gejalaSearch.value.trim().toLowerCase();
        let visible = 0;
        gejalaGrid?.querySelectorAll('.gejala-card').forEach(card => {
            const match = !q || (card.dataset.nama || '').includes(q);
            card.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        if (noSearchResult) {
            noSearchResult.classList.toggle('hidden', visible > 0);
            if (searchQuery) searchQuery.textContent = q;
        }
    });

    // Image upload — preview
    gambarInput?.addEventListener('change', () => {
        const file = gambarInput.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran gambar melebihi 5 MB. Silakan pilih file yang lebih kecil.');
            gambarInput.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = e => {
            previewImg.src           = e.target.result;
            previewName.textContent  = file.name;
            uploadDefault.classList.add('hidden');
            uploadPreview.classList.remove('hidden');
            uploadZone.classList.add('has-file');
        };
        reader.readAsDataURL(file);
    });

    // Drag & drop
    uploadZone?.addEventListener('dragover',  e => { e.preventDefault(); uploadZone.classList.add('drag-over'); });
    uploadZone?.addEventListener('dragleave', ()  => uploadZone.classList.remove('drag-over'));
    uploadZone?.addEventListener('drop', e => {
        e.preventDefault();
        uploadZone.classList.remove('drag-over');
        const file = e.dataTransfer.files[0];
        if (file?.type.startsWith('image/')) {
            const dt = new DataTransfer();
            dt.items.add(file);
            gambarInput.files = dt.files;
            gambarInput.dispatchEvent(new Event('change'));
        }
    });

    window.clearUpload = function(e) {
        e.stopPropagation();
        gambarInput.value = '';
        uploadDefault.classList.remove('hidden');
        uploadPreview.classList.add('hidden');
        uploadZone.classList.remove('has-file');
        previewImg.src = '';
    };

    // Submit with loading state
    form?.addEventListener('submit', e => {
        if (document.querySelectorAll('.gejala-checkbox:checked').length === 0) {
            e.preventDefault();
            alert('Pilih minimal satu gejala sebelum melakukan diagnosis.');
            return;
        }
        loadingOverlay.classList.add('active');
        diagnoseBtn.disabled = true;
        btnText.textContent  = 'Menganalisis...';
        btnIcon.innerHTML    = `<svg class="w-5 h-5" style="animation:spin-smooth 0.8s linear infinite" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>`;
    });

    // Intersection observer for fade-in animations
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting)
                entry.target.style.animationPlayState = 'running';
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.fade-in-up').forEach(el => {
        el.style.animationPlayState = 'paused';
        observer.observe(el);
    });
})();
</script>
@endpush