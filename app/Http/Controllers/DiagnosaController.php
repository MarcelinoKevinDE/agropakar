<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiagnosaRequest;
use App\Models\Plant;
use App\Models\Gejala;
use App\Models\SymptomCategory;
use App\Models\DiagnosisSession;
use App\Services\DiagnosisService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB; // Perbaikan: Namespace yang benar

class DiagnosaController extends Controller
{
    public function __construct(private readonly DiagnosisService $diagnosisService)
    {
    }

    public function selectPlant(): View
    {
        // Perbaikan: Gunakan boolean true, jangan gunakan DB::raw('true')
        // Dengan Model Casts, ini akan dikonversi dengan benar untuk PostgreSQL
        $plants = Plant::where('is_active', true)
            ->orderBy('nama_tanaman', 'asc')
            ->get();

        return view('diagnosa.select-plant', compact('plants'));
    }

    public function index(Request $request, int $plantId): View
    {
        $plant = Plant::where('is_active', true)->findOrFail($plantId);

        $categories = SymptomCategory::with([
            'gejala' => fn ($q) => $q->where('is_active', true)->orderBy('kode'),
        ])
            ->where('plant_id', $plantId)
            ->orderBy('urutan')
            ->get();

        $gejala = $categories->flatMap->gejala;

        $prefilledIds = [];
        if ($request->has('image_payload')) {
            $payload = json_decode($request->input('image_payload'), true) ?? [];
            $prefilledIds = $this->diagnosisService->parseImageAnalysisPayload($payload);
        }

        return view('diagnosa.index', compact(
            'plant',
            'categories',
            'gejala',
            'prefilledIds',
        ));
    }

    public function hitung(DiagnosaRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $selectedIds = $validated['gejala'];
        $plantId = (int) $validated['plant_id'];
        $namaUser = $validated['nama_user'] ?? null;

        try {
            $session = $this->diagnosisService->diagnose(
                selectedGejalaIds: $selectedIds,
                plantId: $plantId,
                namaUser: $namaUser,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            );

            return redirect()
                ->route('diagnosa.hasil', $session->session_code)
                ->with('success', 'Diagnosis berhasil dijalankan.');

        } catch (\Throwable $e) {
            Log::error('DiagnosisService failed', [
                'plant_id' => $plantId,
                'gejala_ids' => $selectedIds,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['engine' => 'Terjadi kesalahan pada mesin inferensi. Silakan coba lagi.']);
        }
    }

    public function hasil(string $sessionCode): View
    {
        $session = DiagnosisSession::with([
            'results.disease.knowledgeBase',
            'results.disease.plant',
            'symptoms.gejala.category',
            'plant',
        ])
            ->where('session_code', $sessionCode)
            ->firstOrFail();

        $hasil = $session->results;
        $gejalaDipilih = $session->symptoms->map->gejala->filter()->values();
        $namaUser = $session->nama_user;
        $noRule = $hasil->isEmpty();

        return view('diagnosa.hasil', compact(
            'session',
            'hasil',
            'gejalaDipilih',
            'namaUser',
            'noRule',
        ));
    }

    public function analyzeImage(Request $request): RedirectResponse
    {
        $request->validate([
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $plantId = (int) $request->input('plant_id');
        $mockPayload = ['detected_symptoms' => []];

        return redirect()
            ->route('diagnosa.index', $plantId)
            ->with('image_payload', json_encode($mockPayload))
            ->with('info', 'Analisis gambar selesai.');
    }

    public function rediagnose(string $sessionCode): RedirectResponse
    {
        $session = DiagnosisSession::with('symptoms')
            ->where('session_code', $sessionCode)
            ->firstOrFail();

        $previousIds = $session->symptoms->pluck('gejala_id')->implode(',');

        return redirect()
            ->route('diagnosa.index', $session->plant_id)
            ->with('prefilled_ids', $previousIds);
    }
}