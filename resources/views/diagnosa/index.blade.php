{{-- Validation error display --}}
@if ($errors->any())
    <div class="nb-alert nb-alert-error mb-4">
        <div class="nb-alert-title">VALIDASI GAGAL</div>
        @foreach ($errors->all() as $error)
            <div>-- {{ $error }}</div>
        @endforeach
    </div>
@endif

<form action="{{ route('diagnosa.hitung') }}" method="POST" id="diagnosaForm">
    @csrf

    {{--
        @forelse guards against an empty collection.
        The variable name here ($gejala) MUST match the compact() key in index().
        The checkbox name MUST be "gejala[]" — the square brackets tell PHP
        to collect all checked values into an array in $_POST['gejala'].
    --}}
    @forelse ($gejala as $g)
        <label class="nb-check-row gejala-item
                       {{ in_array($g->id, old('gejala', [])) ? 'checked' : '' }}"
               for="gejala_{{ $g->id }}">

            <input type="checkbox"
                   name="gejala[]"
                   id="gejala_{{ $g->id }}"
                   value="{{ $g->id }}"
                   class="gejala-check"
                   {{ in_array($g->id, old('gejala', [])) ? 'checked' : '' }}>

            <div>
                <div class="nb-check-label">{{ $g->nama_gejala }}</div>
                <span class="nb-check-code">{{ $g->kode ?? 'No Code' }}</span>
            </div>
        </label>
    @empty
        {{--
            If this renders, your DB has no data — not a code bug.
            Run: php artisan tinker -> Gejala::count()
        --}}
        <div style="padding:2rem; text-align:center; color:#888; font-size:0.82rem;">
            TIDAK ADA DATA GEJALA DALAM DATABASE.
        </div>
    @endforelse

</form>