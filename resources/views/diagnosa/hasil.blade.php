@extends('layouts.app')

@section('title', 'Hasil Diagnosis — AgroPakar')

{{-- =========================================================================
     PAGE-LEVEL STYLES
     All classes prefixed with "hasil-" or reuse existing "nb-" classes.
     ========================================================================= --}}
@push('styles')
<style>
    /* ── Hero banner ──────────────────────────────────────────────────────── */
    .hasil-hero {
        background: var(--black);
        border-bottom: var(--border);
        padding: 2.25rem 0 2rem;
    }
    .hasil-hero-eyebrow {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.45);
        margin-bottom: 0.4rem;
    }
    .hasil-hero-title {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 2.6rem;
        letter-spacing: 0.05em;
        color: var(--white);
        line-height: 1;
    }
    .hasil-hero-title span { color: var(--yellow); }
    .hasil-hero-meta {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.72rem;
        color: rgba(255,255,255,0.45);
        margin-top: 0.5rem;
        letter-spacing: 0.04em;
    }

    /* ── Page layout ─────────────────────────────────────────────────────── */
    .hasil-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 860px) {
        .hasil-grid { grid-template-columns: 1fr; }
    }

    /* ── Generic section block ───────────────────────────────────────────── */
    .hasil-block { margin-bottom: 1.75rem; }

    .hasil-block-header {
        background: var(--black);
        color: var(--yellow);
        padding: 0.6rem 1rem;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1.05rem;
        letter-spacing: 0.08em;
        border: var(--border);
        border-bottom: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .hasil-block-body {
        border: var(--border);
        background: var(--white);
    }

    /* ── TOP RESULT CARD ─────────────────────────────────────────────────── */
    .top-result-card {
        border: var(--border);
        box-shadow: var(--shadow-lg, 8px 8px 0 var(--black));
        margin-bottom: 1.75rem;
        overflow: hidden;
    }

    .top-result-banner {
        background: var(--yellow);
        border-bottom: var(--border);
        padding: 0.55rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .top-result-banner-label {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1rem;
        letter-spacing: 0.1em;
        color: var(--black);
    }
    .top-rank-pill {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        background: var(--black);
        color: var(--yellow);
        padding: 0.2rem 0.55rem;
        border: 2px solid var(--black);
    }

    .top-result-body {
        background: var(--black);
        color: var(--white);
        padding: 1.75rem 1.5rem;
        display: flex;
        gap: 2rem;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .top-result-pct {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 5.5rem;
        line-height: 1;
        color: var(--yellow);
        flex-shrink: 0;
    }

    .top-result-info { flex: 1; min-width: 180px; }

    .top-result-name {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1.8rem;
        letter-spacing: 0.04em;
        line-height: 1.1;
        color: var(--white);
        margin-bottom: 0.4rem;
    }

    .top-result-cf {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.72rem;
        color: rgba(255,255,255,0.5);
        margin-bottom: 0.75rem;
        letter-spacing: 0.06em;
    }

    .level-pill {
        display: inline-block;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 0.25rem 0.65rem;
        border: 2px solid;
    }

    .level-sangat-tinggi { background: #00C060; border-color: #00C060; color: #003a1c; }
    .level-tinggi        { background: #1A5CFF; border-color: #1A5CFF; color: #fff; }
    .level-sedang        { background: var(--yellow); border-color: var(--yellow); color: var(--black); }
    .level-rendah        { background: #ccc; border-color: #999; color: #333; }

    .top-result-solusi {
        padding: 1rem 1.5rem;
        background: #f5f5ed;
        border-top: var(--border);
    }
    .top-result-solusi-label {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #888;
        margin-bottom: 0.35rem;
    }
    .top-result-solusi-text {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.82rem;
        line-height: 1.75;
        color: var(--black);
    }

    /* ── CF progress bar ─────────────────────────────────────────────────── */
    .cf-bar-track {
        height: 18px;
        background: #e0e0d8;
        border: 2px solid var(--black);
        overflow: hidden;
        flex: 1;
    }
    .cf-bar-fill {
        height: 100%;
        width: 0; /* set via JS / inline style for animation */
        border-right: 2px solid var(--black);
        transition: width 1.1s cubic-bezier(.4, 0, .2, 1);
    }
    .fill-sangat-tinggi { background: #00C060; }
    .fill-tinggi        { background: #1A5CFF; }
    .fill-sedang        { background: var(--yellow); }
    .fill-rendah        { background: #aaa; }

    /* ── Candidate rows ───────────────────────────────────────────────────── */
    .candidate-row {
        border-bottom: 2px solid #e8e8e0;
        background: var(--white);
        overflow: hidden;
    }
    .candidate-row:last-child { border-bottom: none; }

    .candidate-head {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.85rem 1rem;
        cursor: pointer;
        user-select: none;
        transition: background 0.12s;
    }
    .candidate-head:hover { background: #fffbe6; }

    .candidate-rank {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1.4rem;
        color: #ccc;
        min-width: 1.8rem;
        flex-shrink: 0;
    }
    .candidate-rank.is-top { color: var(--yellow); }

    .candidate-name {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.82rem;
        font-weight: 700;
        flex: 1;
        line-height: 1.4;
    }

    .candidate-pct {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1.5rem;
        min-width: 3.5rem;
        text-align: right;
        flex-shrink: 0;
    }

    .candidate-chevron {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.75rem;
        color: #aaa;
        flex-shrink: 0;
        transition: transform 0.2s;
    }
    .candidate-chevron.open { transform: rotate(180deg); }

    .candidate-bar-row {
        padding: 0 1rem 0.75rem calc(1rem + 1.8rem + 0.75rem);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .candidate-body {
        display: none;
        border-top: 2px solid #e8e8e0;
        background: #fafaf2;
    }
    .candidate-body.is-open { display: block; }

    .candidate-solusi {
        padding: 0.85rem 1rem 0.85rem calc(1rem + 1.8rem + 0.75rem);
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.78rem;
        line-height: 1.75;
        color: #333;
    }
    .candidate-solusi-label {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #888;
        margin-bottom: 0.3rem;
    }

    /* ── CF Steps accordion ───────────────────────────────────────────────── */
    .cf-steps-toggle {
        width: 100%;
        padding: 0.55rem 1rem 0.55rem calc(1rem + 1.8rem + 0.75rem);
        background: #f0f0e8;
        border: none;
        border-top: 2px solid #e8e8e0;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        text-align: left;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background 0.1s;
    }
    .cf-steps-toggle:hover { background: #e6e6de; }
    .cf-steps-toggle-arrow { transition: transform 0.2s; font-size: 0.7rem; }
    .cf-steps-toggle-arrow.open { transform: rotate(180deg); }

    .cf-steps-panel {
        display: none;
        border-top: 2px solid #e8e8e0;
    }
    .cf-steps-panel.is-open { display: block; }

    .cf-step-row {
        display: grid;
        grid-template-columns: 2rem 1fr;
        gap: 0.5rem;
        padding: 0.65rem 1rem;
        border-bottom: 1px solid #e8e8e0;
        align-items: start;
    }
    .cf-step-row:last-child { border-bottom: none; }

    .cf-step-num {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1.2rem;
        color: #FF3B2F;
        line-height: 1;
        text-align: center;
    }
    .cf-step-body {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.75rem;
        line-height: 1.6;
    }
    .cf-step-gejala { font-weight: 700; color: var(--black); }
    .cf-step-formula {
        font-size: 0.7rem;
        color: #666;
        margin-top: 0.2rem;
        padding: 0.3rem 0.5rem;
        background: var(--white);
        border-left: 3px solid var(--yellow);
    }
    .cf-step-result {
        font-size: 0.68rem;
        color: #888;
        margin-top: 0.15rem;
    }

    /* ── No rule found ────────────────────────────────────────────────────── */
    .no-rule-card {
        border: var(--border);
        box-shadow: var(--shadow);
        background: var(--white);
        text-align: center;
        padding: 3.5rem 2rem;
    }
    .no-rule-icon {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 4rem;
        color: #ddd;
        line-height: 1;
        margin-bottom: 0.75rem;
    }
    .no-rule-title {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1.6rem;
        letter-spacing: 0.06em;
        margin-bottom: 0.6rem;
    }
    .no-rule-text {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.8rem;
        color: #777;
        line-height: 1.75;
        max-width: 420px;
        margin: 0 auto 1.5rem;
    }

    /* ── Right panel: summary sidebar ────────────────────────────────────── */
    .sidebar-panel {
        border: var(--border);
        box-shadow: var(--shadow);
        position: sticky;
        top: 76px;
    }
    .sidebar-header {
        background: var(--black);
        color: var(--yellow);
        padding: 0.6rem 1rem;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1rem;
        letter-spacing: 0.08em;
        border-bottom: var(--border);
    }
    .sidebar-body { background: var(--white); }

    .sidebar-info-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 0.6rem 1rem;
        border-bottom: 2px solid #e8e8e0;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.75rem;
        gap: 0.5rem;
    }
    .sidebar-info-row:last-child { border-bottom: none; }
    .sidebar-info-label {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #888;
        flex-shrink: 0;
    }
    .sidebar-info-value {
        font-weight: 600;
        text-align: right;
        color: var(--black);
        word-break: break-word;
    }

    .gejala-chip-list {
        padding: 0.75rem;
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
        max-height: 360px;
        overflow-y: auto;
        background: var(--white);
    }
    .gejala-chip-list::-webkit-scrollbar { width: 4px; }
    .gejala-chip-list::-webkit-scrollbar-thumb { background: var(--black); }

    .gejala-chip {
        padding: 0.45rem 0.7rem;
        border: 2px solid var(--black);
        background: var(--paper);
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.74rem;
        line-height: 1.4;
    }
    .gejala-chip-code {
        font-size: 0.63rem;
        color: #888;
        display: block;
        margin-top: 0.1rem;
    }

    /* ── Action buttons ──────────────────────────────────────────────────── */
    .action-row {
        display: flex;
        gap: 0.65rem;
        flex-wrap: wrap;
        margin-top: 2rem;
    }

    /* ── Print styles ────────────────────────────────────────────────────── */
    @media print {
        .hasil-hero { padding: 1rem 0; }
        .sidebar-panel { position: static; box-shadow: none; }
        .action-row { display: none; }
        .candidate-body { display: block !important; }
        .cf-steps-panel { display: block !important; }
    }
</style>
@endpush

@section('content')

{{-- =========================================================================
     HERO
     ========================================================================= --}}
<div class="hasil-hero">
    <div class="nb-container">
        <div class="hasil-hero-eyebrow">SISTEM PAKAR AGRO &mdash; HASIL DIAGNOSIS</div>
        <h1 class="hasil-hero-title">
            @if ($noRule)
                TIDAK ADA <span>HASIL</span>
            @else
                HASIL <span>DIAGNOSIS</span>
            @endif
        </h1>
        <p class="hasil-hero-meta">
            {{ now()->format('d M Y, H:i') }} WIB
            @if ($namaUser)
                &nbsp;&mdash;&nbsp; PASIEN: <strong style="color:rgba(255,255,255,.75)">{{ strtoupper($namaUser) }}</strong>
            @endif
        </p>
    </div>
</div>

{{-- =========================================================================
     MAIN CONTENT
     ========================================================================= --}}
<div class="nb-container py-5">
    <div class="hasil-grid">

        {{-- ================================================================
             LEFT COLUMN — diagnosis results
             ================================================================ --}}
        <div>

            {{-- ── NO RULE FOUND branch ──────────────────────────────────── --}}
            @if ($noRule || empty($hasil))

                <div class="no-rule-card">
                    <div class="no-rule-icon">?</div>
                    <div class="no-rule-title">TIDAK ADA PENYAKIT YANG COCOK</div>
                    <p class="no-rule-text">
                        Kombinasi gejala yang Anda pilih tidak sesuai dengan aturan yang
                        tersedia dalam basis pengetahuan sistem.<br><br>
                        Coba pilih gejala yang lebih lengkap atau berbeda.
                    </p>
                    <a href="{{ route('diagnosa.index') }}" class="nb-btn">
                        COBA LAGI
                    </a>
                </div>

            @else
            {{-- ── RESULTS FOUND branch ─────────────────────────────────── --}}

                {{-- ── TOP RESULT CARD ─────────────────────────────────────── --}}
                @php
                    /*
                     * The controller already sorts $hasil by CF descending, but we
                     * defensively re-sort here so the view is correct regardless of
                     * controller ordering guarantees.
                     */
                    $sortedHasil = collect($hasil)->sortByDesc('cf')->values();
                    $top         = $sortedHasil->first();

                    /*
                     * Derive a CSS-safe class suffix from the level string.
                     * E.g. "SANGAT TINGGI" → "sangat-tinggi"
                     */
                    $levelClass = fn(string $level): string =>
                        strtolower(str_replace(' ', '-', $level));
                @endphp

                <div class="top-result-card">

                    <div class="top-result-banner">
                        <span class="top-result-banner-label">DIAGNOSIS UTAMA</span>
                        <span class="top-rank-pill">#1 KEPERCAYAAN TERTINGGI</span>
                    </div>

                    <div class="top-result-body">

                        {{-- Large percentage display --}}
                        <div class="top-result-pct" aria-label="{{ $top['persen'] }} persen">
                            {{ number_format($top['persen'], 1) }}%
                        </div>

                        {{-- Name + CF value + level badge --}}
                        <div class="top-result-info">
                            <div class="top-result-name">{{ $top['nama_kerusakan'] }}</div>
                            <div class="top-result-cf">
                                NILAI CF = {{ number_format($top['cf'], 4) }}
                                &nbsp;&nbsp;|&nbsp;&nbsp;
                                METODE: CERTAINTY FACTOR
                            </div>
                            <span class="level-pill level-{{ $levelClass($top['level']) }}">
                                {{ $top['level'] }}
                            </span>
                        </div>

                    </div>

                    @if (!empty($top['solusi']))
                        <div class="top-result-solusi">
                            <div class="top-result-solusi-label">REKOMENDASI TINDAKAN</div>
                            <div class="top-result-solusi-text">{{ $top['solusi'] }}</div>
                        </div>
                    @endif

                </div>{{-- /top-result-card --}}

                {{-- ── ALL CANDIDATES (ranked) ─────────────────────────────── --}}
                <div class="hasil-block">

                    <div class="hasil-block-header">
                        <span>SEMUA KANDIDAT PENYAKIT (DIRANKING)</span>
                        <span style="font-family:'IBM Plex Mono',monospace; font-size:0.72rem;
                                     color:var(--yellow); opacity:0.7;">
                            {{ $sortedHasil->count() }} KANDIDAT
                        </span>
                    </div>

                    <div class="hasil-block-body">
                        @foreach ($sortedHasil as $index => $item)
                            @php
                                $kerusakanId  = $item['kerusakan_id'] ?? $index;
                                $lClass       = $levelClass($item['level']);
                                $isTop        = ($index === 0);
                                $stepsForItem = $cfSteps[$kerusakanId] ?? null;
                            @endphp

                            <div class="candidate-row">

                                {{-- ── Row header (always visible) ─────────── --}}
                                <div class="candidate-head"
                                     onclick="toggleCandidate('body-{{ $index }}')"
                                     role="button"
                                     aria-expanded="false"
                                     aria-controls="body-{{ $index }}"
                                     tabindex="0"
                                     onkeydown="if(event.key==='Enter'||event.key===' ')toggleCandidate('body-{{ $index }}')">

                                    <span class="candidate-rank {{ $isTop ? 'is-top' : '' }}">
                                        {{ $index + 1 }}
                                    </span>

                                    <span class="candidate-name">{{ $item['nama_kerusakan'] }}</span>

                                    <span class="level-pill level-{{ $lClass }}"
                                          style="font-size:0.62rem;">
                                        {{ $item['level'] }}
                                    </span>

                                    <span class="candidate-pct">
                                        {{ number_format($item['persen'], 1) }}%
                                    </span>

                                    <span class="candidate-chevron" id="chev-{{ $index }}">▼</span>
                                </div>

                                {{-- ── Progress bar ─────────────────────────── --}}
                                <div class="candidate-bar-row">
                                    <div class="cf-bar-track" aria-hidden="true">
                                        <div class="cf-bar-fill fill-{{ $lClass }}"
                                             data-width="{{ $item['persen'] }}%"
                                             style="width:0%;">
                                        </div>
                                    </div>
                                    <span style="font-family:'IBM Plex Mono',monospace;
                                                 font-size:0.7rem; color:#888; min-width:3rem;
                                                 text-align:right; flex-shrink:0;">
                                        CF = {{ number_format($item['cf'], 4) }}
                                    </span>
                                </div>

                                {{-- ── Collapsible body ─────────────────────── --}}
                                <div class="candidate-body {{ $isTop ? 'is-open' : '' }}"
                                     id="body-{{ $index }}">

                                    @if (!empty($item['solusi']))
                                        <div class="candidate-solusi">
                                            <div class="candidate-solusi-label">REKOMENDASI</div>
                                            {{ $item['solusi'] }}
                                        </div>
                                    @endif

                                    {{-- CF calculation steps toggle --}}
                                    @if (!empty($stepsForItem['steps']))
                                        <button type="button"
                                                class="cf-steps-toggle"
                                                onclick="toggleSteps('steps-{{ $index }}')"
                                                aria-expanded="false"
                                                aria-controls="steps-{{ $index }}">
                                            <span class="cf-steps-toggle-arrow"
                                                  id="sarrow-{{ $index }}">▼</span>
                                            LIHAT LANGKAH PERHITUNGAN CF
                                            ({{ count($stepsForItem['steps']) }} LANGKAH)
                                        </button>

                                        <div class="cf-steps-panel" id="steps-{{ $index }}">
                                            @foreach ($stepsForItem['steps'] as $si => $step)
                                                <div class="cf-step-row">
                                                    <div class="cf-step-num">
                                                        {{ str_pad($si + 1, 2, '0', STR_PAD_LEFT) }}
                                                    </div>
                                                    <div class="cf-step-body">
                                                        <div class="cf-step-gejala">
                                                            {{ $step['nama_gejala'] }}
                                                        </div>
                                                        <div class="cf-step-formula">
                                                            {{ $step['formula'] }}
                                                        </div>
                                                        <div class="cf-step-result">
                                                            MB = {{ $step['mb'] }}
                                                            &nbsp; MD = {{ $step['md'] }}
                                                            &nbsp; CF_rule = {{ $step['cf_rule'] }}
                                                            &nbsp;&rarr;&nbsp;
                                                            CF_baru = <strong>{{ $step['cf_new'] }}</strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                </div>{{-- /candidate-body --}}

                            </div>{{-- /candidate-row --}}
                        @endforeach
                    </div>{{-- /hasil-block-body --}}

                </div>{{-- /hasil-block --}}

            @endif
            {{-- end $noRule branch --}}

            {{-- ── ACTION BUTTONS (always shown) ──────────────────────────── --}}
            <div class="action-row">
                <a href="{{ route('diagnosa.index') }}" class="nb-btn nb-btn-black">
                    DIAGNOSIS BARU
                </a>
                <button type="button"
                        class="nb-btn nb-btn-outline"
                        onclick="window.print()"
                        style="cursor:pointer;">
                    CETAK / SIMPAN PDF
                </button>
            </div>

        </div>
        {{-- /left column --}}

        {{-- ================================================================
             RIGHT COLUMN — session summary sidebar
             ================================================================ --}}
        <div>
            <div class="sidebar-panel">

                {{-- Session info --}}
                <div class="sidebar-header">RINGKASAN SESI</div>
                <div class="sidebar-body">

                    <div class="sidebar-info-row">
                        <span class="sidebar-info-label">PENGGUNA</span>
                        <span class="sidebar-info-value">
                            {{ $namaUser ?: '—' }}
                        </span>
                    </div>

                    <div class="sidebar-info-row">
                        <span class="sidebar-info-label">WAKTU</span>
                        <span class="sidebar-info-value">
                            {{ now()->format('d M Y, H:i') }}
                        </span>
                    </div>

                    <div class="sidebar-info-row">
                        <span class="sidebar-info-label">GEJALA DIPILIH</span>
                        <span class="sidebar-info-value">
                            {{ $gejalaDipilih->count() }} GEJALA
                        </span>
                    </div>

                    @unless ($noRule || empty($hasil))
                        <div class="sidebar-info-row">
                            <span class="sidebar-info-label">KANDIDAT</span>
                            <span class="sidebar-info-value">
                                {{ count($hasil) }} PENYAKIT
                            </span>
                        </div>
                        <div class="sidebar-info-row">
                            <span class="sidebar-info-label">CF TERTINGGI</span>
                            <span class="sidebar-info-value">
                                {{ number_format($sortedHasil->first()['cf'], 4) }}
                            </span>
                        </div>
                    @endunless

                </div>

                {{-- Selected symptoms list --}}
                <div class="sidebar-header" style="border-top: var(--border);">
                    GEJALA YANG DIPILIH
                </div>

                <div class="gejala-chip-list">
                    @forelse ($gejalaDipilih as $g)
                        <div class="gejala-chip">
                            {{ $g->nama_gejala }}
                            <span class="gejala-chip-code">{{ $g->kode }}</span>
                        </div>
                    @empty
                        <div style="padding:1rem; text-align:center;
                                    font-family:'IBM Plex Mono',monospace;
                                    font-size:0.72rem; color:#aaa;">
                            TIDAK ADA GEJALA TERCATAT.
                        </div>
                    @endforelse
                </div>

                {{-- CF method reference box --}}
                <div style="border-top: var(--border); padding: 0.9rem 1rem;
                             background: var(--black);">
                    <div style="font-family:'Bebas Neue',sans-serif; font-size:0.95rem;
                                 letter-spacing:0.08em; color:var(--yellow); margin-bottom:0.5rem;">
                        RUMUS CF YANG DIGUNAKAN
                    </div>
                    <div style="font-family:'IBM Plex Mono',monospace; font-size:0.68rem;
                                 color:rgba(255,255,255,0.55); line-height:1.8;">
                        CF(rule) = MB &minus; MD<br>
                        CF(A,B) = CF(A) + CF(B) &times; (1 &minus; CF(A))
                    </div>
                </div>

            </div>{{-- /sidebar-panel --}}
        </div>
        {{-- /right column --}}

    </div>{{-- /hasil-grid --}}
</div>{{-- /nb-container --}}

@endsection

{{-- =========================================================================
     JAVASCRIPT
     ========================================================================= --}}
@push('scripts')
<script>
(function () {
    'use strict';

    // ── Animate all CF progress bars on load ──────────────────────────────
    // Uses requestAnimationFrame so CSS transition fires after paint.
    requestAnimationFrame(function () {
        document.querySelectorAll('.cf-bar-fill').forEach(function (bar) {
            var target = bar.dataset.width;
            if (target) {
                requestAnimationFrame(function () { bar.style.width = target; });
            }
        });
    });

    // =========================================================================
    // toggleCandidate — expand / collapse a candidate row's body
    // =========================================================================
    window.toggleCandidate = function (bodyId) {
        var body  = document.getElementById(bodyId);
        var index = bodyId.replace('body-', '');
        var head  = body ? body.previousElementSibling : null;

        // Walk up from candidate-bar-row to reach candidate-head
        // Structure: candidate-head > candidate-bar-row > candidate-body
        // We look for the candidate-head that has onclick targeting this bodyId
        var chev  = document.getElementById('chev-' + index);

        if (!body) return;

        var isOpen = body.classList.toggle('is-open');

        // Rotate chevron
        if (chev) chev.classList.toggle('open', isOpen);

        // Update aria-expanded on the trigger button (the candidate-head)
        // The head is two siblings above the body (head → bar-row → body)
        var siblings = body.parentElement ? body.parentElement.children : [];
        for (var i = 0; i < siblings.length; i++) {
            if (siblings[i].classList.contains('candidate-head')) {
                siblings[i].setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                break;
            }
        }
    };

    // =========================================================================
    // toggleSteps — expand / collapse the CF steps panel inside a candidate
    // =========================================================================
    window.toggleSteps = function (stepsId) {
        var panel  = document.getElementById(stepsId);
        var index  = stepsId.replace('steps-', '');
        var sarrow = document.getElementById('sarrow-' + index);

        if (!panel) return;

        var isOpen = panel.classList.toggle('is-open');
        if (sarrow) sarrow.classList.toggle('open', isOpen);

        // Update aria-expanded on the toggle button
        var btn = panel.previousElementSibling;
        if (btn && btn.classList.contains('cf-steps-toggle')) {
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }
    };

    // ── Auto-expand the top candidate on load (index 0) ───────────────────
    // The Blade template sets is-open on body-0 already, but we also ensure
    // the chevron and aria state are consistent.
    (function initTopCandidate() {
        var topBody = document.getElementById('body-0');
        if (topBody && topBody.classList.contains('is-open')) {
            var chev = document.getElementById('chev-0');
            if (chev) chev.classList.add('open');
        }
    })();

})(); // end IIFE
</script>
@endpush