<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Gejala;
use App\Models\Penyakit;
use App\Models\Artikel;

class DiagnosaController extends Controller
{
    /**
     * Certainty Factor knowledge base.
     * Structure: penyakit_kode => [ gejala_kode => MB (Measure of Belief) ]
     *
     * MB values range from 0.0 to 1.0
     * The higher the MB, the stronger the symptom supports the disease.
     */
    protected array $knowledgeBase = [
        'P001' => [ // Hawar Daun (Late Blight)
            'G001' => 0.8,
            'G002' => 0.9,
            'G003' => 0.6,
            'G004' => 0.5,
            'G008' => 0.4,
            'G010' => 0.3,
        ],
        'P002' => [ // Busuk Batang (Stem Rot)
            'G005' => 0.9,
            'G006' => 0.8,
            'G007' => 0.7,
            'G003' => 0.4,
            'G011' => 0.6,
            'G012' => 0.5,
        ],
        'P003' => [ // Bercak Daun (Leaf Spot)
            'G001' => 0.5,
            'G008' => 0.9,
            'G009' => 0.8,
            'G010' => 0.7,
            'G013' => 0.6,
        ],
        'P004' => [ // Antraknosa (Anthracnose)
            'G009' => 0.7,
            'G014' => 0.9,
            'G015' => 0.8,
            'G002' => 0.4,
            'G013' => 0.5,
        ],
        'P005' => [ // Karat Daun (Leaf Rust)
            'G016' => 0.9,
            'G017' => 0.8,
            'G008' => 0.6,
            'G018' => 0.7,
            'G001' => 0.3,
        ],
    ];

    /**
     * Measure of Disbelief (MD) — fixed per disease as prior skepticism weight.
     * Can be refined per symptom if needed.
     */
    protected float $defaultMD = 0.1;

    /**
     * Display the diagnosis form.
     */
   public function index(): \Illuminate\View\View
{
    try {

        $gejalas = Gejala::orderBy('nama_gejala')->get();

        $artikel = [];

        if (\Schema::hasTable('artikel')) {
            $artikel = Artikel::latest()->take(3)->get();
        }

        return view('diagnosa.index', compact('gejalas', 'artikel'));

    } catch (\Exception $e) {

        \Log::error('DiagnosaController@index error: ' . $e->getMessage());

        return view('diagnosa.index', [
            'gejalas' => [],
            'artikel' => []
        ]);
    }
}
    /**
     * Process the diagnosis submission.
     */
    public function diagnosa(Request $request): \Illuminate\Http\RedirectResponse
    {
        try {
            // --- Input Validation ---
            $validator = Validator::make($request->all(), [
                'gejala'   => ['required', 'array', 'min:1'],
                'gejala.*' => ['string', 'max:10'],
                'gambar'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            ], [
                'gejala.required' => 'Pilih minimal satu gejala untuk melakukan diagnosis.',
                'gejala.min'      => 'Pilih minimal satu gejala.',
                'gambar.image'    => 'File harus berupa gambar.',
                'gambar.mimes'    => 'Format gambar: jpeg, png, jpg, atau webp.',
                'gambar.max'      => 'Ukuran gambar maksimal 5 MB.',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $selectedGejala = $request->input('gejala', []);
            $gambarPath     = null;

            // --- Handle Image Upload ---
            if ($request->hasFile('gambar') && $request->file('gambar')->isValid()) {
                $gambarPath = $request->file('gambar')->store('diagnosa_images', 'public');
            }

            // --- Certainty Factor Calculation ---
            $results = $this->hitungCertaintyFactor($selectedGejala);

            if (empty($results)) {
                return redirect()->back()
                    ->with('warning', 'Tidak ada penyakit yang cocok dengan kombinasi gejala yang Anda pilih. Coba tambahkan lebih banyak gejala.')
                    ->withInput();
            }

            // Sort by CF descending
            usort($results, fn($a, $b) => $b['cf'] <=> $a['cf']);

            // Fetch full disease data for top results
            $kodePenyakit = array_column($results, 'kode');
            $penyakitData = Penyakit::whereIn('kode', $kodePenyakit)->get()->keyBy('kode');

            $finalResults = [];
            foreach ($results as $result) {
                $penyakit = $penyakitData->get($result['kode']);
                if ($penyakit) {
                    $finalResults[] = [
                        'penyakit'   => $penyakit,
                        'cf'         => $result['cf'],
                        'cf_persen'  => round($result['cf'] * 100, 2),
                        'level'      => $this->getCFLevel($result['cf']),
                        'color'      => $this->getCFColor($result['cf']),
                    ];
                }
            }

            // Fetch selected gejala names for display
            $gejalaTerpilih = Gejala::whereIn('kode', $selectedGejala)->get();

            session([
                'hasil_diagnosa'  => $finalResults,
                'gejala_terpilih' => $gejalaTerpilih,
                'gambar_path'     => $gambarPath,
                'timestamp'       => now()->format('d M Y, H:i'),
            ]);

            return redirect()->route('diagnosa.hasil');
        } catch (\Exception $e) {
            // Log error ke sistem agar bisa dilacak di Logs Render
            Log::error('DiagnosaController@diagnosa error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Terjadi kesalahan sistem saat memproses diagnosis. Silakan coba lagi.')
                ->withInput();
        }
    }

    /**
     * Display diagnosis results.
     */
    public function hasil(): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        try {
            if (!session()->has('hasil_diagnosa')) {
                return redirect()->route('diagnosa.index')
                    ->with('info', 'Silakan lakukan diagnosis terlebih dahulu.');
            }

            $hasilDiagnosa  = session('hasil_diagnosa');
            $gejalaTerpilih = session('gejala_terpilih');
            $gambarPath     = session('gambar_path');
            $timestamp      = session('timestamp');
            $artikels       = Artikel::latest()->take(3)->get();

            return view('diagnosa_hasil', compact(
                'hasilDiagnosa',
                'gejalaTerpilih',
                'gambarPath',
                'timestamp',
                'artikels'
            ));
        } catch (\Exception $e) {
            Log::error('DiagnosaController@hasil error: ' . $e->getMessage());
            return redirect()->route('diagnosa.index')
                ->with('error', 'Gagal memuat hasil diagnosis.');
        }
    }

    /**
     * Clear session and reset diagnosis.
     */
    public function reset(): \Illuminate\Http\RedirectResponse
    {
        session()->forget(['hasil_diagnosa', 'gejala_terpilih', 'gambar_path', 'timestamp']);
        return redirect()->route('diagnosa.index')->with('info', 'Diagnosis telah direset. Silakan mulai diagnosis baru.');
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    private function hitungCertaintyFactor(array $selectedGejala): array
    {
        $cfResults = [];

        foreach ($this->knowledgeBase as $kodePenyakit => $gejalaKB) {
            $cfKombinasi = 0.0;
            $matched     = 0;

            foreach ($selectedGejala as $kodeGejala) {
                if (!isset($gejalaKB[$kodeGejala])) {
                    continue;
                }

                $mb = $gejalaKB[$kodeGejala];
                $md = $this->defaultMD;

                $cfEvidence = $mb - $md;

                if ($cfEvidence <= 0) {
                    continue;
                }

                if ($cfKombinasi === 0.0) {
                    $cfKombinasi = $cfEvidence;
                } else {
                    $cfKombinasi = $cfKombinasi + $cfEvidence * (1 - $cfKombinasi);
                }

                $matched++;
            }

            if ($matched > 0 && $cfKombinasi > 0.05) {
                $cfResults[] = [
                    'kode'    => $kodePenyakit,
                    'cf'      => round(min($cfKombinasi, 1.0), 4),
                    'matched' => $matched,
                ];
            }
        }

        return $cfResults;
    }

    private function getCFLevel(float $cf): string
    {
        return match (true) {
            $cf >= 0.8  => 'Sangat Tinggi',
            $cf >= 0.6  => 'Tinggi',
            $cf >= 0.4  => 'Sedang',
            $cf >= 0.2  => 'Rendah',
            default     => 'Sangat Rendah',
        };
    }

    private function getCFColor(float $cf): string
    {
        return match (true) {
            $cf >= 0.8  => 'emerald',
            $cf >= 0.6  => 'green',
            $cf >= 0.4  => 'yellow',
            $cf >= 0.2  => 'orange',
            default     => 'red',
        };
    }
}