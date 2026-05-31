<?php

namespace App\Http\Controllers;

use App\Models\Gejala;
use App\Models\Rule;
use App\Models\DiagnosisHistory;
use App\Http\Requests\DiagnosaRequest;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class DiagnosaController extends Controller
{
    // -------------------------------------------------------------------------
    // STEP 1: Fetch all gejala and pass to the form view.
    //
    // compact('gejala') creates ['gejala' => $gejala].
    // The view receives $gejala — the variable name in the view
    // MUST match the string key passed to compact().
    // -------------------------------------------------------------------------
    public function index(): View
    {
        $gejala = Gejala::orderBy('kode_gejala')->get();

        // TROUBLESHOOTING — uncomment ONE of these lines to verify DB data:
        // dd($gejala);                    // dumps the full collection and dies
        // dd($gejala->count());           // just the count
        // Log::debug('Gejala count', ['count' => $gejala->count()]);

        // When you're satisfied the data is correct, remove the debug line.

        return view('diagnosa.index', compact('gejala'));
    }

    // -------------------------------------------------------------------------
    // STEP 2: Receive POST from the form, validate, run CF calculation.
    //
    // Route name: diagnosa.hitung  (POST /diagnosa/hitung)
    // -------------------------------------------------------------------------
    public function hitung(DiagnosaRequest $request): View
    {
        // validated() is safe — DiagnosaRequest has already confirmed:
        //   - 'gejala' exists and is an array with at least 1 item
        //   - each ID exists in the gejala table
        $validated   = $request->validated();
        $selectedIds = $validated['gejala'];          // array of integer IDs
        $namaUser    = $validated['nama_user'] ?? null;

        // TROUBLESHOOTING — verify what was received:
        // dd($selectedIds);
        // Log::debug('Selected gejala IDs', ['ids' => $selectedIds]);

        // Fetch matching rules with their relationships pre-loaded (avoids N+1)
        $rules = Rule::with(['gejala', 'kerusakan'])
            ->whereIn('gejala_id', $selectedIds)
            ->get();

        if ($rules->isEmpty()) {
            return view('diagnosa.hasil', [
                'hasil'        => [],
                'namaUser'     => $namaUser,
                'gejalaDipilih'=> Gejala::whereIn('id', $selectedIds)->get(),
                'cfSteps'      => [],
                'noRule'       => true,
            ]);
        }

        // -----------------------------------------------------------------
        // Certainty Factor engine
        // CF per rule = MB - MD
        // CF combine   = CF_old + CF_rule * (1 - CF_old)
        // -----------------------------------------------------------------
        $grouped = $rules->groupBy('kerusakan_id');
        $hasil   = [];
        $cfSteps = [];

        foreach ($grouped as $kerusakanId => $groupRules) {
            $kerusakan  = $groupRules->first()->kerusakan;
            $cfCombined = 0.0;
            $steps      = [];

            foreach ($groupRules as $rule) {
                $mb     = (float) $rule->mb;
                $md     = (float) $rule->md;
                $cfRule = $mb - $md;
                $cfPrev = $cfCombined;

                $cfCombined = $cfPrev + $cfRule * (1 - $cfPrev);

                $steps[] = [
                    'nama_gejala' => $rule->gejala->nama_gejala ?? '-',
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

            $cfFinal = max(0.0, min(1.0, $cfCombined));

            $hasil[] = [
                'kerusakan_id'   => $kerusakanId,
                'nama_kerusakan' => $kerusakan->nama_kerusakan,
                'solusi'         => $kerusakan->solusi,
                'cf'             => round($cfFinal, 4),
                'persen'         => round($cfFinal * 100, 2),
                'level'          => DiagnosisHistory::deriveLevel($cfFinal),
            ];

            $cfSteps[$kerusakanId] = [
                'nama_kerusakan' => $kerusakan->nama_kerusakan,
                'steps'          => $steps,
            ];
        }

        usort($hasil, fn($a, $b) => $b['cf'] <=> $a['cf']);

        $gejalaDipilih = Gejala::whereIn('id', $selectedIds)->get();

        DiagnosisHistory::create([
            'nama_user'            => $namaUser,
            'gejala_dipilih'       => $selectedIds,
            'hasil_diagnosa'       => $hasil,
            'cf_calculation_steps' => $cfSteps,
            'ip_address'           => $request->ip(),
            'user_agent'           => $request->userAgent(),
        ]);

        return view('diagnosa.hasil', [
            'hasil'         => $hasil,
            'namaUser'      => $namaUser,
            'gejalaDipilih' => $gejalaDipilih,
            'cfSteps'       => $cfSteps,
            'noRule'        => false,
        ]);
    }

    public function about(): View
    {
        return view('pages.about');
    }
}