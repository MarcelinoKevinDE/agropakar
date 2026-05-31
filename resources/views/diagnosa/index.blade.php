@extends('layouts.app')

@section('title', 'Diagnosis Penyakit Tanaman — AgroPakar')

{{-- =========================================================================
     PAGE-LEVEL STYLES
     Scoped to this page only. All class names prefixed or use nb-* convention
     already established in the project's layout.
     ========================================================================= --}}
@push('styles')
<style>
    /* ── Page hero ─────────────────────────────────────────────────── */
    .diag-hero {
        background: var(--black);
        border-bottom: var(--border);
        padding: 2.25rem 0 2rem;
    }
    .diag-hero-title {
        font-family: 'Bebas Neue', sans-serif;
        font-size: 2.6rem;
        letter-spacing: 0.05em;
        color: var(--white);
        line-height: 1;
    }
    .diag-hero-title span { color: var(--yellow); }
    .diag-hero-sub {
        font-size: 0.78rem;
        color: rgba(255,255,255,0.55);
        margin-top: 0.5rem;
        letter-spacing: 0.04em;
        line-height: 1.6;
    }

    /* ── Two-column layout ─────────────────────────────────────────── */
    .diag-grid {
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 900px) {
        .diag-grid { grid-template-columns: 1fr; }
    }

    /* ── Symptom panel ─────────────────────────────────────────────── */
    .symptom-panel {
        border: var(--border);
    }
    .symptom-panel-header {
        background: var(--black);
        color: var(--yellow);
        padding: 0.65rem 1rem;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1.1rem;
        letter-spacing: 0.08em;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: var(--border);
    }
    .symptom-count-badge {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.72rem;
        color: var(--yellow);
        background: rgba(255,224,71,0.12);
        padding: 0.2rem 0.55rem;
        border: 1px solid rgba(255,224,71,0.3);
        letter-spacing: 0.08em;
    }

    /* ── Search bar ────────────────────────────────────────────────── */
    .search-bar-wrap {
        padding: 0.75rem;
        background: var(--white);
        border-bottom: 2px solid var(--black);
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
    .search-bar-wrap label {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        white-space: nowrap;
        color: #555;
    }
    .search-input {
        flex: 1;
        padding: 0.5rem 0.75rem;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.82rem;
        border: 2px solid var(--black);
        background: var(--paper);
        color: var(--black);
        outline: none;
        transition: box-shadow 0.15s;
    }
    .search-input:focus {
        box-shadow: 3px 3px 0 var(--black);
    }
    .search-input::placeholder { color: #aaa; }
    .search-clear-btn {
        padding: 0.5rem 0.6rem;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.75rem;
        font-weight: 700;
        border: 2px solid var(--black);
        background: var(--white);
        cursor: pointer;
        transition: background 0.1s;
        line-height: 1;
    }
    .search-clear-btn:hover { background: var(--yellow); }

    /* ── Symptom list (scrollable) ─────────────────────────────────── */
    .symptom-list {
        max-height: 480px;
        overflow-y: auto;
        background: var(--white);
    }
    .symptom-list::-webkit-scrollbar { width: 6px; }
    .symptom-list::-webkit-scrollbar-track { background: #f0f0e8; }
    .symptom-list::-webkit-scrollbar-thumb { background: var(--black); }

    /* ── Individual symptom row (nb-check-row) ─────────────────────── */
    .nb-check-row {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.8rem 1rem;
        border-bottom: 2px solid #e8e8e0;
        cursor: pointer;
        background: var(--white);
        transition: background 0.1s;
        user-select: none;
    }
    .nb-check-row:last-child { border-bottom: none; }
    .nb-check-row:hover       { background: #fffbe6; }
    .nb-check-row.is-checked  { background: var(--yellow); }
    .nb-check-row.is-hidden   { display: none; }

    .nb-check-row input[type="checkbox"] {
        width: 17px;
        height: 17px;
        border: 2px solid var(--black);
        accent-color: var(--black);
        flex-shrink: 0;
        margin-top: 3px;
        cursor: pointer;
    }

    /* nb-check-label and nb-check-code are used by the JS too */
    .nb-check-label {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.82rem;
        font-weight: 500;
        line-height: 1.45;
        color: var(--black);
    }
    .nb-check-code {
        display: block;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.68rem;
        color: #666;
        margin-top: 0.15rem;
        letter-spacing: 0.06em;
    }

    /* ── No-results message ────────────────────────────────────────── */
    .no-results-msg {
        display: none;
        padding: 1.75rem 1rem;
        text-align: center;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.78rem;
        color: #999;
    }

    /* ── Bulk action toolbar ───────────────────────────────────────── */
    .bulk-toolbar {
        padding: 0.65rem 0.75rem;
        background: #f5f5ed;
        border-top: 2px solid var(--black);
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .bulk-btn {
        padding: 0.4rem 0.85rem;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        border: 2px solid var(--black);
        background: var(--white);
        cursor: pointer;
        transition: background 0.1s, transform 0.08s, box-shadow 0.08s;
        box-shadow: 2px 2px 0 var(--black);
    }
    .bulk-btn:hover {
        background: var(--yellow);
        transform: translate(-1px, -1px);
        box-shadow: 3px 3px 0 var(--black);
    }
    .bulk-btn:active {
        transform: translate(1px, 1px);
        box-shadow: 1px 1px 0 var(--black);
    }
    .bulk-btn.is-destructive { background: #ffe0de; }
    .bulk-btn.is-destructive:hover { background: #ffcfcc; }

    /* ── Symptom count shown inside the header ─────────────────────── */
    #visibleCount {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.7rem;
        color: rgba(255,255,255,0.5);
    }

    /* ── Right panel: summary + submit ────────────────────────────── */
    .summary-panel {
        border: var(--border);
        position: sticky;
        top: 76px; /* height of navbar */
    }
    .summary-panel-header {
        background: var(--black);
        color: var(--yellow);
        padding: 0.65rem 1rem;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1.1rem;
        letter-spacing: 0.08em;
        border-bottom: var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .summary-list {
        min-height: 120px;
        max-height: 320px;
        overflow-y: auto;
        background: var(--white);
    }
    .summary-list::-webkit-scrollbar { width: 4px; }
    .summary-list::-webkit-scrollbar-thumb { background: var(--black); }

    .summary-empty {
        padding: 2rem 1rem;
        text-align: center;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.75rem;
        color: #aaa;
        line-height: 1.7;
    }
    .summary-item {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 0.6rem 0.9rem;
        border-bottom: 2px solid #e8e8e0;
        background: var(--white);
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.76rem;
        gap: 0.5rem;
    }
    .summary-item:last-child { border-bottom: none; }
    .summary-item-name { line-height: 1.45; flex: 1; }
    .summary-item-code {
        font-size: 0.65rem;
        color: #888;
        display: block;
        margin-top: 0.1rem;
    }
    .summary-remove-btn {
        background: var(--red, #FF3B2F);
        border: 2px solid var(--black);
        color: var(--white);
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.62rem;
        font-weight: 700;
        padding: 0.1rem 0.35rem;
        cursor: pointer;
        flex-shrink: 0;
        line-height: 1.4;
        transition: opacity 0.1s;
    }
    .summary-remove-btn:hover { opacity: 0.8; }

    /* ── Submit area ───────────────────────────────────────────────── */
    .submit-area {
        padding: 1rem;
        background: var(--paper);
        border-top: var(--border);
    }
    .submit-btn {
        display: block;
        width: 100%;
        padding: 0.9rem 1rem;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1.3rem;
        letter-spacing: 0.08em;
        border: var(--border);
        box-shadow: 4px 4px 0 var(--black);
        background: var(--yellow);
        color: var(--black);
        cursor: pointer;
        text-align: center;
        transition: transform 0.1s, box-shadow 0.1s;
    }
    .submit-btn:hover {
        transform: translate(-2px, -2px);
        box-shadow: 6px 6px 0 var(--black);
    }
    .submit-btn:active {
        transform: translate(2px, 2px);
        box-shadow: 2px 2px 0 var(--black);
    }
    .submit-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        transform: none;
        box-shadow: 2px 2px 0 var(--black);
    }
    .disclaimer {
        margin-top: 0.75rem;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.66rem;
        color: #888;
        line-height: 1.6;
        text-align: center;
    }

    /* ── User info card (above submit) ─────────────────────────────── */
    .user-card {
        border: var(--border);
        margin-bottom: 1.5rem;
    }
    .user-card-header {
        background: var(--black);
        color: var(--yellow);
        padding: 0.65rem 1rem;
        font-family: 'Bebas Neue', sans-serif;
        font-size: 1.05rem;
        letter-spacing: 0.08em;
        border-bottom: var(--border);
    }
    .user-card-body { padding: 0.9rem 1rem; background: var(--white); }

    /* ── Validation alert ──────────────────────────────────────────── */
    .nb-alert {
        padding: 0.9rem 1.1rem;
        border: var(--border);
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.8rem;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }
    .nb-alert-error  { background: #FFE0DE; border-color: #FF3B2F; }
    .nb-alert-title  {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        margin-bottom: 0.4rem;
    }

    /* ── CF info button ────────────────────────────────────────────── */
    .cf-info-btn {
        padding: 0.35rem 0.85rem;
        font-family: 'IBM Plex Mono', monospace;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        border: 2px solid rgba(255,224,71,0.5);
        background: transparent;
        color: rgba(255,255,255,0.75);
        cursor: pointer;
        transition: border-color 0.15s, color 0.15s, background 0.15s;
    }
    .cf-info-btn:hover {
        border-color: var(--yellow);
        color: var(--yellow);
        background: rgba(255,224,71,0.07);
    }
</style>
@endpush

@section('content')

{{-- =========================================================================
     HERO
     ========================================================================= --}}
<div class="diag-hero">
    <div class="nb-container">
        <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:1rem;">
            <div>
                <h1 class="diag-hero-title">
                    DIAGNOSIS<br><span>PENYAKIT TANAMAN</span>
                </h1>
                <p class="diag-hero-sub">
                    Pilih semua gejala yang Anda amati pada tanaman.<br>
                    Sistem akan menghitung penyakit paling mungkin menggunakan metode
                    <strong style="color:rgba(255,255,255,.8)">Certainty Factor</strong>.
                </p>
            </div>
            <button type="button" class="cf-info-btn" onclick="openCfModal()">
                PELAJARI METODE CF
            </button>
        </div>
    </div>
</div>

{{-- =========================================================================
     MAIN CONTENT
     ========================================================================= --}}
<div class="nb-container py-5">

    {{-- ── Server-side validation errors ─────────────────────────────────── --}}
    @if ($errors->any())
        <div class="nb-alert nb-alert-error" role="alert" id="serverErrors">
            <div class="nb-alert-title">VALIDASI GAGAL</div>
            @foreach ($errors->all() as $error)
                <div style="display:flex; gap:0.5rem;">
                    <span style="color:#FF3B2F; font-weight:700; flex-shrink:0;">[!]</span>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Inline client-side alert (hidden until triggered) ─────────────── --}}
    <div class="nb-alert nb-alert-error"
         id="clientError"
         role="alert"
         style="display:none;"
         aria-live="assertive">
        <div class="nb-alert-title">BELUM ADA GEJALA DIPILIH</div>
        <div>Pilih minimal satu gejala dari daftar di bawah sebelum menjalankan diagnosis.</div>
    </div>

    <form action="{{ route('diagnosa.hitung') }}"
          method="POST"
          id="diagnosaForm"
          novalidate>
        @csrf

        <div class="diag-grid">

            {{-- ============================================================
                 LEFT COLUMN: symptom list
                 ============================================================ --}}
            <div>

                {{-- ── User info (optional name) ──────────────────────────── --}}
                <div class="user-card" style="margin-bottom:1.5rem;">
                    <div class="user-card-header">DATA PENGGUNA</div>
                    <div class="user-card-body">
                        <label class="nb-label" for="nama_user"
                               style="font-family:'IBM Plex Mono',monospace;
                                      font-size:0.7rem; font-weight:700;
                                      letter-spacing:0.1em; text-transform:uppercase;
                                      display:block; margin-bottom:0.4rem;">
                            NAMA PENGGUNA
                            <span style="opacity:0.5; font-weight:400;">(OPSIONAL)</span>
                        </label>
                        <input type="text"
                               name="nama_user"
                               id="nama_user"
                               class="nb-input"
                               placeholder="CONTOH: PETANI MAJU BERSAMA"
                               value="{{ old('nama_user') }}"
                               style="width:100%; padding:0.65rem 0.85rem;
                                      font-family:'IBM Plex Mono',monospace;
                                      font-size:0.82rem; border:2px solid var(--black);
                                      background:var(--paper); outline:none;">
                    </div>
                </div>

                {{-- ── Symptom checklist panel ─────────────────────────────── --}}
                <div class="symptom-panel">

                    {{-- Header --}}
                    <div class="symptom-panel-header">
                        <span>PILIH GEJALA YANG DIAMATI</span>
                        <div style="display:flex; align-items:center; gap:0.75rem;">
                            <span id="visibleCount"></span>
                            <span class="symptom-count-badge" id="selectedBadge">0 DIPILIH</span>
                        </div>
                    </div>

                    {{-- Live search --}}
                    <div class="search-bar-wrap">
                        <label for="searchInput">CARI:</label>
                        <input type="text"
                               id="searchInput"
                               class="search-input"
                               placeholder="KETIK NAMA ATAU KODE GEJALA..."
                               autocomplete="off"
                               aria-label="Cari gejala">
                        <button type="button"
                                class="search-clear-btn"
                                id="searchClearBtn"
                                title="Bersihkan pencarian"
                                aria-label="Bersihkan pencarian"
                                style="display:none;">
                            X
                        </button>
                    </div>

                    {{-- Symptom rows --}}
                    <div class="symptom-list" id="symptomList" role="group" aria-label="Daftar gejala">

                        @forelse ($gejala as $g)
                            {{--
                                COLUMN NAMES USED:
                                  $g->kode         → kode   (unique identifier per spec)
                                  $g->nama_gejala  → nama_gejala
                                  $g->id           → value for the checkbox (FK used in rules table)

                                old('gejala', []) restores checked state after a failed validation
                                redirect so the user does not lose their selections.
                            --}}
                            <label class="nb-check-row gejala-item
                                          {{ in_array($g->id, old('gejala', [])) ? 'is-checked' : '' }}"
                                   for="gejala_{{ $g->id }}"
                                   data-nama="{{ strtolower($g->nama_gejala) }}"
                                   data-kode="{{ strtolower($g->kode) }}">

                                <input type="checkbox"
                                       name="gejala[]"
                                       id="gejala_{{ $g->id }}"
                                       value="{{ $g->id }}"
                                       class="gejala-check"
                                       {{ in_array($g->id, old('gejala', [])) ? 'checked' : '' }}
                                       aria-describedby="kode_{{ $g->id }}">

                                <div>
                                    <span class="nb-check-label">{{ $g->nama_gejala }}</span>
                                    <span class="nb-check-code"
                                          id="kode_{{ $g->id }}">
                                        {{ $g->kode }}
                                    </span>
                                </div>
                            </label>
                        @empty
                            {{--
                                If this branch renders, the database has no gejala rows.
                                Debug: php artisan tinker → Gejala::count()
                            --}}
                            <div style="padding:2.5rem 1rem; text-align:center;
                                        font-family:'IBM Plex Mono',monospace;
                                        font-size:0.78rem; color:#aaa; line-height:1.8;">
                                TIDAK ADA DATA GEJALA DALAM DATABASE.<br>
                                <span style="font-size:0.68rem; color:#ccc;">
                                    Jalankan seeder atau tambahkan data melalui panel admin.
                                </span>
                            </div>
                        @endforelse

                        {{-- No-results message (shown by JS when search has no hits) --}}
                        <div class="no-results-msg" id="noResultsMsg" aria-live="polite">
                            TIDAK ADA GEJALA YANG COCOK DENGAN PENCARIAN ANDA.
                        </div>

                    </div>{{-- /symptom-list --}}

                    {{-- Bulk action toolbar --}}
                    <div class="bulk-toolbar">
                        <button type="button"
                                class="bulk-btn"
                                id="selectAllBtn">
                            PILIH SEMUA
                        </button>
                        <button type="button"
                                class="bulk-btn is-destructive"
                                id="clearAllBtn">
                            HAPUS SEMUA
                        </button>
                    </div>

                </div>{{-- /symptom-panel --}}
            </div>

            {{-- ============================================================
                 RIGHT COLUMN: summary + submit
                 ============================================================ --}}
            <div>
                <div class="summary-panel">

                    {{-- Summary header --}}
                    <div class="summary-panel-header">
                        <span>GEJALA TERPILIH</span>
                        <span class="symptom-count-badge" id="summaryCountBadge">0</span>
                    </div>

                    {{-- Dynamic summary list --}}
                    <div class="summary-list" id="summaryList" aria-live="polite" aria-label="Ringkasan gejala terpilih">
                        <div class="summary-empty" id="summaryEmpty">
                            BELUM ADA GEJALA DIPILIH.<br>
                            <span style="font-size:0.68rem;">
                                CENTANG GEJALA DI SEBELAH KIRI.
                            </span>
                        </div>
                    </div>

                    {{-- Submit area --}}
                    <div class="submit-area">
                        <button type="submit"
                                class="submit-btn"
                                id="submitBtn">
                            JALANKAN DIAGNOSIS
                        </button>
                        <p class="disclaimer">
                            HASIL BERSIFAT REKOMENDATIF.<br>
                            KONFIRMASI DENGAN AHLI PERTANIAN.
                        </p>
                    </div>

                </div>{{-- /summary-panel --}}
            </div>

        </div>{{-- /diag-grid --}}
    </form>

</div>{{-- /nb-container --}}

{{-- =========================================================================
     CF EDUCATIONAL MODAL
     Triggered by the "PELAJARI METODE CF" button in the hero.
     @include assumes partials/cf-modal.blade.php exists in the project.
     ========================================================================= --}}
@include('partials.cf-modal')

@endsection

{{-- =========================================================================
     JAVASCRIPT — all UI logic for this page
     ========================================================================= --}}
@push('scripts')
<script>
(function () {
    'use strict';

    // ── DOM references ─────────────────────────────────────────────────────
    const form          = document.getElementById('diagnosaForm');
    const symptomList   = document.getElementById('symptomList');
    const searchInput   = document.getElementById('searchInput');
    const searchClear   = document.getElementById('searchClearBtn');
    const selectAllBtn  = document.getElementById('selectAllBtn');
    const clearAllBtn   = document.getElementById('clearAllBtn');
    const summaryList   = document.getElementById('summaryList');
    const summaryEmpty  = document.getElementById('summaryEmpty');
    const summaryBadge  = document.getElementById('summaryCountBadge');
    const selectedBadge = document.getElementById('selectedBadge');
    const visibleCount  = document.getElementById('visibleCount');
    const noResultsMsg  = document.getElementById('noResultsMsg');
    const clientError   = document.getElementById('clientError');

    // All symptom rows and checkboxes (NodeList — live)
    const allRows   = () => document.querySelectorAll('.gejala-item');
    const allChecks = () => document.querySelectorAll('.gejala-check');

    // ── Helper: get all currently VISIBLE rows ─────────────────────────────
    function visibleRows() {
        return [...allRows()].filter(row => !row.classList.contains('is-hidden'));
    }

    // ── Helper: get all currently CHECKED checkboxes ───────────────────────
    function checkedBoxes() {
        return [...allChecks()].filter(cb => cb.checked);
    }

    // =========================================================================
    // CORE: sync the entire UI from current checkbox state
    // Called after ANY change to checkboxes (check, uncheck, bulk, restore)
    // =========================================================================
    function syncUI() {
        const checked = checkedBoxes();
        const count   = checked.length;

        // Update header badges
        selectedBadge.textContent = count + ' DIPILIH';
        summaryBadge.textContent  = count;

        // Show/hide summary empty state
        summaryEmpty.style.display = count === 0 ? 'block' : 'none';

        // Rebuild summary list items (remove all except the empty msg)
        summaryList.querySelectorAll('.summary-item').forEach(el => el.remove());

        checked.forEach(function (cb) {
            const row  = cb.closest('.gejala-item');
            const name = row.querySelector('.nb-check-label').textContent.trim();
            const code = row.querySelector('.nb-check-code').textContent.trim();
            const id   = cb.value;

            const item = document.createElement('div');
            item.className = 'summary-item';
            item.dataset.id = id;

            // Name + code text block
            const textBlock = document.createElement('div');
            textBlock.className = 'summary-item-name';
            textBlock.innerHTML =
                name +
                '<span class="summary-item-code">' + escapeHtml(code) + '</span>';

            // Remove button
            const removeBtn = document.createElement('button');
            removeBtn.type      = 'button';
            removeBtn.className = 'summary-remove-btn';
            removeBtn.textContent = 'X';
            removeBtn.setAttribute('aria-label', 'Hapus ' + name);
            removeBtn.addEventListener('click', function () {
                const targetCb = document.getElementById('gejala_' + id);
                if (targetCb) {
                    targetCb.checked = false;
                    targetCb.closest('.gejala-item').classList.remove('is-checked');
                }
                syncUI();
            });

            item.appendChild(textBlock);
            item.appendChild(removeBtn);

            // Insert before the empty message node
            summaryList.insertBefore(item, summaryEmpty);
        });

        // Hide client-side error once user makes a selection
        if (count > 0 && clientError.style.display !== 'none') {
            clientError.style.display = 'none';
        }
    }

    // ── Utility: escape HTML to avoid XSS in summary items ─────────────────
    function escapeHtml(str) {
        return str
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // =========================================================================
    // CHECKBOX CHANGE — individual row toggle
    // =========================================================================
    symptomList.addEventListener('change', function (e) {
        if (!e.target.classList.contains('gejala-check')) return;
        const row = e.target.closest('.gejala-item');
        row.classList.toggle('is-checked', e.target.checked);
        syncUI();
    });

    // =========================================================================
    // LIVE SEARCH
    // Filters rows by matching query against data-nama and data-kode attributes.
    // Using data attributes avoids repeated DOM reads on every keystroke.
    // =========================================================================
    searchInput.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();

        // Show/hide the X clear button
        searchClear.style.display = query.length > 0 ? 'inline-block' : 'none';

        let visibleTotal = 0;

        allRows().forEach(function (row) {
            const nama = row.dataset.nama || '';
            const kode = row.dataset.kode || '';
            const match = nama.includes(query) || kode.includes(query);

            row.classList.toggle('is-hidden', !match);
            if (match) visibleTotal++;
        });

        // Update the visible count label
        updateVisibleCount(visibleTotal);

        // Show no-results message
        noResultsMsg.style.display = (visibleTotal === 0 && query.length > 0)
            ? 'block'
            : 'none';
    });

    // Clear search
    searchClear.addEventListener('click', function () {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
        searchInput.focus();
    });

    // ── Helper: update visible count in header ─────────────────────────────
    function updateVisibleCount(count) {
        const total = allRows().length;
        if (count === total) {
            visibleCount.textContent = total + ' GEJALA';
        } else {
            visibleCount.textContent = count + '/' + total + ' TAMPIL';
        }
    }

    // =========================================================================
    // SELECT ALL — only selects currently VISIBLE rows (respects active search)
    // =========================================================================
    selectAllBtn.addEventListener('click', function () {
        visibleRows().forEach(function (row) {
            const cb = row.querySelector('.gejala-check');
            if (cb && !cb.checked) {
                cb.checked = true;
                row.classList.add('is-checked');
            }
        });
        syncUI();
    });

    // =========================================================================
    // CLEAR ALL — unchecks ALL rows regardless of search filter
    // =========================================================================
    clearAllBtn.addEventListener('click', function () {
        allChecks().forEach(function (cb) {
            cb.checked = false;
            cb.closest('.gejala-item').classList.remove('is-checked');
        });
        syncUI();
    });

    // =========================================================================
    // FORM SUBMIT — client-side guard before POST
    // =========================================================================
    form.addEventListener('submit', function (e) {
        const count = checkedBoxes().length;

        if (count === 0) {
            e.preventDefault();

            // Show the inline error alert
            clientError.style.display = 'block';

            // Scroll to top of page so the alert is visible
            window.scrollTo({ top: 0, behavior: 'smooth' });

            // Focus the search input to help the user start selecting
            setTimeout(function () { searchInput.focus(); }, 400);
        }
    });

    // =========================================================================
    // CF MODAL CONTROLS
    // openCfModal / closeCfModal are expected by cf-modal.blade.php
    // =========================================================================
    window.openCfModal = function () {
        const overlay = document.getElementById('cfModal');
        if (overlay) overlay.classList.add('open');
    };
    window.closeCfModal = function () {
        const overlay = document.getElementById('cfModal');
        if (overlay) overlay.classList.remove('open');
    };

    // Close modal on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') window.closeCfModal();
    });

    // =========================================================================
    // INITIALISE — run once on page load to restore old() state + set counts
    // =========================================================================
    (function init() {
        // Mark rows that were re-checked via old() (after server validation fail)
        allChecks().forEach(function (cb) {
            if (cb.checked) {
                cb.closest('.gejala-item').classList.add('is-checked');
            }
        });

        // Set initial visible count
        updateVisibleCount(allRows().length);

        // Build summary for any old() selections
        syncUI();
    })();

})(); // end IIFE
</script>
@endpush