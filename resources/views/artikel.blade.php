@extends('layouts.app')

@section('content')

@php
$artikelPalsu = [
    [
        'judul' => 'Teknik Menanam Padi Modern',
        'isi' => 'Teknik modern membantu petani meningkatkan hasil panen padi dengan irigasi dan pupuk yang tepat.',
        'foto' => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=1200&q=80'
    ],
    [
        'judul' => 'Cara Merawat Tanaman Cabai',
        'isi' => 'Penyiraman rutin, pupuk organik, dan pengendalian hama membuat tanaman cabai tetap subur.',
        'foto' => 'https://images.unsplash.com/photo-1588252303782-cb80119abd6d?auto=format&fit=crop&w=1200&q=80'
    ],
    [
        'judul' => 'Pertanian Organik untuk Masa Depan',
        'isi' => 'Pertanian organik semakin diminati karena lebih sehat, alami, dan ramah lingkungan.',
        'foto' => 'https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=1200&q=80'
    ],
    [
        'judul' => 'Manfaat Pupuk Kompos',
        'isi' => 'Kompos menjaga kesuburan tanah, meningkatkan kualitas tanaman, dan mengurangi pupuk kimia.',
        'foto' => 'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=1200&q=80'
    ],
    [
        'judul' => 'Drone Membantu Petani',
        'isi' => 'Drone digunakan untuk memantau kondisi sawah dan membantu mendeteksi masalah lebih cepat.',
        'foto' => 'https://images.unsplash.com/photo-1523741543316-beb7fc7023d8?auto=format&fit=crop&w=1200&q=80'
    ],
    [
        'judul' => 'Budidaya Hidroponik Modern',
        'isi' => 'Hidroponik cocok untuk lahan sempit dan efektif menanam sayuran dengan hasil cepat.',
        'foto' => 'https://images.unsplash.com/photo-1560493676-04071c5f467b?auto=format&fit=crop&w=1200&q=80'
    ],
];
@endphp

<div class="min-h-screen bg-[#F6FBF7] py-16 px-6">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="text-center mb-14">
            <h1 class="text-5xl font-black text-[#123524] mb-4">
                Artikel Pertanian
            </h1>

            <p class="text-gray-600 max-w-2xl mx-auto text-lg">
                Temukan berbagai informasi, tips, dan wawasan terbaru seputar dunia pertanian modern.
            </p>

            <div class="mt-6 inline-block bg-[#2E7D32] text-white px-6 py-2 rounded-full shadow-md font-semibold">
                Dibuat oleh Muhammad Arif Rivaldi
            </div>
        </div>

        {{-- Grid Artikel --}}
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

            @foreach($artikelPalsu as $artikel)
            <div class="bg-white rounded-3xl overflow-hidden shadow-lg border border-gray-100 hover:shadow-2xl transition duration-300 hover:-translate-y-1">

                {{-- Gambar --}}
                <div class="overflow-hidden">
                    <img 
                        src="{{ $artikel['foto'] }}" 
                        alt="{{ $artikel['judul'] }}"
                        class="w-full h-52 object-cover hover:scale-105 transition duration-500"
                    >
                </div>

                {{-- Konten --}}
                <div class="p-6">

                    {{-- Judul --}}
                    <h3 class="font-bold text-xl text-[#123524] mb-3">
                        {{ $artikel['judul'] }}
                    </h3>

                    {{-- Deskripsi --}}
                    <p class="text-gray-600 text-sm leading-relaxed">
                        {{ $artikel['isi'] }}
                    </p>

                </div>
            </div>
            @endforeach

        </div>

    </div>
</div>

@endsection