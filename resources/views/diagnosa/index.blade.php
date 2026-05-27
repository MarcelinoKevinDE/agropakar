@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10">
    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-[#1B5E20]">Pilih Gejala Tanaman</h1>
        <p class="text-gray-600 mt-2">Pilih gejala yang tampak pada tanaman Anda saat ini</p>
    </div>

    <form action="{{ route('diagnosa.hitung') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div class="glass p-6 rounded-3xl shadow-sm border-2 border-[#81C784]/30">
            <label class="block font-semibold text-[#1B5E20] mb-3">Upload Foto Tanaman (Opsional)</label>
            <input type="file" name="foto" accept="image/*" class="w-full p-4 rounded-xl border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-[#2E7D32]">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($gejalas as $gejala)
            <label class="glass group p-5 rounded-2xl flex items-center space-x-4 cursor-pointer hover:bg-[#81C784]/20 transition-all duration-300 border border-transparent hover:border-[#2E7D32]">
                <div class="w-10 h-10 rounded-xl bg-[#F4FFF4] flex items-center justify-center text-[#2E7D32] group-hover:bg-[#2E7D32] group-hover:text-white transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <input type="checkbox" name="gejala_ids[]" value="{{ $gejala->id }}" class="hidden peer">
                <span class="text-gray-700 peer-checked:text-[#1B5E20] peer-checked:font-bold">{{ $gejala->nama_gejala }}</span>
                <div class="ml-auto opacity-0 peer-checked:opacity-100 text-[#2E7D32]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
            </label>
            @endforeach
        </div>

        <button type="submit" class="w-full bg-[#2E7D32] text-white py-4 rounded-full font-bold text-lg hover:bg-[#1B5E20] transition-all shadow-lg hover:shadow-2xl">
            Proses Diagnosa
        </button>
    </form>
</div>
@endsection
