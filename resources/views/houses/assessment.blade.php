<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; width:100%;">
            <div>
                <div class="kicker">
                    EVALUASI & ASSESSMENT RTLH
                </div>
                <h1 style="font-size:28px; font-weight:800; color:var(--slate-900); margin:0;">
                    Assessment RTLH — {{ $house->house_code }}
                </h1>
                <p class="muted" style="margin-top:4px;">
                    Formulir evaluasi tingkat kerusakan dan pembobotan prioritas berbasis 8 indikator fisik bangunan yang selaras dengan data survei rumah.
                </p>
            </div>

            <div class="actions" style="margin:0;">
                <a href="{{ route('houses.show', $house) }}" class="btn">
                    Kembali ke Detail
                </a>
            </div>
        </div>
    </x-slot>

    <div style="max-width: 960px; margin: 0 auto; padding-bottom: 40px;">
        {{-- ERROR ALERT --}}
        @if ($errors->any())
            <div class="alert danger" style="border-left:4px solid #b91c1c; margin-bottom:20px;">
                <div>
                    <strong>Terdapat kesalahan input:</strong>
                    <ul style="margin:6px 0 0 16px; font-size:13px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- SUCCESS ALERT --}}
        @if (session('success'))
            <div class="alert ok" style="margin-bottom:20px;">
                <div>{{ session('success') }}</div>
            </div>
        @endif

        {{-- LIVE SCORE CARD --}}
        <div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border-left:4px solid var(--primary-600);">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                <div>
                    <div style="font-size:12px; font-weight:800; text-transform:uppercase; color:var(--primary-700); letter-spacing:0.5px;">
                        Standar Skoring Penilaian RTLH (8 Indikator Fisik)
                    </div>
                    <div style="display:flex; gap:10px; margin-top:8px; flex-wrap:wrap;">
                        <span class="badge" style="background:#ecfdf5; color:#047857; font-weight:700;">BAIK = 0</span>
                        <span class="badge" style="background:#fef3c7; color:#b45309; font-weight:700;">RUSAK RINGAN = 1</span>
                        <span class="badge" style="background:#ffedd5; color:#c2410c; font-weight:700;">RUSAK SEDANG = 2</span>
                        <span class="badge" style="background:#fee2e2; color:#b91c1c; font-weight:700;">RUSAK BERAT = 3</span>
                    </div>
                    <div style="font-size:11px; color:var(--slate-500); margin-top:6px;">
                        * Kerusakan berat pada komponen struktur (Sloof/Kolom/Balok/Rangka Atap) otomatis menjadikan <strong>Prioritas Tinggi</strong>.
                    </div>
                </div>

                <div style="display:flex; align-items:center; gap:24px;">
                    <div style="text-align:right;">
                        <div class="muted" style="font-size:11px;">SKOR TOTAL REAL-TIME</div>
                        <div id="live-total-score" style="font-size:28px; font-weight:900; color:var(--primary-700); font-family:monospace;">
                            {{ $surveyScoreData['total_score'] ?? 0 }}
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div class="muted" style="font-size:11px;">TINGKAT PRIORITAS</div>
                        <span id="live-priority-badge" class="badge" style="font-size:13px; font-weight:800; padding:6px 12px;">
                            Prioritas {{ ucfirst($surveyScoreData['priority_level'] ?? 'rendah') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('houses.assessment.update', $house) }}" id="assessmentForm">
            @csrf
            @method('PUT')

            {{-- 1. INFORMASI POKOK ASSESSMENT --}}
            <div class="card" style="margin-bottom: 24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                    <div class="stat-widget-icon purple" style="width:34px; height:34px; font-size:15px; margin:0;">
                        1
                    </div>
                    <div>
                        <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                            Waktu & Status Verifikasi Assessment
                        </h3>
                        <p class="muted" style="font-size:12px; margin:0;">Informasi tahun survei, tanggal verifikasi, dan status alur data assessment.</p>
                    </div>
                </div>

                <div class="grid3">
                    <div class="form-group">
                        <label for="assessment_year">Tahun Assessment <span style="color:#e11d48;">*</span></label>
                        <input
                            type="number"
                            id="assessment_year"
                            name="assessment_year"
                            value="{{ old('assessment_year', $assessment?->assessment_year ?? $house->survey_year ?? now()->year) }}"
                            min="2000"
                            max="2100"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="assessment_date">Tanggal Survei / Penilaian <span style="color:#e11d48;">*</span></label>
                        <input
                            type="date"
                            id="assessment_date"
                            name="assessment_date"
                            value="{{ old('assessment_date', $assessment?->assessment_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="status">Status Verifikasi <span style="color:#e11d48;">*</span></label>
                        <select id="status" name="status" required>
                            <option value="draft" @selected(old('status', $assessment?->status ?? 'draft') === 'draft')>Draft (Penyusunan)</option>
                            <option value="completed" @selected(old('status', $assessment?->status) === 'completed')>Selesai (Completed)</option>
                            <option value="verified" @selected(old('status', $assessment?->status) === 'verified')>Terverifikasi (Verified)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label for="notes">Catatan Umum & Rekomendasi Penilai</label>
                    <textarea
                        id="notes"
                        name="notes"
                        rows="3"
                        placeholder="Tuliskan catatan khusus terkait kondisi sosial ekonomi, kendala teknis lapangan, atau urgensi bantuan..."
                    >{{ old('notes', $assessment?->notes) }}</textarea>
                </div>
            </div>

            {{-- 2. DELAPAN PARAMETER KELAYAKAN FISIK BANGUNAN --}}
            @php
                $structuralKeys = ['structure_sloof', 'structure_column', 'structure_beam', 'roof_frame_condition'];
                $nonStructuralKeys = ['floor_condition', 'wall_condition', 'ceiling_condition', 'roof_condition'];
            @endphp

            <div class="card" style="margin-bottom: 24px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                    <div style="display:flex; align-items:center; gap:10px;">
                        <div class="stat-widget-icon emerald" style="width:34px; height:34px; font-size:15px; margin:0;">
                            2
                        </div>
                        <div>
                            <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                                8 Parameter Kondisi Fisik Rumah (Standar RTLH)
                            </h3>
                            <p class="muted" style="font-size:12px; margin:0;">
                                Nilai otomatis tersinkronkan dengan data kondisi fisik rumah. Mengubah opsi di sini akan memperbarui kondisi teknis bangunan.
                            </p>
                        </div>
                    </div>
                    <span class="badge published" style="font-size:11px;">
                        8 Indikator Terpadu
                    </span>
                </div>

                {{-- SUB-SECTION A: KOMPONEN STRUKTURAL --}}
                <div style="margin-bottom:20px;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                        <span class="badge" style="background:#ede9fe; color:#6d28d9; font-weight:800;">
                            ASPEK KESELAMATAN STRUKTUR
                        </span>
                        <span class="muted" style="font-size:12px;">(Sloof, Kolom, Balok, Rangka Atap)</span>
                    </div>

                    <div class="grid2" style="gap:16px;">
                        @foreach ($structuralKeys as $code)
                            @php
                                $comp = $components[$code] ?? null;
                                if (!$comp) continue;
                                $selectedId = old('conditions.' . $code, $comp['condition_id']);
                                $customNote = old('item_notes.' . $code, $existingItemNotes[$code] ?? '');
                            @endphp

                            <div style="background:var(--slate-50); border:1px solid var(--slate-200); border-radius:var(--radius-md); padding:16px;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                                    <label for="cond_{{ $code }}" style="font-size:13px; font-weight:700; color:var(--slate-800); margin:0;">
                                        {{ $comp['name'] }}
                                    </label>
                                    <span class="badge" style="background:#fee2e2; color:#b91c1c; font-size:10px;">Struktural</span>
                                </div>

                                <select
                                    id="cond_{{ $code }}"
                                    name="conditions[{{ $code }}]"
                                    class="assessment-param-select"
                                    data-structural="true"
                                    style="background:#fff; margin-bottom:8px;"
                                >
                                    <option value="" data-score="0">-- Pilih Kondisi --</option>
                                    @foreach ($conditions as $val)
                                        @php
                                            $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($val->code, $val->label, $val->sort_order);
                                        @endphp
                                        <option
                                            value="{{ $val->id }}"
                                            data-score="{{ $sc }}"
                                            @selected((string)$selectedId === (string)$val->id)
                                        >
                                            {{ $val->label }} (Skor: {{ $sc }})
                                        </option>
                                    @endforeach
                                </select>

                                <div>
                                    <input
                                        type="text"
                                        name="item_notes[{{ $code }}]"
                                        value="{{ $customNote }}"
                                        placeholder="Catatan teknis khusus {{ $comp['name'] }}..."
                                        style="font-size:12px; padding:6px 10px; background:#fff;"
                                    >
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- SUB-SECTION B: KOMPONEN NON-STRUKTURAL / ARSITEKTURAL --}}
                <div>
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:12px;">
                        <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:800;">
                            ASPEK ARSITEKTUR & PENUTUP
                        </span>
                        <span class="muted" style="font-size:12px;">(Lantai, Dinding, Plafon, Penutup Atap)</span>
                    </div>

                    <div class="grid2" style="gap:16px;">
                        @foreach ($nonStructuralKeys as $code)
                            @php
                                $comp = $components[$code] ?? null;
                                if (!$comp) continue;
                                $selectedId = old('conditions.' . $code, $comp['condition_id']);
                                $customNote = old('item_notes.' . $code, $existingItemNotes[$code] ?? '');
                            @endphp

                            <div style="background:var(--slate-50); border:1px solid var(--slate-200); border-radius:var(--radius-md); padding:16px;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                                    <label for="cond_{{ $code }}" style="font-size:13px; font-weight:700; color:var(--slate-800); margin:0;">
                                        {{ $comp['name'] }}
                                    </label>
                                    <span class="badge" style="background:#f1f5f9; color:var(--slate-600); font-size:10px;">Pengisi</span>
                                </div>

                                <select
                                    id="cond_{{ $code }}"
                                    name="conditions[{{ $code }}]"
                                    class="assessment-param-select"
                                    data-structural="false"
                                    style="background:#fff; margin-bottom:8px;"
                                >
                                    <option value="" data-score="0">-- Pilih Kondisi --</option>
                                    @foreach ($conditions as $val)
                                        @php
                                            $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($val->code, $val->label, $val->sort_order);
                                        @endphp
                                        <option
                                            value="{{ $val->id }}"
                                            data-score="{{ $sc }}"
                                            @selected((string)$selectedId === (string)$val->id)
                                        >
                                            {{ $val->label }} (Skor: {{ $sc }})
                                        </option>
                                    @endforeach
                                </select>

                                <div>
                                    <input
                                        type="text"
                                        name="item_notes[{{ $code }}]"
                                        value="{{ $customNote }}"
                                        placeholder="Catatan teknis khusus {{ $comp['name'] }}..."
                                        style="font-size:12px; padding:6px 10px; background:#fff;"
                                    >
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="card" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px; padding:18px 24px;">
                <a href="{{ route('houses.show', $house) }}" class="btn">
                    Batal
                </a>

                <button type="submit" class="btn primary large">
                    Simpan Penilaian Assessment
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selects = document.querySelectorAll('.assessment-param-select');
            const totalScoreEl = document.getElementById('live-total-score');
            const priorityBadgeEl = document.getElementById('live-priority-badge');

            function recalculateScores() {
                let sum = 0;
                let hasSevereStructuralDamage = false;

                selects.forEach(sel => {
                    const opt = sel.options[sel.selectedIndex];
                    const score = parseInt(opt?.dataset?.score || 0, 10);
                    const isStructural = sel.dataset.structural === 'true';

                    sum += score;

                    if (isStructural && score >= 3) {
                        hasSevereStructuralDamage = true;
                    }
                });

                if (totalScoreEl) {
                    totalScoreEl.textContent = sum;
                }

                if (priorityBadgeEl) {
                    if (sum >= 12 || hasSevereStructuralDamage) {
                        priorityBadgeEl.textContent = 'Prioritas Tinggi';
                        priorityBadgeEl.style.background = '#fee2e2';
                        priorityBadgeEl.style.color = '#b91c1c';
                    } else if (sum >= 6) {
                        priorityBadgeEl.textContent = 'Prioritas Sedang';
                        priorityBadgeEl.style.background = '#ffedd5';
                        priorityBadgeEl.style.color = '#c2410c';
                    } else {
                        priorityBadgeEl.textContent = 'Prioritas Rendah';
                        priorityBadgeEl.style.background = '#ecfdf5';
                        priorityBadgeEl.style.color = '#047857';
                    }
                }
            }

            selects.forEach(sel => {
                sel.addEventListener('change', recalculateScores);
            });

            recalculateScores();
        });
    </script>
</x-app-layout>
