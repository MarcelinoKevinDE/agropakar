@extends('layouts.app')

@section('title', 'Hasil Diagnosis — AgroPakar')
@section('meta_description', 'Hasil diagnosis penyakit tanaman berdasarkan gejala yang dipilih menggunakan metode Certainty Factor.')

@push('styles')
<style>
    /* ---- CF Progress Bar ---- */
    .cf-bar-track {
        height: 10px;
        border-radius: 9999px;
        background: rgba(255,255,255,0.06);
        overflow: hidden;
    }
    .cf-bar-fill {
        height: 100%;
        border-radius: 9999px;
        width: 0%;
        transition: width 1.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .cf-fill-emerald { background: linear-gradient(90deg, #10b981, #34d399); box-shadow: 0 0 12px rgba(52,211,153,0.4); }
    .cf-fill-green   { background: linear-gradient(90deg, #22c55e, #4ade80); box-shadow: 0 0 12px rgba(74,222,128,0.35); }
    .cf-fill-yellow  { background: linear-gradient(90deg, #eab308, #facc15); box-shadow: 0 0 12px rgba(234,179,8,0.35); }
    .cf-fill-orange  { background: linear-gradient(90deg, #f97316, #fb923c); box-shadow: 0 0 12px rgba(249,115,22,0.35); }
    .cf-fill-red     { background: linear-gradient(90deg, #ef4444, #f87171); box-shadow: 0 0 12px rgba(239,68,68,0.35); }

    /* ---- Result Card ---- */
    .result-card {
        transition: all 0.35s ease;
        animation: slideInCard 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        opacity: 0;
    }
    @keyframes slideInCard {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .result-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 24px 64px rgba(0,0,0,0.3);
    }

    /* ---- Primary result (rank 1) special styling ---- */
    .result-primary {
        border-color: rgba(34,197,94,0.35) !important;
        background: linear-gradient(135deg, rgba(34,197,94,0.10), rgba(16,185,129,0.05)) !important;
    }
    .result-primary::before {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        padding: 1px;
        background: linear-gradient(135deg, rgba(34,197,94,0.5), transparent, rgba(16,185,129,0.3));
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: xor;
        mask-composite: exclude;
        pointer-events: none;
    }

    /* ---- Gejala Pill ---- */
    .gejala-pill {
        animation: fadeInPill 0.3s ease forwards;
        opacity: 0;
    }
    @keyframes fadeInPill {
        to { opacity: 1; }
    }

    /* ---- Print ---- */
    @media print {
        nav, footer, .no-print { display: none !important; }
        body { background: white; color: black; }
        .glass-card { background: #f9f9f9; border: 1px solid #ddd; }
    }
</style>
@endpush

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    {{-- ---- Header ---- --}}
    <div class="mb-10 text-center">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass-card-light border border-brand-500/30 text-brand-400 text-xs font-semibold tracking-widest uppercase mb-5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Diagnosis Selesai
        </div>
        <h1 class="font-display text-4xl font-bold text-white mb-2">Hasil Diagnosis Penyakit</h1>
        <p class="text-gray-400 text-sm">{{ $timestamp ?? now()->format('d M Y, H:i') }} WIB · Metode: <em class="text-brand-400 not-italic">Certainty Factor</em></p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- ---- LEFT: Result Cards ---- --}}
        <div class="lg:col-span-2 space-y-5">

            @if (empty($hasilDiagnosa))
            {{-- Empty State --}}
            <div class="glass-card rounded-2xl p-16 text-center border border-white/5">
                <svg class="w-20 h-20 text-gray-600 mx-auto mb-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-xl font-bold text-gray-300 mb-2">Tidak Ada Hasil</h3>
                <p class="text-gray-500 text-sm mb-6">
                    Kombinasi gejala yang dipilih tidak cocok dengan penyakit yang ada di basis pengetahuan.
                    Coba pilih lebih banyak gejala.
                </p>
                <a href="{{ route('diagnosa.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold transition-all">
                    Ulangi Diagnosis
                </a>
            </div>

            @else

            {{-- Result Cards --}}
            @foreach ($hasilDiagnosa as $idx => $hasil)
            @php
                $isPrimary = $idx === 0;
                $delay     = $idx * 120;
                $color     = $hasil['color'] ?? 'green';
            @endphp

            <div class="result-card relative glass-card rounded-2xl p-6 border border-white/8 {{ $isPrimary ? 'result-primary' : '' }}"
                 style="animation-delay: {{ $delay }}ms">

                {{-- Rank Badge --}}
                <div class="flex items-start justify-between gap-4 mb-5">
                    <div class="flex items-start gap-4">
                        {{-- Rank --}}
                        <div class="flex-shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center font-display font-bold text-lg
                                    {{ $isPrimary ? 'bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-lg shadow-brand-900/40' : 'bg-white/5 text-gray-400' }}">
                            #{{ $idx + 1 }}
                        </div>

                        <div>
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <h2 class="text-lg font-bold text-white">
                                    {{ $hasil['penyakit']->nama_penyakit ?? 'Penyakit Tidak Diketahui' }}
                                </h2>
                                @if($isPrimary)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-500/20 text-brand-300 border border-brand-500/30">
                                    Diagnosis Utama
                                </span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 font-mono">
                                {{ $hasil['penyakit']->kode ?? '-' }}
                            </p>
                        </div>
                    </div>

                    {{-- CF Percentage --}}
                    <div class="flex-shrink-0 text-right">
                        <div class="font-display text-3xl font-bold text-{{ $color }}-400">
                            {{ $hasil['cf_persen'] }}%
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">Keyakinan</div>
                    </div>
                </div>

                {{-- CF Bar --}}
                <div class="mb-5">
                    <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
                        <span>Tingkat Keyakinan: <strong class="text-{{ $color }}-400">{{ $hasil['level'] }}</strong></span>
                        <span>CF = <code class="text-gray-300">{{ number_format($hasil['cf'], 4) }}</code></span>
                    </div>
                    <div class="cf-bar-track">
                        <div class="cf-bar-fill cf-fill-{{ $color }}" data-width="{{ $hasil['cf_persen'] }}"></div>
                    </div>
                </div>

                {{-- Disease Description --}}
                @if(isset($hasil['penyakit']->deskripsi) && $hasil['penyakit']->deskripsi)
                <div class="mb-5 p-4 rounded-xl bg-white/3 border border-white/5">
                    <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Deskripsi Penyakit</h4>
                    <p class="text-sm text-gray-300 leading-relaxed">{{ $hasil['penyakit']->deskripsi }}</p>
                </div>
                @endif

                {{-- Treatment / Penanganan --}}
                @if(isset($hasil['penyakit']->penanganan) && $hasil['penyakit']->penanganan)
                <div class="mb-5 p-4 rounded-xl bg-brand-950/40 border border-brand-900/50">
                    <h4 class="text-xs font-semibold text-brand-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Rekomendasi Penanganan
                    </h4>
                    <p class="text-sm text-gray-300 leading-relaxed">{{ $hasil['penyakit']->penanganan }}</p>
                </div>
                @endif

                {{-- Pencegahan --}}
                @if(isset($hasil['penyakit']->pencegahan) && $hasil['penyakit']->pencegahan)
                <div class="p-4 rounded-xl bg-emerald-950/30 border border-emerald-900/40">
                    <h4 class="text-xs font-semibold text-emerald-400 uppercase tracking-wider mb-2 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                        Pencegahan
                    </h4>
                    <p class="text-sm text-gray-300 leading-relaxed">{{ $hasil['penyakit']->pencegahan }}</p>
                </div>
                @endif

            </div>
            @endforeach

            @endif
        </div>

        {{-- ---- RIGHT PANEL: Summary ---- --}}
        <div class="space-y-6">

            {{-- Upload Preview --}}
            @if($gambarPath)
            <div class="glass-card rounded-2xl overflow-hidden border border-white/8">
                <div class="p-4 border-b border-white/5">
                    <h3 class="text-sm font-semibold text-white">Foto Tanaman</h3>
                </div>
                <img src="{{ Storage::url($gambarPath) }}" alt="Foto Tanaman"
                     class="w-full h-56 object-cover">
            </div>
            @endif

            {{-- Gejala Summary --}}
            <div class="glass-card rounded-2xl p-6 border border-white/8">
                <h3 class="text-base font-bold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Gejala Dipilih
                    <span class="ml-auto text-brand-400 font-display text-xl">{{ count($gejalaTerpilih ?? []) }}</span>
                </h3>

                @if(!empty($gejalaTerpilih) && count($gejalaTerpilih) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach ($gejalaTerpilih as $idx => $gejala)
                    <span class="gejala-pill inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-brand-900/50 text-brand-300 border border-brand-800/60"
                          style="animation-delay: {{ $idx * 60 }}ms">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand-400 flex-shrink-0"></span>
                        {{ $gejala['nama_gejala'] }}
                    </span>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-500">Tidak ada gejala yang tercatat.</p>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="glass-card rounded-2xl p-5 border border-white/8 space-y-3 no-print">
                <a href="{{ route('diagnosa.index') }}"
                   class="w-full flex items-center justify-center gap-2 py-3 px-5 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold transition-all shadow-lg shadow-brand-900/30 hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Diagnosis Ulang
                </a>
                <a href="{{ route('diagnosa.reset') }}"
                   class="w-full flex items-center justify-center gap-2 py-3 px-5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-sm font-semibold transition-all border border-white/8">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Mulai dari Awal
                </a>
                <button onclick="window.print()"
                        class="w-full flex items-center justify-center gap-2 py-3 px-5 rounded-xl bg-white/5 hover:bg-white/10 text-gray-300 text-sm font-semibold transition-all border border-white/8">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Hasil
                </button>
            </div>

            {{-- Disclaimer --}}
            <div class="glass-card rounded-2xl p-5 border border-yellow-800/30 bg-yellow-950/20">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="text-xs font-semibold text-yellow-400 mb-1">Catatan Penting</p>
                        <p class="text-xs text-yellow-600/80 leading-relaxed">
                            Hasil diagnosis ini merupakan estimasi berbasis sistem pakar dan tidak menggantikan konsultasi dengan ahli pertanian atau penyuluh lapangan.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Animate CF bars on load
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(() => {
            document.querySelectorAll('.cf-bar-fill[data-width]').forEach(bar => {
                bar.style.width = bar.dataset.width + '%';
            });
        }, 300);
    });
</script>
@endpush