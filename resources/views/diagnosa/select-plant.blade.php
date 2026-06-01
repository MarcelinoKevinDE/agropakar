<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Tanaman - AgroPakar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-4 md:p-8">

    <div class="max-w-4xl mx-auto">
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Pilih Tanaman</h1>
            <p class="text-gray-600">Pilih tanaman yang ingin Anda diagnosa gejalanya.</p>
        </header>

        @if($plants->isEmpty())
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4" role="alert">
                <p>Saat ini belum ada tanaman yang tersedia untuk didiagnosa.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($plants as $plant)
                    <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                        <div class="h-40 bg-gray-200 flex items-center justify-center">
                            <span class="text-gray-500">Foto {{ $plant->nama_tanaman }}</span>
                        </div>
                        
                        <div class="p-5">
                            <h2 class="text-xl font-semibold text-gray-800 mb-2">{{ $plant->nama_tanaman }}</h2>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3">
                                {{ $plant->deskripsi ?? 'Tidak ada deskripsi tersedia.' }}
                            </p>
                            
                            <a href="{{ route('diagnosa.index', $plant->id) }}" 
                               class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition-colors">
                                Mulai Diagnosa
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-10 text-center">
            <a href="/" class="text-green-600 hover:underline font-medium">← Kembali ke Beranda</a>
        </div>
    </div>

</body>
</html>