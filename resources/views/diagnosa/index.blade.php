<form method="POST"
      action="{{ route('diagnosa.proses') }}"
      enctype="multipart/form-data">

```
@csrf

{{-- Upload Foto --}}
<div class="mb-6">
    <label class="block mb-2 font-semibold">
        Upload Foto Tanaman (Opsional)
    </label>

    <input type="file"
           name="foto"
           class="w-full border rounded-lg p-3">
</div>

{{-- List Gejala --}}
<div class="grid gap-3">

    @forelse($gejalas as $gejala)

        <label class="flex items-center gap-3 p-4 border rounded-xl">

            <input type="checkbox"
                   name="gejala[]"
                   value="{{ $gejala->id }}">

            <span>
                {{ $gejala->kode }}
                -
                {{ $gejala->nama_gejala }}
            </span>

        </label>

    @empty

        <div class="text-red-500">
            Data gejala tidak tersedia
        </div>

    @endforelse

</div>

{{-- Submit --}}
<button type="submit"
        class="w-full mt-6 bg-green-600 text-white py-3 rounded-xl">

    Proses Diagnosa

</button>
```

</form>
