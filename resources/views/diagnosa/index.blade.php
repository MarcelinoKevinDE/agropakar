@extends('layouts.app')

@section('title', 'Diagnosis — PakarMatik')

@section('content')

<!-- HERO -->
<div class="nb-hero">
    <div class="nb-container">
        <h1 class="nb-hero-title">SISTEM <span>DIAGNOSIS</span><br>KERUSAKAN MOTOR</h1>
        <p class="nb-hero-sub">
            Pilih gejala yang Anda amati, sistem akan menghitung kerusakan paling mungkin
            menggunakan metode Certainty Factor.
        </p>
        <div class="mt-3">
            <button class="nb-btn nb-btn-sm" onclick="openCfModal()" type="button">
                PELAJARI CARA KERJA CF
            </button>
        </div>
    </div>
</div>

<!-- CONTENT -->
<div class="nb-container py-5">

    <!-- VALIDATION ERRORS -->
    @if ($errors->any())
        <div class="nb-alert nb-alert-error mb-4">
            <div class="nb-alert-title">TERJADI KESALAHAN VALIDASI</div>
            @foreach ($errors->all() as $error)
                <div>-- {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('diagnosa.hitung') }}" method="POST" id="diagnosaForm">
        @csrf

        <div class="grid cols-7-5 g-4">

            <!-- LEFT: SYMPTOM LIST -->
            <div>
                <div class="nb-section-header flex-between">
                    <span>PILIH GEJALA YANG DIAMATI</span>
                    <span id="hitungGejala" style="font-family:'IBM Plex Mono',monospace;
                          font-size:0.78rem; color: var(--yellow);">
                        0 dipilih
                    </span>
                </div>

                <!-- Search -->
                <div style="border: var(--border); border-top: none; border-bottom: none;
                            padding: 0.75rem; background: var(--white);">
                    <input type="text"
                           id="searchGejala"
                           class="nb-input"
                           placeholder="CARI GEJALA..."
                           style="margin-bottom:0;">
                </div>

                <!-- Gejala Checkboxes -->
                <div id="gejalaList"
     style="border: var(--border); border-top: none;
            max-height: 520px; overflow-y: auto;">
    
    {{-- Ubah $gejalas menjadi $gejala --}}
    @forelse ($gejala as $g) 
        <label class="nb-check-row gejala-item {{ in_array($g->id, old('gejala', [])) ? 'checked' : '' }}"
               for="gejala_{{ $g->id }}">
            <input type="checkbox"
                   name="gejala[]"
                   id="gejala_{{ $g->id }}"
                   value="{{ $g->id }}"
                   class="gejala-check"
                   {{ in_array($g->id, old('gejala', [])) ? 'checked' : '' }}>
            <div>
                <div class="nb-check-label">{{ $g->nama_gejala }}</div>
                {{-- Pastikan nama kolom 'kode' sesuai dengan database Anda --}}
                <span class="nb-check-code">{{ $g->kode }}</span>
            </div>
        </label>
    @empty
        <div style="padding: 2rem; text-align: center; color: #888; font-size: 0.82rem;">
            TIDAK ADA DATA GEJALA.
        </div>
    @endforelse
</div>

                <!-- Select all / clear -->
                <div style="border: var(--border); border-top: none; padding: 0.75rem;
                            background: var(--white); display: flex; gap: 0.5rem;">
                    <button type="button" class="nb-btn nb-btn-sm" id="selectAll">
                        PILIH SEMUA
                    </button>
                    <button type="button" class="nb-btn nb-btn-sm nb-btn-outline" id="clearAll">
                        HAPUS SEMUA
                    </button>
                </div>
            </div>

            <!-- RIGHT: INFO + SUBMIT -->
            <div>

                <!-- Nama pengguna -->
                <div class="nb-section-header">DATA PENGGUNA</div>
                <div class="nb-card" style="border-top: none; margin-bottom: 1.5rem;">
                    <label class="nb-label" for="nama_user">
                        NAMA PENGGUNA <span style="opacity:0.5;">(OPSIONAL)</span>
                    </label>
                    <input type="text"
                           name="nama_user"
                           id="nama_user"
                           class="nb-input"
                           placeholder="CONTOH: BUDI SANTOSO"
                           value="{{ old('nama_user') }}">
                </div>

                <!-- Selected summary -->
                <div class="nb-section-header">GEJALA TERPILIH</div>
                <div id="summaryBox"
                     style="border: var(--border); border-top: none;
                            min-height: 140px; max-height: 280px;
                            overflow-y: auto; background: var(--white);">
                    <div id="summaryEmpty"
                         style="padding: 1.5rem; text-align: center;
                                color: #888; font-size: 0.78rem;">
                        BELUM ADA GEJALA DIPILIH.<br>
                        CENTANG GEJALA DI SEBELAH KIRI.
                    </div>
                </div>

                <!-- Submit -->
                <div class="mt-4">
                    <button type="submit" class="nb-btn w-100" id="submitBtn"
                            style="font-size: 1rem; padding: 1rem; text-align: center;
                                   display: block;">
                        JALANKAN DIAGNOSIS
                    </button>
                </div>

                <!-- Disclaimer -->
                <div class="nb-card mt-3"
                     style="background: var(--black); color: rgba(255,255,255,0.55);
                            font-size: 0.7rem; line-height: 1.6;">
                    HASIL DIAGNOSIS BERSIFAT REKOMENDATIF. SELALU KONSULTASIKAN DENGAN
                    TEKNISI BERLISENSI UNTUK PERBAIKAN KENDARAAN ANDA.
                </div>

            </div>
        </div>

    </form>
</div>

@include('partials.cf-modal')

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checks     = document.querySelectorAll('.gejala-check');
    const summary    = document.getElementById('summaryBox');
    const emptyMsg   = document.getElementById('summaryEmpty');
    const counter    = document.getElementById('hitungGejala');
    const search     = document.getElementById('searchGejala');

    // -----------------------------------------------------------------
    // Sync summary panel
    // -----------------------------------------------------------------
    function syncSummary() {
        const checked = [...checks].filter(c => c.checked);
        counter.textContent = checked.length + ' DIPILIH';

        // Remove existing summary rows
        summary.querySelectorAll('.sum-row').forEach(el => el.remove());

        if (checked.length === 0) {
            emptyMsg.style.display = 'block';
            return;
        }

        emptyMsg.style.display = 'none';

        checked.forEach(c => {
            const label = c.closest('.gejala-item');
            const name  = label.querySelector('.nb-check-label').textContent;
            const code  = label.querySelector('.nb-check-code').textContent;

            const row = document.createElement('div');
            row.className = 'sum-row';
            row.style.cssText = 'display:flex; justify-content:space-between; align-items:center;' +
                                'padding:0.6rem 0.9rem; border-bottom:2px solid #e8e8e0; font-size:0.76rem;';
            row.innerHTML =
                '<div>' +
                    '<div style="font-weight:600;">' + name + '</div>' +
                    '<div style="color:#888; font-size:0.68rem;">' + code + '</div>' +
                '</div>' +
                '<button type="button" style="background:var(--red); border:2px solid var(--black);' +
                    'color:#fff; font-size:0.65rem; padding:0.1rem 0.4rem; cursor:pointer;' +
                    'font-family:inherit; font-weight:700;" data-id="' + c.value + '">X</button>';

            row.querySelector('button').addEventListener('click', function () {
                const id = this.dataset.id;
                const cb = document.getElementById('gejala_' + id);
                if (cb) {
                    cb.checked = false;
                    cb.closest('.gejala-item').classList.remove('checked');
                }
                syncSummary();
            });

            summary.insertBefore(row, emptyMsg);
        });
    }

    // -----------------------------------------------------------------
    // Checkbox listeners
    // -----------------------------------------------------------------
    checks.forEach(c => {
        c.addEventListener('change', function () {
            this.closest('.gejala-item').classList.toggle('checked', this.checked);
            syncSummary();
        });
    });

    syncSummary(); // restore old() values

    // -----------------------------------------------------------------
    // Select all / clear
    // -----------------------------------------------------------------
    document.getElementById('selectAll').addEventListener('click', function () {
        document.querySelectorAll('.gejala-item:not([style*="display: none"])').forEach(item => {
            const cb = item.querySelector('.gejala-check');
            cb.checked = true;
            item.classList.add('checked');
        });
        syncSummary();
    });

    document.getElementById('clearAll').addEventListener('click', function () {
        checks.forEach(c => {
            c.checked = false;
            c.closest('.gejala-item').classList.remove('checked');
        });
        syncSummary();
    });

    // -----------------------------------------------------------------
    // Live search
    // -----------------------------------------------------------------
    search.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.gejala-item').forEach(item => {
            const name = item.querySelector('.nb-check-label').textContent.toLowerCase();
            const code = item.querySelector('.nb-check-code').textContent.toLowerCase();
            item.style.display = (name.includes(q) || code.includes(q)) ? '' : 'none';
        });
    });

    // -----------------------------------------------------------------
    // Client-side guard
    // -----------------------------------------------------------------
    document.getElementById('diagnosaForm').addEventListener('submit', function (e) {
        const anyChecked = [...checks].some(c => c.checked);
        if (!anyChecked) {
            e.preventDefault();
            const existing = document.getElementById('clientErr');
            if (!existing) {
                const div = document.createElement('div');
                div.id = 'clientErr';
                div.className = 'nb-alert nb-alert-error mb-4';
                div.innerHTML = '<div class="nb-alert-title">VALIDASI GAGAL</div>' +
                                '-- Pilih minimal satu gejala sebelum menjalankan diagnosis.';
                this.insertAdjacentElement('beforebegin', div);
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
});

// CF Modal
function openCfModal()  { document.getElementById('cfModal').classList.add('open'); }
function closeCfModal() { document.getElementById('cfModal').classList.remove('open'); }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCfModal(); });
</script>
@endpush