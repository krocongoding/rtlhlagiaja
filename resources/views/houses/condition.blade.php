<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="kicker">
                KONDISI FISIK & ARSITEKTUR
            </div>
            <h1>Kondisi Fisik Rumah — {{ $house->house_code }}</h1>
            <p class="muted">
                Input dan perbarui data komponen struktural, dinding, lantai, plafon, dan atap rumah. Penilaian skor akan dihitung secara otomatis.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('houses.show', $house) }}" class="btn">
                Kembali ke Detail
            </a>
        </div>
    </x-slot>

    <div style="max-width: 860px; margin: 0 auto;">
        @if ($errors->any())
            <div class="alert danger" style="border-left:4px solid #b91c1c;">
                <div>
                    <strong>Terdapat kesalahan pengisian:</strong>
                    <ul style="margin:6px 0 0 16px; font-size:13px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- RINGKASAN SCORE OTOMATIS --}}
        <div class="card" style="margin-bottom: 20px; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border-left: 4px solid var(--primary-600);">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
                <div>
                    <div style="font-size:12px; font-weight:800; text-transform:uppercase; color:var(--primary-700); letter-spacing:0.5px;">
                        Penilaian Skor Otomatis Berdasarkan Kondisi Fisik
                    </div>
                    <p class="muted" style="font-size:12px; margin:4px 0 0 0;">
                        Standar Skor: <strong>Baik (0)</strong>, <strong>Rusak Ringan (1)</strong>, <strong>Rusak Sedang (2)</strong>, <strong>Rusak Berat (3)</strong>.
                    </p>
                </div>
                <div style="display:flex; align-items:center; gap:20px;">
                    <div style="text-align:right;">
                        <div class="muted" style="font-size:11px;">ESTIMASI SKOR TOTAL</div>
                        <div id="live-total-score" style="font-size:26px; font-weight:900; color:var(--primary-700);">0</div>
                    </div>
                    <div style="text-align:right;">
                        <div class="muted" style="font-size:11px;">STATUS PRIORITAS</div>
                        <span id="live-priority-badge" class="badge priority-low" style="font-size:12px; padding:4px 10px;">
                            Prioritas Rendah
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('houses.condition.update', $house) }}" id="houseConditionForm">
            @csrf
            @method('PUT')

            {{-- 1. STRUKTUR RUMAH --}}
            <div class="card" style="margin-bottom: 20px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                    <div class="stat-widget-icon emerald" style="width:34px; height:34px; font-size:15px; margin:0;">
                        1
                    </div>
                    <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                        Struktur Utama Rumah
                    </h3>
                </div>

                <div class="grid2">
                    <div class="form-group">
                        <label for="foundation">Pondasi <span class="badge" style="font-size:10px; background:#e0f2fe; color:#0369a1; padding:2px 6px;">Pilihan Dropdown</span></label>
                        @php
                            $defaultFoundations = [
                                'BATU KALI / BATU BELAH',
                                'BETON BERTULANG / TAPAK',
                                'BATA MERAH / ROLAG',
                                'KAYU / CERUCUK',
                                'BAMBU',
                                'TANPA PONDASI / MENUMPANG TANAH',
                                'LAINNYA',
                            ];
                            $opts = ($foundationTypes && $foundationTypes->isNotEmpty())
                                ? $foundationTypes->pluck('label')->toArray()
                                : $defaultFoundations;
                            $currentFoundation = old('foundation', $house->structure?->foundation);
                        @endphp
                        <select id="foundation" name="foundation">
                            <option value="">-- Pilih Jenis Pondasi --</option>
                            @foreach ($opts as $opt)
                                <option value="{{ $opt }}" @selected(strtoupper(trim((string)$currentFoundation)) === strtoupper(trim($opt)))>
                                    {{ $opt }}
                                </option>
                            @endforeach
                            @if($currentFoundation && !in_array(strtoupper(trim($currentFoundation)), array_map('strtoupper', array_map('trim', $opts))))
                                <option value="{{ $currentFoundation }}" selected>
                                    {{ $currentFoundation }}
                                </option>
                            @endif
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="sloof_condition_id">Kondisi Sloof / Balok Dasar</label>
                        <select id="sloof_condition_id" name="sloof_condition_id" class="score-input">
                            <option value="" data-score="0">-- Pilih Kondisi --</option>
                            @foreach ($conditions as $item)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($item->code, $item->label, $item->sort_order);
                                @endphp
                                <option value="{{ $item->id }}" data-score="{{ $sc }}" @selected(old('sloof_condition_id', $house->structure?->sloof_condition_id) == $item->id)>
                                    {{ $item->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="column_condition_id">Kondisi Kolom / Tiang Penopang</label>
                        <select id="column_condition_id" name="column_condition_id" class="score-input">
                            <option value="" data-score="0">-- Pilih Kondisi --</option>
                            @foreach ($conditions as $item)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($item->code, $item->label, $item->sort_order);
                                @endphp
                                <option value="{{ $item->id }}" data-score="{{ $sc }}" @selected(old('column_condition_id', $house->structure?->column_condition_id) == $item->id)>
                                    {{ $item->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="beam_condition_id">Kondisi Balok Pengikat</label>
                        <select id="beam_condition_id" name="beam_condition_id" class="score-input">
                            <option value="" data-score="0">-- Pilih Kondisi --</option>
                            @foreach ($conditions as $item)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($item->code, $item->label, $item->sort_order);
                                @endphp
                                <option value="{{ $item->id }}" data-score="{{ $sc }}" @selected(old('beam_condition_id', $house->structure?->beam_condition_id) == $item->id)>
                                    {{ $item->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- 2. LANTAI --}}
            <div class="card" style="margin-bottom: 20px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                    <div class="stat-widget-icon blue" style="width:34px; height:34px; font-size:15px; margin:0;">
                        2
                    </div>
                    <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                        Lantai Rumah
                    </h3>
                </div>

                <div class="grid2">
                    <div class="form-group">
                        <label for="floor_material_id">Bahan Material Lantai</label>
                        <select id="floor_material_id" name="floor_material_id">
                            <option value="">-- Pilih Bahan --</option>
                            @foreach ($floorMaterials as $item)
                                <option value="{{ $item->id }}" @selected(old('floor_material_id', $house->floor?->material_id) == $item->id)>
                                    {{ $item->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="floor_condition_id">Kondisi Lantai</label>
                        <select id="floor_condition_id" name="floor_condition_id" class="score-input">
                            <option value="" data-score="0">-- Pilih Kondisi --</option>
                            @foreach ($conditions as $item)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($item->code, $item->label, $item->sort_order);
                                @endphp
                                <option value="{{ $item->id }}" data-score="{{ $sc }}" @selected(old('floor_condition_id', $house->floor?->condition_id) == $item->id)>
                                    {{ $item->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- 3. DINDING --}}
            <div class="card" style="margin-bottom: 20px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                    <div class="stat-widget-icon purple" style="width:34px; height:34px; font-size:15px; margin:0;">
                        3
                    </div>
                    <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                        Dinding Rumah
                    </h3>
                </div>

                <div class="grid2">
                    <div class="form-group">
                        <label for="wall_material_id">Bahan Material Dinding</label>
                        <select id="wall_material_id" name="wall_material_id">
                            <option value="">-- Pilih Bahan --</option>
                            @foreach ($wallMaterials as $item)
                                <option value="{{ $item->id }}" @selected(old('wall_material_id', $house->wall?->material_id) == $item->id)>
                                    {{ $item->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="wall_condition_id">Kondisi Dinding</label>
                        <select id="wall_condition_id" name="wall_condition_id" class="score-input">
                            <option value="" data-score="0">-- Pilih Kondisi --</option>
                            @foreach ($conditions as $item)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($item->code, $item->label, $item->sort_order);
                                @endphp
                                <option value="{{ $item->id }}" data-score="{{ $sc }}" @selected(old('wall_condition_id', $house->wall?->condition_id) == $item->id)>
                                    {{ $item->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- 4. PLAFON & ATAP --}}
            <div class="card" style="margin-bottom: 24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                    <div class="stat-widget-icon amber" style="width:34px; height:34px; font-size:15px; margin:0;">
                        4
                    </div>
                    <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                        Plafon & Atap Bangunan
                    </h3>
                </div>

                <div class="grid2">
                    <div class="form-group">
                        <label for="ceiling_condition_id">Kondisi Plafon / Langit-langit</label>
                        <select id="ceiling_condition_id" name="ceiling_condition_id" class="score-input">
                            <option value="" data-score="0">-- Pilih Kondisi --</option>
                            @foreach ($conditions as $item)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($item->code, $item->label, $item->sort_order);
                                @endphp
                                <option value="{{ $item->id }}" data-score="{{ $sc }}" @selected(old('ceiling_condition_id', $house->ceiling?->condition_id) == $item->id)>
                                    {{ $item->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="roof_frame_condition_id">Kondisi Rangka Atap</label>
                        <select id="roof_frame_condition_id" name="roof_frame_condition_id" class="score-input">
                            <option value="" data-score="0">-- Pilih Kondisi --</option>
                            @foreach ($conditions as $item)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($item->code, $item->label, $item->sort_order);
                                @endphp
                                <option value="{{ $item->id }}" data-score="{{ $sc }}" @selected(old('roof_frame_condition_id', $house->roof?->frame_condition_id) == $item->id)>
                                    {{ $item->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="roof_material_id">Bahan Penutup Atap</label>
                        <select id="roof_material_id" name="roof_material_id">
                            <option value="">-- Pilih Bahan --</option>
                            @foreach ($roofMaterials as $item)
                                <option value="{{ $item->id }}" @selected(old('roof_material_id', $house->roof?->material_id) == $item->id)>
                                    {{ $item->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="roof_condition_id">Kondisi Penutup Atap</label>
                        <select id="roof_condition_id" name="roof_condition_id" class="score-input">
                            <option value="" data-score="0">-- Pilih Kondisi --</option>
                            @foreach ($conditions as $item)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($item->code, $item->label, $item->sort_order);
                                @endphp
                                <option value="{{ $item->id }}" data-score="{{ $sc }}" @selected(old('roof_condition_id', $house->roof?->condition_id) == $item->id)>
                                    {{ $item->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="actions" style="justify-content: flex-end;">
                <a href="{{ route('houses.show', $house) }}" class="btn">
                    Batal
                </a>
                <button type="submit" class="btn primary">
                    Simpan Kondisi Fisik & Hitung Skor
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scoreInputs = document.querySelectorAll('.score-input');
            const totalScoreEl = document.getElementById('live-total-score');
            const priorityBadgeEl = document.getElementById('live-priority-badge');

            function updateLiveScore() {
                let total = 0;
                let hasStructuralSevere = false;

                scoreInputs.forEach(select => {
                    const selectedOpt = select.options[select.selectedIndex];
                    const score = parseInt(selectedOpt?.dataset?.score || 0, 10);
                    total += score;

                    // Cek kerusakan berat pada struktural
                    if (['sloof_condition_id', 'column_condition_id', 'beam_condition_id', 'roof_frame_condition_id'].includes(select.id)) {
                        if (score >= 3) {
                            hasStructuralSevere = true;
                        }
                    }
                });

                if (totalScoreEl) {
                    totalScoreEl.textContent = total;
                }

                if (priorityBadgeEl) {
                    if (total >= 12 || hasStructuralSevere) {
                        priorityBadgeEl.className = 'badge priority-high';
                        priorityBadgeEl.textContent = 'Prioritas Tinggi (Rusak Berat)';
                    } else if (total >= 6) {
                        priorityBadgeEl.className = 'badge priority-medium';
                        priorityBadgeEl.textContent = 'Prioritas Sedang (Rusak Sedang)';
                    } else {
                        priorityBadgeEl.className = 'badge priority-low';
                        priorityBadgeEl.textContent = 'Prioritas Rendah (Rusak Ringan)';
                    }
                }
            }

            scoreInputs.forEach(select => {
                select.addEventListener('change', updateLiveScore);
            });

            updateLiveScore();
        });
    </script>
</x-app-layout>