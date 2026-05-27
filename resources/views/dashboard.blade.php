@extends('layouts.app')

@section('content')
<section class="relative flex flex-col items-center justify-center py-20 px-4 text-center">
    <h1 class="text-5xl md:text-6xl font-extrabold text-[#1B5E20] mb-6">
        Deteksi Hama & Penyakit <br>
        <span class="text-[#2E7D32]">Tanaman Secara Cepat</span>
    </h1>
    <p class="text-lg text-gray-600 mb-10 max-w-2xl">
        Solusi cerdas berbasis sistem pakar untuk membantu petani mendiagnosis kesehatan tanaman secara akurat dan mendapatkan solusi instan.
    </p>
    
    <div class="flex flex-wrap gap-4 justify-center">
        <a href="/diagnosa" class="bg-[#2E7D32] hover:bg-[#1B5E20] text-white px-8 py-4 rounded-full font-semibold transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
            Mulai Diagnosa
        </a>
        <a href="/diagnosa" class="bg-white border-2 border-[#2E7D32] text-[#2E7D32] px-8 py-4 rounded-full font-semibold transition duration-300 hover:bg-[#F4FFF4]">
            Upload Foto Gejala
        </a>
    </div>
</section>

<section class="py-12">
    <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8 px-4">
        <div class="glass p-8 rounded-3xl transition duration-500 hover:scale-105 hover:shadow-2xl">
            <div class="w-16 h-16 bg-[#F4FFF4] rounded-2xl flex items-center justify-center mb-6 text-[#2E7D32]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-[#1B5E20] mb-3">Diagnosa Akurat</h3>
            <p class="text-gray-600">Menggunakan metode Certainty Factor untuk memastikan tingkat akurasi diagnosis tanaman Anda.</p>
        </div>

        <div class="glass p-8 rounded-3xl transition duration-500 hover:scale-105 hover:shadow-2xl">
            <div class="w-16 h-16 bg-[#F4FFF4] rounded-2xl flex items-center justify-center mb-6 text-[#2E7D32]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-[#1B5E20] mb-3">Artikel Edukasi</h3>
            <p class="text-gray-600">Pelajari berbagai tips perawatan tanaman dari pakar pertanian untuk mencegah hama datang kembali.</p>
        </div>

        <div class="glass p-8 rounded-3xl transition duration-500 hover:scale-105 hover:shadow-2xl">
            <div class="w-16 h-16 bg-[#F4FFF4] rounded-2xl flex items-center justify-center mb-6 text-[#2E7D32]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-[#1B5E20] mb-3">Solusi Instan</h3>
            <p class="text-gray-600">Dapatkan rekomendasi solusi penanganan yang konkret segera setelah proses diagnosis selesai.</p>
        </div>
    </div>
</section>
@endsection