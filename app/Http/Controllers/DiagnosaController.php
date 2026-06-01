<?php

// =============================================================================
// FILE: app/Http/Requests/DiagnosaRequest.php
// =============================================================================

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class DiagnosaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'plant_id'  => ['required', 'integer', 'exists:plants,id'],
            'nama_user' => ['nullable', 'string', 'max:100'],

            /*
             * gejala[] — the selected symptom IDs from the wizard.
             * Each ID must exist in the gejala table.
             */
            'gejala'    => ['required', 'array', 'min:1'],
            'gejala.*'  => ['required', 'integer', 'exists:gejala,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'plant_id.required' => 'Pilih jenis tanaman terlebih dahulu.',
            'plant_id.exists'   => 'Tanaman yang dipilih tidak valid.',
            'gejala.required'   => 'Pilih minimal satu gejala sebelum menjalankan diagnosis.',
            'gejala.min'        => 'Pilih minimal :min gejala untuk melanjutkan.',
            'gejala.*.exists'   => 'Salah satu gejala yang dipilih tidak valid. Silakan muat ulang halaman.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'errors'  => $validator->errors(),
                ], 422)
            );
        }

        throw new HttpResponseException(
            redirect()->back()->withErrors($validator)->withInput()
        );
    }
}

// =============================================================================
// FILE: app/Http/Controllers/DiagnosaController.php
// =============================================================================

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

class DiagnosaController extends Controller
{
    /*
     * Inject DiagnosisService via constructor injection.
     * Laravel's container resolves it automatically — no manual
     * instantiation needed in the controller.
     */
    public function __construct(private readonly DiagnosisService $diagnosisService)
    {
    }

    // -------------------------------------------------------------------------
    // Step 1 — Plant Selection
    // -------------------------------------------------------------------------

    /**
     * Show the plant selection page (wizard step 1).
     * Users pick which plant they are diagnosing.
     */
    public function selectPlant(): View
    {
        // Pastikan Anda sudah mengimpor:
        // use App\Models\Plant;
        // use Illuminate\Support\Facades\DB;
        // use Illuminate\View\View;

        $plants = Plant::where('is_active', DB::raw('true'))
            ->orderBy('nama_tanaman', 'asc')
            ->get();

        return view('diagnosa.select-plant', compact('plants'));
    }
    // -------------------------------------------------------------------------
    // Step 2 — Symptom Selection (the wizard form)
    // -------------------------------------------------------------------------

    /**
     * Show the symptom selection form for a given plant (wizard step 2).
     *
     * Symptoms are loaded grouped by category so the Blade wizard
     * can render tabs/steps per category without additional queries.
     *
     * The variable is named $gejala (not $gejalas) per spec.
     */
    public function index(Request $request, int $plantId): View
    {
        $plant = Plant::where('is_active', true)->findOrFail($plantId);

        /*
         * Load categories with their symptoms in one eager-loaded query.
         * This prevents N+1 queries in the Blade template.
         *
         * Result structure:
         *   SymptomCategory → has many Gejala
         *
         * The variable $gejala below is the flat collection used for
         * backward-compatibility with old() restoring checkbox state.
         */
        $categories = SymptomCategory::with([
            'gejala' => fn ($q) => $q->where('is_active', true)->orderBy('kode'),
        ])
            ->where('plant_id', $plantId)
            ->orderBy('urutan')
            ->get();

        // Flat collection for old() restoration in the view
        $gejala = $categories->flatMap->gejala;

        // If the request carries an image analysis payload (from Gemini/Roboflow),
        // parse it and pass pre-filled IDs to the view so JS can auto-check them.
        $prefilledIds = [];
        if ($request->has('image_payload')) {
            $payload      = json_decode($request->input('image_payload'), true) ?? [];
            $prefilledIds = $this->diagnosisService->parseImageAnalysisPayload($payload);
        }

        return view('diagnosa.index', compact(
            'plant',
            'categories',
            'gejala',          // flat collection — variable name matches spec
            'prefilledIds',    // IDs to auto-check (from image analysis)
        ));
    }

    // -------------------------------------------------------------------------
    // Step 3 — Run Diagnosis (POST handler)
    // -------------------------------------------------------------------------

    /**
     * Process the submitted symptom form, run the hybrid inference engine,
     * and redirect to the results page.
     *
     * Using Post-Redirect-Get (PRG) pattern:
     *   POST /diagnosa/hitung → redirect → GET /diagnosa/hasil/{sessionCode}
     *
     * This prevents double-submission on browser refresh.
     */
    public function hitung(DiagnosaRequest $request): RedirectResponse
    {
        $validated      = $request->validated();
        $selectedIds    = $validated['gejala'];
        $plantId        = (int) $validated['plant_id'];
        $namaUser       = $validated['nama_user'] ?? null;

        try {
            $session = $this->diagnosisService->diagnose(
                selectedGejalaIds: $selectedIds,
                plantId:           $plantId,
                namaUser:          $namaUser,
                ipAddress:         $request->ip(),
                userAgent:         $request->userAgent(),
            );

            return redirect()
                ->route('diagnosa.hasil', $session->session_code)
                ->with('success', 'Diagnosis berhasil dijalankan.');

        } catch (\Throwable $e) {
            Log::error('DiagnosisService failed', [
                'plant_id'    => $plantId,
                'gejala_ids'  => $selectedIds,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['engine' => 'Terjadi kesalahan pada mesin inferensi. Silakan coba lagi.']);
        }
    }

    // -------------------------------------------------------------------------
    // Step 4 — Show Results
    // -------------------------------------------------------------------------

    /**
     * Display the diagnosis results page.
     *
     * Uses session_code (not ID) in the URL so IDs are not enumerable.
     */
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

        $hasil         = $session->results;        // Collection<DiagnosisResult>
        $gejalaDipilih = $session->symptoms        // Collection — gejala objects
            ->map->gejala
            ->filter()
            ->values();
        $namaUser      = $session->nama_user;
        $noRule        = $hasil->isEmpty();

        return view('diagnosa.hasil', compact(
            'session',
            'hasil',
            'gejalaDipilih',
            'namaUser',
            'noRule',
        ));
    }

    // -------------------------------------------------------------------------
    // Image Analysis Entry Point (architecture-ready)
    // -------------------------------------------------------------------------

    /**
     * Accept an uploaded plant image, forward it to the image analysis service,
     * and redirect to the symptom form with pre-filled symptom IDs.
     *
     * This endpoint is ARCHITECTURE-READY. The actual Gemini/Roboflow API call
     * is stubbed — wire it in when the integration is ready.
     *
     * @see DiagnosisService::parseImageAnalysisPayload()
     */
    public function analyzeImage(Request $request): RedirectResponse
    {
        $request->validate([
            'plant_id' => ['required', 'integer', 'exists:plants,id'],
            'image'    => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $plantId = (int) $request->input('plant_id');
        $image   = $request->file('image');

        /*
         * ── STUB: Replace this block with actual Gemini/Roboflow API call ──
         *
         * $imagePath   = $image->store('uploads/diagnosis', 'public');
         * $base64Image = base64_encode(Storage::disk('public')->get($imagePath));
         *
         * $geminiResponse = Http::withHeaders(['Authorization' => 'Bearer ' . config('services.gemini.key')])
         *     ->post(config('services.gemini.endpoint'), [
         *         'image'  => $base64Image,
         *         'prompt' => 'Identify visible plant disease symptoms from this image.',
         *     ])
         *     ->json();
         *
         * The payload structure must match DiagnosisService::parseImageAnalysisPayload()
         * ─────────────────────────────────────────────────────────────────────
         */

        // Stub response — remove when real integration is implemented
        $mockPayload = ['detected_symptoms' => []];

        return redirect()
            ->route('diagnosa.index', $plantId)
            ->with('image_payload', json_encode($mockPayload))
            ->with('info', 'Analisis gambar selesai. Gejala yang terdeteksi telah dipilih secara otomatis.');
    }

    // -------------------------------------------------------------------------
    // Rediagnose (from results page)
    // -------------------------------------------------------------------------

    /**
     * Pre-fill the symptom form with the selections from a previous session.
     * Lets the user iterate on a diagnosis without re-selecting from scratch.
     */
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