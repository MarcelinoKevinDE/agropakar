<?php

namespace App\Services;

use App\Models\Gejala;
use App\Models\Disease;
use App\Models\Rule;
use App\Models\DiagnosisSession;
use App\Models\DiagnosisResult;
use App\Models\DiagnosisSymptom;
use App\Models\Plant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * DiagnosisService
 *
 * Implements the three-phase hybrid inference engine:
 *
 *   Phase 1 — Forward Chaining  : filter candidate diseases
 *   Phase 2 — Certainty Factor  : rank candidates by combined CF
 *   Phase 3 — Similarity Match  : fallback / augmentation when CF is low
 *
 * All business logic lives here. The controller only prepares input
 * and renders output.
 */
final class DiagnosisService
{
    /**
     * Minimum CF threshold to trust Phase 2 alone (0–1 scale).
     * Below this value, Phase 3 similarity scores are appended.
     */
    private const CF_THRESHOLD = 0.40;

    /**
     * Maximum number of results to return.
     */
    private const TOP_N = 5;

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Run the full hybrid diagnosis pipeline.
     *
     * @param  array<int>  $selectedGejalaIds   IDs of selected symptoms
     * @param  int         $plantId             Plant context for scoping rules
     * @param  string|null $namaUser            Optional user name
     * @param  string|null $ipAddress
     * @param  string|null $userAgent
     * @param  array|null  $imageAnalysisPayload Pre-filled symptom payload from Gemini/Roboflow
     * @return DiagnosisSession                  Persisted session with eager-loaded results
     */
    public function diagnose(
        array   $selectedGejalaIds,
        int     $plantId,
        ?string $namaUser        = null,
        ?string $ipAddress       = null,
        ?string $userAgent       = null,
        ?array  $imageAnalysisPayload = null,
    ): DiagnosisSession {
        // Phase 1 — Forward Chaining
        $candidates = $this->forwardChaining($selectedGejalaIds, $plantId);

        // Phase 2 — Certainty Factor
        [$cfResults, $topCf] = $this->certaintyFactor($candidates, $selectedGejalaIds);

        // Phase 3 — Similarity Matching (run when CF is weak OR no candidates found)
        $finalResults = [];
        $metodeAkhir  = 'cf';

        if (empty($cfResults) || $topCf < self::CF_THRESHOLD) {
            $similarityResults = $this->similarityMatching($selectedGejalaIds, $plantId);
            $finalResults      = $this->mergeResults($cfResults, $similarityResults);
            $metodeAkhir       = empty($cfResults) ? 'similarity' : 'hybrid';
        } else {
            $finalResults = $cfResults;
        }

        // Take top N, re-rank by final score
        $finalResults = collect($finalResults)
            ->sortByDesc('final_score')
            ->values()
            ->take(self::TOP_N)
            ->all();

        // Persist everything inside a transaction
        return DB::transaction(function () use (
            $finalResults, $selectedGejalaIds, $plantId,
            $namaUser, $ipAddress, $userAgent,
            $imageAnalysisPayload, $metodeAkhir
        ) {
            return $this->persistSession(
                finalResults:         $finalResults,
                selectedIds:          $selectedGejalaIds,
                plantId:              $plantId,
                namaUser:             $namaUser,
                ipAddress:            $ipAddress,
                userAgent:            $userAgent,
                imageAnalysisPayload: $imageAnalysisPayload,
                metodeAkhir:          $metodeAkhir,
            );
        });
    }

    // -------------------------------------------------------------------------
    // Phase 1 — Forward Chaining
    // -------------------------------------------------------------------------

    /**
     * Filter candidate diseases by finding all diseases that have at least one
     * rule matching a selected symptom.
     *
     * Returns a Collection of Rule models grouped by disease_id, with
     * gejala and disease relationships already loaded.
     *
     * @param  array<int> $selectedIds
     * @param  int        $plantId
     * @return Collection<int, Collection<Rule>>  Keyed by disease_id
     */
    private function forwardChaining(array $selectedIds, int $plantId): Collection
    {
        if (empty($selectedIds)) {
            return collect();
        }

        /*
         * Eager-load everything we need in a single query to avoid N+1.
         * We scope to the plant so rules from unrelated plants cannot
         * contaminate the result.
         */
        $matchingRules = Rule::with(['gejala', 'disease.knowledgeBase'])
            ->whereIn('gejala_id', $selectedIds)
            ->whereHas('disease', fn ($q) =>
                $q->where('plant_id', $plantId)->where('is_active', true)
            )
            ->get();

        // Group by disease_id — each group is a candidate
        return $matchingRules->groupBy('disease_id');
    }

    // -------------------------------------------------------------------------
    // Phase 2 — Certainty Factor
    // -------------------------------------------------------------------------

    /**
     * Apply the Certainty Factor algorithm to each candidate disease.
     *
     * Formula:
     *   CF(rule)     = MB - MD
     *   CF(A, B)     = CF(A) + CF(B) × (1 - CF(A))
     *
     * Returns the ranked results array and the top CF value.
     *
     * @param  Collection<int, Collection<Rule>> $candidates
     * @param  array<int>                        $selectedIds
     * @return array{0: array, 1: float}         [results, topCfValue]
     */
    private function certaintyFactor(Collection $candidates, array $selectedIds): array
    {
        if ($candidates->isEmpty()) {
            return [[], 0.0];
        }

        $results = [];

        foreach ($candidates as $diseaseId => $rules) {
            $disease    = $rules->first()->disease;
            $cfCombined = 0.0;
            $steps      = [];

            foreach ($rules as $rule) {
                $mb     = (float) $rule->mb;
                $md     = (float) $rule->md;
                $cfRule = $mb - $md;
                $cfPrev = $cfCombined;

                // CF combination formula
                $cfCombined = $cfPrev + ($cfRule * (1.0 - $cfPrev));

                $steps[] = [
                    'gejala_id'   => $rule->gejala_id,
                    'nama_gejala' => $rule->gejala->nama_gejala ?? '—',
                    'kode_gejala' => $rule->gejala->kode ?? '—',
                    'mb'          => $mb,
                    'md'          => $md,
                    'cf_rule'     => round($cfRule, 4),
                    'cf_prev'     => round($cfPrev, 4),
                    'cf_new'      => round($cfCombined, 4),
                    'formula'     => sprintf(
                        'CF = %.4f + (%.4f - %.4f) × (1 - %.4f) = %.4f',
                        $cfPrev, $mb, $md, $cfPrev, $cfCombined
                    ),
                ];
            }

            // Clamp to [0, 1]
            $cfFinal    = max(0.0, min(1.0, $cfCombined));
            $cfPercent  = round($cfFinal * 100, 2);

            $results[] = [
                'disease_id'       => $diseaseId,
                'disease'          => $disease,
                'cf_value'         => round($cfFinal, 4),
                'cf_percentage'    => $cfPercent,
                'similarity_score' => 0.0,
                'final_score'      => $cfFinal,     // used for merge-sort
                'metode'           => 'cf',
                'cf_steps'         => $steps,
            ];
        }

        // Sort descending by CF
        usort($results, fn ($a, $b) => $b['cf_value'] <=> $a['cf_value']);

        $topCf = !empty($results) ? $results[0]['cf_value'] : 0.0;

        return [$results, $topCf];
    }

    // -------------------------------------------------------------------------
    // Phase 3 — Similarity Matching
    // -------------------------------------------------------------------------

    /**
     * Calculate Jaccard-style similarity for every disease in the plant:
     *
     *   similarity = |selected ∩ disease_symptoms| / |disease_symptoms|
     *
     * This is used as a fallback when CF is below threshold, ensuring we
     * always return a Top-5 even with sparse symptom data.
     *
     * @param  array<int> $selectedIds
     * @param  int        $plantId
     * @return array<int, array>  Indexed result array sorted by similarity desc
     */
    private function similarityMatching(array $selectedIds, int $plantId): array
    {
        if (empty($selectedIds)) {
            return [];
        }

        /*
         * Load all active diseases for this plant, with their rule gejala_ids.
         * We use a single query with eager loading to stay efficient.
         */
        $diseases = Disease::with(['rules.gejala', 'knowledgeBase'])
            ->where('plant_id', $plantId)
            ->where('is_active', true)
            ->get();

        $results = [];

        foreach ($diseases as $disease) {
            $diseaseSymptomIds = $disease->rules->pluck('gejala_id')->toArray();
            $totalSymptoms     = count($diseaseSymptomIds);

            if ($totalSymptoms === 0) {
                continue;
            }

            $matchingCount = count(array_intersect($selectedIds, $diseaseSymptomIds));
            $similarity    = $matchingCount / $totalSymptoms;

            if ($similarity <= 0) {
                continue;
            }

            $results[] = [
                'disease_id'       => $disease->id,
                'disease'          => $disease,
                'cf_value'         => 0.0,
                'cf_percentage'    => 0.0,
                'similarity_score' => round($similarity, 4),
                'final_score'      => $similarity,
                'metode'           => 'similarity',
                'cf_steps'         => [],
            ];
        }

        usort($results, fn ($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);

        return $results;
    }

    // -------------------------------------------------------------------------
    // Merge Phase 2 + Phase 3
    // -------------------------------------------------------------------------

    /**
     * Merge CF results with similarity results.
     *
     * Strategy:
     *  - For diseases that appear in BOTH: combine as final_score = CF + (similarity * 0.2)
     *    and set metode = 'hybrid'
     *  - For diseases only in similarity: keep similarity score, metode = 'similarity'
     *  - For diseases only in CF: keep CF score, metode = 'cf'
     *
     * This ensures the CF result dominates while similarity provides a useful
     * boost and fills in gaps.
     *
     * @param  array $cfResults
     * @param  array $similarityResults
     * @return array
     */
    private function mergeResults(array $cfResults, array $similarityResults): array
    {
        // Index CF results by disease_id for O(1) lookups
        $cfByDiseaseId = collect($cfResults)->keyBy('disease_id');

        $merged = collect($cfResults)->keyBy('disease_id');

        foreach ($similarityResults as $simResult) {
            $id = $simResult['disease_id'];

            if ($cfByDiseaseId->has($id)) {
                // Disease appears in both — boost CF score with similarity
                $existing               = $merged[$id];
                $existing['similarity_score'] = $simResult['similarity_score'];
                $existing['final_score']      = $existing['cf_value'] + ($simResult['similarity_score'] * 0.2);
                $existing['metode']           = 'hybrid';
                $merged[$id]            = $existing;
            } else {
                // Disease only in similarity
                $merged[$id] = $simResult;
            }
        }

        return $merged->values()->all();
    }

    // -------------------------------------------------------------------------
    // Persistence
    // -------------------------------------------------------------------------

    /**
     * Persist the session, selected symptoms, and ranked results to the database.
     *
     * @param  array       $finalResults
     * @param  array<int>  $selectedIds
     * @param  int         $plantId
     * @param  string|null $namaUser
     * @param  string|null $ipAddress
     * @param  string|null $userAgent
     * @param  array|null  $imageAnalysisPayload
     * @param  string      $metodeAkhir
     * @return DiagnosisSession
     */
    private function persistSession(
        array   $finalResults,
        array   $selectedIds,
        int     $plantId,
        ?string $namaUser,
        ?string $ipAddress,
        ?string $userAgent,
        ?array  $imageAnalysisPayload,
        string  $metodeAkhir,
    ): DiagnosisSession {
        // Create the session record
        $session = DiagnosisSession::create([
            'session_code'          => $this->generateSessionCode(),
            'nama_user'             => $namaUser,
            'plant_id'              => $plantId,
            'metode_akhir'          => $metodeAkhir,
            'ip_address'            => $ipAddress,
            'user_agent'            => $userAgent,
            'image_analysis_payload'=> $imageAnalysisPayload,
        ]);

        // Persist selected symptoms
        $symptomRows = array_map(
            fn (int $gejalaId) => [
                'session_id' => $session->id,
                'gejala_id'  => $gejalaId,
                'prefilled_by_image' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            $selectedIds
        );

        DiagnosisSymptom::insert($symptomRows);

        // Persist ranked results
        foreach ($finalResults as $rank => $result) {
            DiagnosisResult::create([
                'session_id'       => $session->id,
                'disease_id'       => $result['disease_id'],
                'cf_value'         => $result['cf_value'],
                'cf_percentage'    => $result['cf_percentage'],
                'similarity_score' => $result['similarity_score'],
                'metode'           => $result['metode'],
                'peringkat'        => $rank + 1,
                'cf_steps'         => $result['cf_steps'] ?: null,
            ]);
        }

        // Return with eager-loaded relationships for the view
        return $session->load([
            'results.disease.knowledgeBase',
            'symptoms.gejala',
            'plant',
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Generate a unique, human-readable session code.
     * Format: DIAG-YYYYMMDD-XXXX (e.g. DIAG-20240101-A3F7)
     */
    private function generateSessionCode(): string
    {
        do {
            $code = 'DIAG-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (DiagnosisSession::where('session_code', $code)->exists());

        return $code;
    }

    // -------------------------------------------------------------------------
    // Image Analysis — Architecture-Ready Methods
    // -------------------------------------------------------------------------

    /**
     * Parse a Gemini Vision or Roboflow response payload into a list of
     * gejala IDs that can be pre-filled on the diagnosis form.
     *
     * This method defines the CONTRACT between the image analysis provider
     * and the diagnosis form. Implement the provider-specific parsing
     * in the body when integrating.
     *
     * Expected payload structure from Gemini:
     * {
     *   "detected_symptoms": [
     *     { "label": "bercak_kuning", "confidence": 0.87 },
     *     { "label": "daun_layu",     "confidence": 0.72 }
     *   ]
     * }
     *
     * @param  array $payload   Raw JSON-decoded response from image analysis API
     * @param  float $minConf   Minimum confidence to accept a detected symptom
     * @return array<int>       Array of gejala IDs to pre-fill
     */
    public function parseImageAnalysisPayload(array $payload, float $minConf = 0.65): array
    {
        $detectedLabels = collect($payload['detected_symptoms'] ?? [])
            ->filter(fn ($item) => ($item['confidence'] ?? 0) >= $minConf)
            ->pluck('label')
            ->all();

        if (empty($detectedLabels)) {
            return [];
        }

        /*
         * Match detected labels against gejala.kode values.
         * The kode column is the bridge between the image model's vocabulary
         * and the expert system's symptom table.
         */
        return Gejala::whereIn('kode', $detectedLabels)
            ->where('is_active', true)
            ->pluck('id')
            ->all();
    }
}