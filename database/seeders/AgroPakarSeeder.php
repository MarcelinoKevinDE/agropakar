<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Gejala;
use App\Models\Penyakit;
use App\Models\Rule;

class AgroPakarSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | DATA PENYAKIT
        |--------------------------------------------------------------------------
        */

        $penyakit = [
            [
                'kode_penyakit' => 'P01',
                'nama_penyakit' => 'Busuk Daun',
                'solusi' => 'Potong bagian daun yang busuk, gunakan fungisida, dan kurangi kelembapan berlebih.'
            ],
            [
                'kode_penyakit' => 'P02',
                'nama_penyakit' => 'Karat Daun',
                'solusi' => 'Gunakan pestisida alami dan jaga sirkulasi udara tanaman.'
            ],
            [
                'kode_penyakit' => 'P03',
                'nama_penyakit' => 'Layu Fusarium',
                'solusi' => 'Cabut tanaman terinfeksi dan gunakan fungisida sistemik.'
            ],
            [
                'kode_penyakit' => 'P04',
                'nama_penyakit' => 'Embun Tepung',
                'solusi' => 'Semprot fungisida sulfur dan hindari kelembapan tinggi.'
            ],
            [
                'kode_penyakit' => 'P05',
                'nama_penyakit' => 'Bercak Alternaria',
                'solusi' => 'Buang daun terinfeksi dan lakukan rotasi tanaman.'
            ],
        ];

        foreach ($penyakit as $item) {
            Penyakit::create($item);
        }

        /*
        |--------------------------------------------------------------------------
        | DATA GEJALA
        |--------------------------------------------------------------------------
        */

        $gejala = [
            [
                'kode_gejala' => 'G01',
                'nama_gejala' => 'Daun bercak cokelat'
            ],
            [
                'kode_gejala' => 'G02',
                'nama_gejala' => 'Daun menguning'
            ],
            [
                'kode_gejala' => 'G03',
                'nama_gejala' => 'Batang membusuk'
            ],
            [
                'kode_gejala' => 'G04',
                'nama_gejala' => 'Daun layu'
            ],
            [
                'kode_gejala' => 'G05',
                'nama_gejala' => 'Muncul serbuk putih pada daun'
            ],
            [
                'kode_gejala' => 'G06',
                'nama_gejala' => 'Pertumbuhan tanaman terhambat'
            ],
            [
                'kode_gejala' => 'G07',
                'nama_gejala' => 'Daun berlubang'
            ],
            [
                'kode_gejala' => 'G08',
                'nama_gejala' => 'Akar membusuk'
            ],
            [
                'kode_gejala' => 'G09',
                'nama_gejala' => 'Tepi daun mengering'
            ],
            [
                'kode_gejala' => 'G10',
                'nama_gejala' => 'Daun terdapat bercak hitam'
            ],
        ];

        foreach ($gejala as $item) {
            Gejala::create($item);
        }

        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA PENYAKIT
        |--------------------------------------------------------------------------
        */

        $p1 = Penyakit::where('kode_penyakit', 'P01')->first();
        $p2 = Penyakit::where('kode_penyakit', 'P02')->first();
        $p3 = Penyakit::where('kode_penyakit', 'P03')->first();
        $p4 = Penyakit::where('kode_penyakit', 'P04')->first();
        $p5 = Penyakit::where('kode_penyakit', 'P05')->first();

        /*
        |--------------------------------------------------------------------------
        | AMBIL DATA GEJALA
        |--------------------------------------------------------------------------
        */

        $g1  = Gejala::where('kode_gejala', 'G01')->first();
        $g2  = Gejala::where('kode_gejala', 'G02')->first();
        $g3  = Gejala::where('kode_gejala', 'G03')->first();
        $g4  = Gejala::where('kode_gejala', 'G04')->first();
        $g5  = Gejala::where('kode_gejala', 'G05')->first();
        $g6  = Gejala::where('kode_gejala', 'G06')->first();
        $g7  = Gejala::where('kode_gejala', 'G07')->first();
        $g8  = Gejala::where('kode_gejala', 'G08')->first();
        $g9  = Gejala::where('kode_gejala', 'G09')->first();
        $g10 = Gejala::where('kode_gejala', 'G10')->first();

        /*
        |--------------------------------------------------------------------------
        | DATA RULE CERTAINTY FACTOR (CF)
        |--------------------------------------------------------------------------
        */

        $rules = [

            // BUSUK DAUN
            [
                'penyakit_id' => $p1->id,
                'gejala_id'   => $g1->id,
                'bobot_cf'    => 0.7
            ],
            [
                'penyakit_id' => $p1->id,
                'gejala_id'   => $g3->id,
                'bobot_cf'    => 0.9
            ],
            [
                'penyakit_id' => $p1->id,
                'gejala_id'   => $g8->id,
                'bobot_cf'    => 0.8
            ],

            // KARAT DAUN
            [
                'penyakit_id' => $p2->id,
                'gejala_id'   => $g1->id,
                'bobot_cf'    => 0.5
            ],
            [
                'penyakit_id' => $p2->id,
                'gejala_id'   => $g2->id,
                'bobot_cf'    => 0.8
            ],
            [
                'penyakit_id' => $p2->id,
                'gejala_id'   => $g9->id,
                'bobot_cf'    => 0.6
            ],

            // LAYU FUSARIUM
            [
                'penyakit_id' => $p3->id,
                'gejala_id'   => $g4->id,
                'bobot_cf'    => 0.9
            ],
            [
                'penyakit_id' => $p3->id,
                'gejala_id'   => $g6->id,
                'bobot_cf'    => 0.7
            ],
            [
                'penyakit_id' => $p3->id,
                'gejala_id'   => $g8->id,
                'bobot_cf'    => 0.8
            ],

            // EMBUN TEPUNG
            [
                'penyakit_id' => $p4->id,
                'gejala_id'   => $g5->id,
                'bobot_cf'    => 0.95
            ],
            [
                'penyakit_id' => $p4->id,
                'gejala_id'   => $g2->id,
                'bobot_cf'    => 0.6
            ],
            [
                'penyakit_id' => $p4->id,
                'gejala_id'   => $g4->id,
                'bobot_cf'    => 0.5
            ],

            // BERCAK ALTERNARIA
            [
                'penyakit_id' => $p5->id,
                'gejala_id'   => $g10->id,
                'bobot_cf'    => 0.9
            ],
            [
                'penyakit_id' => $p5->id,
                'gejala_id'   => $g1->id,
                'bobot_cf'    => 0.7
            ],
            [
                'penyakit_id' => $p5->id,
                'gejala_id'   => $g6->id,
                'bobot_cf'    => 0.6
            ],

        ];

        foreach ($rules as $rule) {
            Rule::create($rule);
        }
    }
}