@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto glass p-8 rounded-3xl shadow-xl">
    <h2 class="text-2xl font-bold text-[#1B5E20] mb-6">Hasil Analisis</h2>
    
    @foreach($hasilDiagnosa as $hasil)
    <div class="mb-6">
        <div class="flex justify-between mb-1">
            <span class="font-semibold">{{ $hasil['nama'] }}</span>
            <span class="text-[#2E7D32] font-bold">{{ $hasil['persentase'] }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="bg-[#2E7D32] h-3 rounded-full" style="width: {{ $hasil['persentase'] }}%"></div>
        </div>
        <p class="text-sm mt-2 text-gray-600 bg-white p-3 rounded-lg border-l-4 border-[#81C784]">
            <strong>Solusi:</strong> {{ $hasil['solusi'] }}
        </p>
    </div>
    @endforeach
    
    <a href="/diagnosa" class="block text-center mt-6 text-[#1B5E20] font-bold hover:underline">← Diagnosa Ulang</a>
</div>
@endsection