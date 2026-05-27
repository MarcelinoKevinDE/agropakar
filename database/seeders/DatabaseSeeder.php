<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ====================================================
        // GEJALA (Symptoms)
        // Kode must match the keys in DiagnosaController::$knowledgeBase
        // ====================================================
        $gejalas = [
            ['kode' => 'G001', 'nama_gejala' => 'Bercak coklat pada permukaan daun',         'deskripsi' => 'Terdapat bercak berwarna coklat atau coklat kehitaman pada helaian daun, dapat menyebar luas.'],
            ['kode' => 'G002', 'nama_gejala' => 'Tepi daun mengering dan berubah coklat',     'deskripsi' => 'Bagian tepi atau ujung daun mengering, layu, dan berubah warna menjadi coklat.'],
            ['kode' => 'G003', 'nama_gejala' => 'Daun menguning secara cepat (klorosis)',     'deskripsi' => 'Daun berubah kuning dimulai dari ujung atau tepi, menunjukkan kekurangan klorofil.'],
            ['kode' => 'G004', 'nama_gejala' => 'Permukaan daun terasa lembab dan basah',     'deskripsi' => 'Daun terasa lembab secara tidak normal, sering disertai lapisan berlendir tipis.'],
            ['kode' => 'G005', 'nama_gejala' => 'Batang berwarna hitam atau coklat gelap',    'deskripsi' => 'Batang tanaman tampak hitam atau coklat gelap, terutama pada pangkal atau bagian bawah.'],
            ['kode' => 'G006', 'nama_gejala' => 'Batang lunak dan membusuk saat ditekan',     'deskripsi' => 'Jaringan batang menjadi lunak, berair, dan mengeluarkan bau tidak sedap ketika ditekan.'],
            ['kode' => 'G007', 'nama_gejala' => 'Tanaman layu secara tiba-tiba',              'deskripsi' => 'Seluruh tanaman atau sebagian besar daun layu mendadak meski media tanam masih cukup lembab.'],
            ['kode' => 'G008', 'nama_gejala' => 'Bercak bulat kecil dengan halo kuning',      'deskripsi' => 'Muncul bercak kecil berbentuk bulat atau oval dengan cincin atau halo berwarna kuning di sekitarnya.'],
            ['kode' => 'G009', 'nama_gejala' => 'Bercak cekung berwarna gelap pada buah',    'deskripsi' => 'Terdapat bercak cekung berwarna coklat gelap atau hitam pada permukaan buah.'],
            ['kode' => 'G010', 'nama_gejala' => 'Bercak menyatu membentuk area besar nekrotik', 'deskripsi' => 'Beberapa bercak kecil bergabung menjadi area besar berwarna coklat tua atau hitam (nekrosis).'],
            ['kode' => 'G011', 'nama_gejala' => 'Akar berwarna hitam dan rapuh',              'deskripsi' => 'Akar tanaman berwarna hitam, rapuh, dan mudah putus ketika dicabut dari media tanam.'],
            ['kode' => 'G012', 'nama_gejala' => 'Miselium putih atau abu-abu pada pangkal batang', 'deskripsi' => 'Terlihat pertumbuhan jamur berwarna putih atau abu-abu seperti kapas di sekitar pangkal batang.'],
            ['kode' => 'G013', 'nama_gejala' => 'Bercak merah muda atau jingga pada jaringan', 'deskripsi' => 'Terdapat warna merah muda atau jingga (spora jamur) pada permukaan bercak yang sudah lanjut.'],
            ['kode' => 'G014', 'nama_gejala' => 'Buah mengeriput dan membusuk',               'deskripsi' => 'Buah kehilangan air, mengeriput, dan mengalami pembusukan dimulai dari bercak yang ada.'],
            ['kode' => 'G015', 'nama_gejala' => 'Bercak hitam dengan cincin konsentris',      'deskripsi' => 'Bercak berwarna hitam atau coklat tua dengan pola lingkaran konsentris seperti mata burung.'],
            ['kode' => 'G016', 'nama_gejala' => 'Pustul oranye atau kuning pada daun',        'deskripsi' => 'Muncul tonjolan kecil (pustul) berwarna oranye atau kuning cerah pada bagian bawah daun.'],
            ['kode' => 'G017', 'nama_gejala' => 'Serbuk oranye mudah berpindah saat disentuh', 'deskripsi' => 'Serbuk seperti debu berwarna oranye (uredospora) menempel dan mudah berpindah ketika daun disentuh.'],
            ['kode' => 'G018', 'nama_gejala' => 'Daun menguning kemudian gugur lebih awal',   'deskripsi' => 'Daun yang terinfeksi menguning dan rontok lebih awal dari siklus normal tanaman.'],
        ];

        DB::table('gejala')->insert(array_map(fn($g) => array_merge($g, [
            'created_at' => now(),
            'updated_at' => now(),
        ]), $gejalas));

        // ====================================================
        // PENYAKIT (Diseases)
        // ====================================================
        $penyakits = [
            [
                'kode'          => 'P001',
                'nama_penyakit' => 'Hawar Daun (Late Blight)',
                'deskripsi'     => 'Penyakit hawar daun disebabkan oleh oomisetes Phytophthora infestans. Merupakan salah satu penyakit paling destruktif pada tanaman kentang dan tomat. Menyerang daun, batang, dan buah, menyebabkan kerusakan jaringan yang luas dalam waktu singkat, terutama pada kondisi lembab dan dingin.',
                'penanganan'    => 'Semprot fungisida berbahan aktif mankozeb atau klorotalonil setiap 7–10 hari. Buang dan bakar bagian tanaman yang terinfeksi. Hindari penyiraman dari atas. Gunakan varietas tahan jika tersedia.',
                'pencegahan'    => 'Tanam varietas tahan penyakit. Pastikan drainase lahan baik. Rotasi tanaman setiap musim. Jaga jarak tanam agar sirkulasi udara lancar. Hindari pemupukan nitrogen berlebihan.',
            ],
            [
                'kode'          => 'P002',
                'nama_penyakit' => 'Busuk Batang (Stem Rot)',
                'deskripsi'     => 'Busuk batang umumnya disebabkan oleh jamur Sclerotinia sclerotiorum atau Rhizoctonia solani. Patogen ini menyerang jaringan batang, menyebabkan pembusukan dan pelunakan yang menghambat transportasi air dan nutrisi, berujung pada kelayuan dan kematian tanaman.',
                'penanganan'    => 'Cabut dan musnahkan tanaman yang terinfeksi berat. Aplikasikan fungisida berbahan aktif iprodione atau benomyl pada tanah di sekitar pangkal batang. Kurangi kelembaban media tanam. Perbaiki drainase segera.',
                'pencegahan'    => 'Hindari luka mekanis pada batang saat bekerja di lahan. Sterilisasi media tanam sebelum digunakan. Jangan menanam terlalu rapat. Pastikan kelembaban udara tidak berlebihan.',
            ],
            [
                'kode'          => 'P003',
                'nama_penyakit' => 'Bercak Daun (Leaf Spot)',
                'deskripsi'     => 'Bercak daun adalah penyakit yang disebabkan oleh berbagai jamur seperti Cercospora, Alternaria, atau Septoria. Gejalanya berupa bercak-bercak kecil yang dapat menyebar dan menyebabkan gugurnya daun prematur sehingga menurunkan kapasitas fotosintesis tanaman.',
                'penanganan'    => 'Aplikasikan fungisida berbahan aktif tembaga (copper-based) atau mankozeb. Buang daun yang terinfeksi. Hindari menyiram daun langsung. Kurangi kepadatan kanopi.',
                'pencegahan'    => 'Gunakan benih sehat dan bersertifikat. Rotasi tanaman minimal 2 tahun. Hindari menyiram tanaman pada sore hari. Bersihkan sisa tanaman setelah panen.',
            ],
            [
                'kode'          => 'P004',
                'nama_penyakit' => 'Antraknosa (Anthracnose)',
                'deskripsi'     => 'Antraknosa disebabkan oleh jamur Colletotrichum spp. Penyakit ini menyerang buah, daun, dan batang, ditandai dengan bercak cekung berwarna gelap yang meluas. Sangat merugikan pada tanaman cabai, mangga, pepaya, dan alpukat terutama saat musim hujan.',
                'penanganan'    => 'Semprot fungisida berbahan aktif azoksistrobin atau propineb. Panen buah sebelum terlalu matang untuk menghindari infeksi lanjut. Buang buah yang terinfeksi dari kebun dan musnahkan.',
                'pencegahan'    => 'Jaga kebersihan lahan dari sisa tanaman. Hindari melukai buah saat pemanenan. Aplikasikan fungisida preventif sebelum musim hujan. Gunakan varietas tahan antraknosa.',
            ],
            [
                'kode'          => 'P005',
                'nama_penyakit' => 'Karat Daun (Leaf Rust)',
                'deskripsi'     => 'Karat daun disebabkan oleh jamur obligat dari genus Puccinia atau Uromyces. Ditandai dengan pustul berwarna oranye hingga coklat pada permukaan daun yang mengandung spora. Penyakit ini menyebar sangat cepat melalui angin dan dapat menyebabkan defoliasi massal jika tidak ditangani.',
                'penanganan'    => 'Aplikasikan fungisida sistemik berbahan aktif tebukonazol atau trifloksistrobin. Buang daun bergejala berat. Semprot pada awal infeksi untuk mencegah penyebaran. Ulangi setiap 14 hari.',
                'pencegahan'    => 'Tanam varietas tahan karat. Hindari menanam terlalu rapat. Monitor kondisi tanaman secara rutin terutama saat kelembaban tinggi. Rotasi tanaman setiap musim.',
            ],
        ];

        DB::table('penyakit')->insert(array_map(fn($p) => array_merge($p, [
            'created_at' => now(),
            'updated_at' => now(),
        ]), $penyakits));

        // ====================================================
        // ARTIKEL
        // ====================================================
        $artikels = [
            [
                'judul'        => 'Mengenal Penyakit Hawar Daun dan Cara Pengendaliannya',
                'slug'         => 'mengenal-penyakit-hawar-daun',
                'kategori'     => 'Penyakit Tanaman',
                'ringkasan'    => 'Hawar daun atau late blight adalah ancaman serius bagi petani kentang dan tomat. Pelajari gejala, penyebab, dan strategi pengendalian yang efektif.',
                'konten'       => 'Hawar daun (late blight) disebabkan oleh Phytophthora infestans dan dapat menghancurkan seluruh pertanaman dalam waktu singkat jika tidak segera ditangani. Pengendalian meliputi penggunaan fungisida, varietas tahan, dan manajemen lahan yang baik.',
                'published_at' => now()->subDays(5),
            ],
            [
                'judul'        => 'Panduan Lengkap Penggunaan Fungisida untuk Tanaman Hortikultura',
                'slug'         => 'panduan-fungisida-tanaman-hortikultura',
                'kategori'     => 'Agronomi',
                'ringkasan'    => 'Pemilihan fungisida yang tepat sangat menentukan keberhasilan pengendalian penyakit jamur. Pahami jenis, dosis, dan waktu aplikasi yang benar.',
                'konten'       => 'Fungisida dibedakan menjadi kontak dan sistemik. Fungisida kontak bekerja pada permukaan tanaman, sementara fungisida sistemik diserap dan bekerja dari dalam jaringan tanaman. Rotasi bahan aktif penting dilakukan untuk mencegah resistensi.',
                'published_at' => now()->subDays(10),
            ],
            [
                'judul'        => 'Karat Daun: Musuh Tersembunyi Tanaman yang Menyebar Lewat Angin',
                'slug'         => 'karat-daun-musuh-tersembunyi',
                'kategori'     => 'Penyakit Tanaman',
                'ringkasan'    => 'Karat daun sering diabaikan petani karena gejalanya yang muncul bertahap. Namun jika dibiarkan, penyakit ini dapat menyebabkan kerugian panen hingga 70%.',
                'konten'       => 'Jamur karat dari genus Puccinia menghasilkan spora yang sangat ringan dan mudah terbawa angin hingga ratusan kilometer. Deteksi dini dan aplikasi fungisida sistemik adalah kunci pengendalian yang berhasil.',
                'published_at' => now()->subDays(2),
            ],
        ];

        DB::table('artikel')->insert(array_map(fn($a) => array_merge($a, [
            'created_at' => now(),
            'updated_at' => now(),
        ]), $artikels));
    }
}