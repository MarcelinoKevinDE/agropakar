{{-- Educational CF Modal
     Usage: @include('partials.cf-modal')
     Trigger: <button onclick="openCfModal()">...</button>
--}}

<!-- MODAL OVERLAY -->
<div class="nb-modal-overlay" id="cfModal">
    <div class="nb-modal">

        <div class="nb-modal-header">
            <span class="nb-modal-title">CARA KERJA CERTAINTY FACTOR</span>
            <button class="nb-modal-close" onclick="closeCfModal()">X</button>
        </div>

        <div class="nb-modal-body">

            <!-- What is CF -->
            <div class="mb-4">
                <div class="nb-section-header">APA ITU CERTAINTY FACTOR?</div>
                <div class="nb-card" style="border-top: none;">
                    <p class="text-sm" style="line-height: 1.7;">
                        Certainty Factor (CF) adalah nilai numerik antara <strong>0.0</strong>
                        dan <strong>1.0</strong> yang merepresentasikan tingkat kepercayaan
                        seorang pakar terhadap suatu hipotesis (kerusakan) berdasarkan
                        gejala yang diamati. Metode ini diperkenalkan pada sistem pakar
                        medis MYCIN oleh Stanford University.
                    </p>
                    <p class="text-sm mt-2" style="line-height: 1.7;">
                        Dalam sistem ini, setiap aturan (rule) memiliki dua nilai:
                        <strong>MB (Measure of Belief)</strong> yaitu tingkat kepercayaan
                        dan <strong>MD (Measure of Disbelief)</strong> yaitu tingkat
                        ketidakpercayaan, sehingga <strong>CF = MB - MD</strong>.
                    </p>
                </div>
            </div>

            <!-- Scale table -->
            <div class="mb-4">
                <div class="nb-section-header">SKALA NILAI CF</div>
                <table class="nb-table" style="border-top: none;">
                    <thead>
                        <tr>
                            <th>Nilai CF</th>
                            <th>Tingkat Kepercayaan</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>0.80 -- 1.00</strong></td>
                            <td><span class="nb-badge badge-high">SANGAT TINGGI</span></td>
                            <td class="text-sm">Pakar sangat yakin. Kerusakan ini sangat mungkin terjadi.</td>
                        </tr>
                        <tr>
                            <td><strong>0.60 -- 0.79</strong></td>
                            <td><span class="nb-badge badge-med-high">TINGGI</span></td>
                            <td class="text-sm">Pakar cukup yakin. Kemungkinan besar kerusakan ini.</td>
                        </tr>
                        <tr>
                            <td><strong>0.40 -- 0.59</strong></td>
                            <td><span class="nb-badge badge-medium">SEDANG</span></td>
                            <td class="text-sm">Ada bukti, namun belum cukup meyakinkan.</td>
                        </tr>
                        <tr>
                            <td><strong>0.00 -- 0.39</strong></td>
                            <td><span class="nb-badge badge-low">RENDAH</span></td>
                            <td class="text-sm">Bukti lemah. Pertimbangkan kemungkinan lain.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Formula -->
            <div class="mb-4">
                <div class="nb-section-header">RUMUS KOMBINASI CF</div>
                <div class="nb-card" style="border-top: none;">
                    <p class="text-sm mb-2">
                        Saat lebih dari satu gejala mengarah ke kerusakan yang sama,
                        nilai CF digabungkan secara berurutan menggunakan rumus:
                    </p>
                    <div style="background: var(--black); color: var(--yellow);
                                padding: 1.25rem; text-align: center;
                                font-family: 'IBM Plex Mono', monospace;
                                font-size: 0.95rem; margin: 0.75rem 0;
                                border: var(--border);">
                        CF(A, B) = CF(A) + CF(B) x (1 - CF(A))
                    </div>
                    <p class="text-xs text-muted mt-2" style="line-height: 1.6;">
                        Rumus ini memastikan nilai CF tidak pernah melebihi 1.0 (100% kepastian)
                        dan setiap gejala baru meningkatkan kepercayaan secara proporsional.
                        CF per aturan dihitung dari: CF(rule) = MB - MD
                    </p>
                </div>
            </div>

            <!-- Worked example -->
            <div>
                <div class="nb-section-header">CONTOH PERHITUNGAN</div>
                <div style="border: var(--border); border-top: none;">
                    <div style="padding: 0.75rem 1rem; background: #f0f0e8; font-size: 0.78rem; border-bottom: 2px solid var(--black);">
                        Misalnya 3 gejala mengarah ke "Karburator Tersumbat" dengan MB=0.7, MD=0.1 | MB=0.8, MD=0.1 | MB=0.6, MD=0.1
                    </div>

                    <div class="nb-step">
                        <div class="nb-step-num">01</div>
                        <div class="nb-step-body">
                            <strong>Gejala 1</strong> — CF = 0.7 - 0.1 = 0.6
                            <div class="nb-step-formula">CF = 0.0000 + 0.6000 x (1 - 0.0000) = <strong>0.6000</strong></div>
                        </div>
                    </div>

                    <div class="nb-step">
                        <div class="nb-step-num">02</div>
                        <div class="nb-step-body">
                            <strong>Gejala 2</strong> — CF = 0.8 - 0.1 = 0.7
                            <div class="nb-step-formula">CF = 0.6000 + 0.7000 x (1 - 0.6000) = <strong>0.8800</strong></div>
                        </div>
                    </div>

                    <div class="nb-step">
                        <div class="nb-step-num">03</div>
                        <div class="nb-step-body">
                            <strong>Gejala 3</strong> — CF = 0.6 - 0.1 = 0.5
                            <div class="nb-step-formula">CF = 0.8800 + 0.5000 x (1 - 0.8800) = <strong>0.9400</strong></div>
                        </div>
                    </div>

                    <div style="padding: 0.75rem 1rem; background: var(--yellow); border-top: 2px solid var(--black); font-size: 0.8rem; font-weight: 700;">
                        HASIL AKHIR: CF = 0.9400 (94%) -- SANGAT TINGGI -- Diagnosis: Karburator Tersumbat
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>