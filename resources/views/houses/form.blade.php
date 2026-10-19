<x-layouts.app title="{{ $house->exists ? 'Edit Data RTLH' : 'Tambah Data RTLH' }}">

    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <style>
            .stepper-container {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 24px;
                background: #ffffff;
                border: 1px solid var(--slate-200);
                border-radius: var(--radius-xl);
                padding: 12px 18px;
                box-shadow: var(--shadow-sm);
                gap: 12px;
                overflow-x: auto;
            }
            .stepper-step {
                display: flex;
                align-items: center;
                gap: 12px;
                flex: 1;
                cursor: pointer;
                padding: 8px 12px;
                border-radius: var(--radius-lg);
                transition: all 0.2s ease;
                border: 1px solid transparent;
                text-decoration: none;
            }
            .stepper-step:hover {
                background: var(--slate-50);
            }
            .stepper-step.active {
                background: #f0fdf4;
                border-color: #86efac;
            }
            .stepper-step.completed {
                background: #f8fafc;
            }
            .stepper-badge {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 800;
                font-size: 14px;
                background: var(--slate-100);
                color: var(--slate-600);
                flex-shrink: 0;
                transition: all 0.2s ease;
            }
            .stepper-step.active .stepper-badge {
                background: #16a34a;
                color: #ffffff;
                box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.15);
            }
            .stepper-step.completed .stepper-badge {
                background: #0284c7;
                color: #ffffff;
            }
            .stepper-title {
                font-size: 14px;
                font-weight: 800;
                color: var(--slate-800);
                margin: 0;
                line-height: 1.2;
            }
            .stepper-step.active .stepper-title {
                color: #15803d;
            }
            .stepper-subtitle {
                font-size: 11px;
                color: var(--slate-500);
                margin-top: 2px;
            }
            .stepper-divider {
                color: var(--slate-300);
                font-size: 14px;
                flex-shrink: 0;
            }

            .form-step-pane {
                display: none;
            }
            .form-step-pane.active {
                display: block;
                animation: fadeInStep 0.25s ease;
            }
            @keyframes fadeInStep {
                from { opacity: 0; transform: translateY(6px); }
                to { opacity: 1; transform: translateY(0); }
            }

            .form-section {
                background: #ffffff;
                border: 1px solid var(--slate-200);
                border-radius: var(--radius-xl);
                padding: 24px;
                margin-bottom: 20px;
                box-shadow: var(--shadow-sm);
                transition: all 0.2s ease;
            }
            .form-section:hover {
                border-color: var(--slate-300);
            }
            .form-section-header {
                display: flex;
                align-items: center;
                gap: 14px;
                margin-bottom: 20px;
                border-bottom: 1px solid var(--slate-100);
                padding-bottom: 14px;
            }
            .form-section-badge {
                width: 38px;
                height: 38px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                font-weight: 800;
                flex-shrink: 0;
            }
            .form-section-badge.emerald { background: var(--primary-100); color: var(--primary-700); }
            .form-section-badge.blue { background: #e0f2fe; color: #0284c7; }
            .form-section-badge.amber { background: #fef3c7; color: #d97706; }
            .form-section-badge.purple { background: #ede9fe; color: #7c3aed; }
            .form-section-badge.rose { background: #ffe4e6; color: #e11d48; }

            .form-section-title {
                font-size: 17px;
                font-weight: 800;
                color: var(--slate-900);
                margin: 0;
            }
            .form-section-desc {
                font-size: 13px;
                color: var(--slate-500);
                margin-top: 2px;
            }

            .form-field {
                display: flex;
                flex-direction: column;
                margin-bottom: 16px;
            }
            .form-field label {
                font-size: 13px;
                font-weight: 700;
                color: var(--slate-700);
                margin-bottom: 6px;
                display: flex;
                align-items: center;
                gap: 4px;
            }
            .form-field label span.req {
                color: #e11d48;
            }

            .photo-upload-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
            }
            @media(max-width: 800px) {
                .photo-upload-grid {
                    grid-template-columns: 1fr;
                }
            }

            .photo-dropzone {
                border: 2px dashed var(--slate-300);
                border-radius: var(--radius-lg);
                padding: 20px;
                text-align: center;
                background: var(--slate-50);
                cursor: pointer;
                transition: all 0.2s ease;
                position: relative;
            }
            .photo-dropzone:hover {
                border-color: var(--primary-500);
                background: var(--primary-50);
            }
            .photo-dropzone input[type="file"] {
                display: none;
            }
            .photo-preview {
                max-height: 120px;
                border-radius: 8px;
                object-fit: cover;
                margin-top: 10px;
                display: none;
                width: 100%;
            }

            .step-navigation-bar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #ffffff;
                border: 1px solid var(--slate-200);
                border-radius: var(--radius-xl);
                padding: 16px 24px;
                margin-top: 24px;
                box-shadow: var(--shadow-sm);
                gap: 12px;
                flex-wrap: wrap;
            }

            .realization-radio-card {
                border: 2px solid var(--slate-200);
                border-radius: var(--radius-lg);
                padding: 16px;
                cursor: pointer;
                transition: all 0.2s ease;
                background: #ffffff;
            }
            .realization-radio-card:hover {
                border-color: var(--slate-400);
            }
            .realization-radio-card.active-kuning {
                border-color: #f59e0b;
                background: #fffbeb;
            }
            .realization-radio-card.active-hijau {
                border-color: #10b981;
                background: #f0fdf4;
            }
        </style>
    @endpush

    <div class="pagehead">
        <div>
            <div class="kicker">
                {{ strtoupper($panel ?? 'FORMULIR') }} &bull; 3 LANGKAH INPUT
            </div>
            <h1>{{ $house->exists ? 'Edit Data RTLH' : 'Tambah Data RTLH Baru' }}</h1>
            <p class="muted">
                Formulir bertahap terpadu: Data CPBM, Kondisi Fisik Rumah, dan Sumber Anggaran / Usulan Proposal.
            </p>
        </div>

        <div class="actions" style="display:flex; gap:10px; align-items:center;">
            <button type="button" class="btn" onclick="submitAsDraft()" style="background:#ffffff; color:var(--slate-700); font-weight:600;" title="Simpan data saat ini sebagai draft">
                Simpan Draft
            </button>
            <a href="{{ $panel === 'surveyor' ? route('surveyor.rtlh.index') : route('houses.index') }}" class="btn">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert danger" style="border-left:4px solid #b91c1c; margin-bottom:20px;">
            <div>
                <strong>Terdapat kesalahan input formulir:</strong>
                <ul style="margin:6px 0 0 16px; font-size:13px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if($house->revision_notes)
        <div class="alert danger" style="border-left: 5px solid #e11d48; background: #fff1f2; border-color: #fecaca; margin-bottom: 24px; border-radius: var(--radius-lg); padding: 18px 20px;">
            <div>
                <h4 style="font-size:15px; font-weight:800; color:#9f1239; margin:0 0 4px 0;">
                    Instruksi Catatan Revisi dari Verifikator / Admin:
                </h4>
                <p style="font-size:13.5px; color:#881337; margin:0; line-height:1.6; white-space:pre-wrap; background:#fff; padding:12px 16px; border-radius:var(--radius-md); border:1px solid #fecaca;">{{ $house->revision_notes }}</p>
                <p class="muted" style="font-size:12px; color:#be123c; margin:8px 0 0 0;">
                    Mohon periksa dan perbaiki isian data formulir atau foto sesuai poin catatan di atas, kemudian tekan <strong>"Simpan & Kirim Verifikasi"</strong> di langkah ke-3.
                </p>
            </div>
        </div>
    @endif

    {{-- 3-STEP WIZARD PROGRESS BAR --}}
    <div class="stepper-container" id="stepper-container">
        <div class="stepper-step active" id="step-tab-1" onclick="switchStep(1)">
            <div class="stepper-badge" id="step-badge-1">1</div>
            <div>
                <div class="stepper-title">Data CPBM</div>
                <div class="stepper-subtitle">Calon Penerima Bantuan</div>
            </div>
        </div>

        <span class="stepper-divider" style="font-size:18px; line-height:1; font-weight:700;">&rsaquo;</span>

        <div class="stepper-step" id="step-tab-2" onclick="switchStep(2)">
            <div class="stepper-badge" id="step-badge-2">2</div>
            <div>
                <div class="stepper-title">Data Rumah</div>
                <div class="stepper-subtitle">Fisik, Sanitasi & Lapangan</div>
            </div>
        </div>

        <span class="stepper-divider" style="font-size:18px; line-height:1; font-weight:700;">&rsaquo;</span>

        <div class="stepper-step" id="step-tab-3" onclick="switchStep(3)">
            <div class="stepper-badge" id="step-badge-3">3</div>
            <div>
                <div class="stepper-title">Anggaran & Usulan</div>
                <div class="stepper-subtitle">Sumber Dana & Proposal</div>
            </div>
        </div>
    </div>

    <form class="form" id="rtlh-form" enctype="multipart/form-data" method="POST" action="{{ $action }}">
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif

        {{-- ========================================================================= --}}
        {{-- STEP 1: DATA CPBM (CALON PENERIMA BANTUAN MANFAAT) --}}
        {{-- ========================================================================= --}}
        <div class="form-step-pane active" id="pane-step-1">
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-badge emerald" style="font-weight:800; font-size:13px;">
                        01
                    </div>
                    <div>
                        <h3 class="form-section-title">Langkah 1: Identitas Calon Penerima Bantuan Manfaat (CPBM)</h3>
                        <p class="form-section-desc">Identitas kependudukan kepala keluarga dan alamat domisili desa calon penerima manfaat.</p>
                    </div>
                </div>

                <div class="grid2">
                    <div class="form-field">
                        <label for="nik">Nomor Induk Kependudukan (NIK) <span class="req">*</span></label>
                        <input
                            type="text"
                            id="nik"
                            name="nik"
                            value="{{ old('nik', $house->nik) }}"
                            placeholder="Contoh: 320914XXXXXXXXXX (16 digit)"
                            maxlength="20"
                        >
                        <small class="muted">NIK kepala keluarga calon penerima manfaat.</small>
                    </div>

                    <div class="form-field">
                        <label for="head_of_household_name">Nama Kepala Keluarga (KK) <span class="req">*</span></label>
                        <input
                            type="text"
                            id="head_of_household_name"
                            name="head_of_household_name"
                            value="{{ old('head_of_household_name', $house->head_of_household_name) }}"
                            placeholder="Nama lengkap sesuai KTP / KK"
                        >
                        <small class="muted">Nama kepala keluarga yang berhak menerima bantuan.</small>
                    </div>
                </div>

                <div class="grid3">
                    <div class="form-field" style="grid-column: span 2;">
                        <label for="address">Alamat Jalan / Gang</label>
                        <input
                            type="text"
                            id="address"
                            name="address"
                            value="{{ old('address', $house->address) }}"
                            placeholder="Contoh: Jl. Pangeran Sutajaya No. 12"
                        >
                    </div>

                    <div class="form-field">
                        <label for="block">Dusun / Blok</label>
                        <input
                            type="text"
                            id="block"
                            name="block"
                            value="{{ old('block', $house->block) }}"
                            placeholder="Contoh: Blok Manis / Blok Wage"
                        >
                    </div>
                </div>

                <div class="grid3">
                    <div class="form-field">
                        <label for="rt">Nomor RT</label>
                        <input
                            type="text"
                            id="rt"
                            name="rt"
                            value="{{ old('rt', $house->rt) }}"
                            placeholder="Contoh: 001"
                            maxlength="10"
                        >
                    </div>

                    <div class="form-field">
                        <label for="rw">Nomor RW</label>
                        <input
                            type="text"
                            id="rw"
                            name="rw"
                            value="{{ old('rw', $house->rw) }}"
                            placeholder="Contoh: 002"
                            maxlength="10"
                        >
                    </div>

                    <div class="form-field">
                        <label for="region_id">Wilayah Administratif (Kelurahan) <span class="req">*</span></label>
                        <select id="region_id" name="region_id" required>
                            <option value="">-- Pilih Kelurahan --</option>
                            @foreach($regions as $r)
                                <option value="{{ $r->id }}" data-region-name="{{ $r->name }}" @selected(old('region_id', $house->region_id) == $r->id)>
                                    Kelurahan {{ $r->name }}
                                </option>
                            @endforeach
                        </select>
                        <small class="muted">Cakupan wilayah dibatasi khusus tingkat Kelurahan di Kota Cirebon.</small>
                    </div>
                </div>
            </div>

            {{-- STEP 1 FOOTER NAVIGATION --}}
            <div class="step-navigation-bar">
                <a href="{{ $panel === 'surveyor' ? route('surveyor.rtlh.index') : route('houses.index') }}" class="btn" style="background:#fff; border-color:var(--slate-300);">
                    Kembali ke Daftar
                </a>

                <div style="display:flex; gap:10px; align-items:center;">
                    <button type="button" class="btn" onclick="submitAsDraft()" style="background:#ffffff; color:var(--slate-700); border-color:var(--slate-300);">
                        Simpan Draft
                    </button>
                    <button type="button" class="btn primary" onclick="validateAndGoStep(2)">
                        Lanjut: Data Fisik Rumah (Step 2) &rarr;
                    </button>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- STEP 2: DATA FISIK RUMAH, STRUKTUR, SANITASI, GPS & FOTO --}}
        {{-- ========================================================================= --}}
        <div class="form-step-pane" id="pane-step-2">

            {{-- SUBSECTION 2A: KARAKTERISTIK UMUM RUMAH & KEPEMILIKAN --}}
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-badge blue" style="font-weight:800; font-size:13px;">
                        2A
                    </div>
                    <div>
                        <h3 class="form-section-title">Karakteristik & Status Kepemilikan Hunian</h3>
                        <p class="form-section-desc">Dimensi bangunan, penghuni, tahun survei, status penguasaan rumah dan tanah.</p>
                    </div>
                </div>

                <div class="grid4">
                    <div class="form-field">
                        <label for="house_code">Kode RTLH</label>
                        <input
                            type="text"
                            id="house_code"
                            name="house_code"
                            value="{{ old('house_code', $house->house_code) }}"
                            placeholder="Otomatis dari sistem"
                            readonly
                        >
                    </div>

                    <div class="form-field">
                        <label for="survey_year">Tahun Survei <span class="req">*</span></label>
                        <input
                            type="number"
                            id="survey_year"
                            name="survey_year"
                            value="{{ old('survey_year', $house->survey_year ?: date('Y')) }}"
                            min="2000"
                            max="2100"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label for="area_m2">Luas Rumah (m²)</label>
                        <input
                            type="number"
                            step="0.01"
                            id="area_m2"
                            name="area_m2"
                            value="{{ old('area_m2', $house->area_m2) }}"
                            placeholder="Contoh: 36.0"
                        >
                    </div>

                    <div class="form-field">
                        <label for="occupant_count">Jumlah Penghuni (Jiwa)</label>
                        <input
                            type="number"
                            min="0"
                            id="occupant_count"
                            name="occupant_count"
                            value="{{ old('occupant_count', $house->occupant_count) }}"
                            placeholder="Contoh: 4"
                        >
                    </div>
                </div>

                <div class="grid2">
                    <div class="form-field">
                        <label for="household_count">Jumlah KK dalam Rumah</label>
                        <input
                            type="number"
                            min="0"
                            id="household_count"
                            name="household_count"
                            value="{{ old('household_count', $house->household_count ?: 1) }}"
                            placeholder="1"
                        >
                    </div>

                    <div class="form-field">
                        <label for="settlement_condition_id">Kondisi Kawasan Permukiman</label>
                        <select id="settlement_condition_id" name="settlement_condition_id">
                            <option value="">-- Pilih Kondisi Kawasan --</option>
                            @foreach(($cats['SETTLEMENT_CONDITION']->values ?? []) as $v)
                                <option value="{{ $v->id }}" @selected(old('settlement_condition_id', $house->settlement_condition_id) == $v->id)>
                                    {{ $v->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid2">
                    {{-- STATUS PENGUASAAN RUMAH: HANYA MILIK SENDIRI --}}
                    <div class="form-field">
                        <label for="ownership_status_id">Status Penguasaan Rumah</label>
                        <select id="ownership_status_id" name="ownership_status_id">
                            @foreach(($cats['OWNERSHIP_STATUS']->values ?? []) as $v)
                                <option value="{{ $v->id }}" @selected(old('ownership_status_id', $house->ownership_status_id) == $v->id)>
                                    {{ $v->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- STATUS PENGUASAAN TANAH: ORANG TUA, SAUDARA, ANAK --}}
                    <div class="form-field">
                        <label for="land_status_id">Status Penguasaan Tanah</label>
                        <select id="land_status_id" name="land_status_id">
                            <option value="">-- Pilih Status Penguasaan Tanah --</option>
                            @foreach(($cats['LAND_STATUS']->values ?? []) as $v)
                                <option value="{{ $v->id }}" @selected(old('land_status_id', $house->land_status_id) == $v->id)>
                                    {{ $v->label }}
                                </option>
                            @endforeach
                        </select>
                        <small class="muted">Pilihan penguasaan tanah: Orang Tua, Saudara, atau Anak.</small>
                    </div>
                </div>
            </div>

            {{-- SUBSECTION 2B: KONDISI STRUKTUR UTAMA BANGUNAN --}}
            <div class="form-section">
                <div class="form-section-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <div style="display:flex; align-items:center; gap:14px;">
                        <div class="form-section-badge blue" style="font-weight:800; font-size:13px;">
                            2B
                        </div>
                        <div>
                            <h3 class="form-section-title">Kondisi Struktur Utama Bangunan</h3>
                            <p class="form-section-desc">Pondasi, sloof, kondisi tiang / kolom, balok, serta rangka atap.</p>
                        </div>
                    </div>

                    {{-- LIVE AUTO-SCORE BADGE --}}
                    <div style="background:var(--slate-50); border:1px solid var(--slate-200); padding:6px 14px; border-radius:var(--radius-lg); display:flex; align-items:center; gap:12px;">
                        <div style="font-size:11px; font-weight:700; color:var(--slate-600); text-transform:uppercase;">
                            Skor Survei:
                        </div>
                        <div style="font-size:18px; font-weight:900; color:var(--primary-700); font-family:monospace;" id="form-live-score">0</div>
                        <span id="form-live-priority" class="badge priority-low" style="font-size:11px;">Prioritas Rendah</span>
                    </div>
                </div>

                <div class="grid2">
                    <div class="form-field">
                        <label for="foundation">Jenis Pondasi</label>
                        @php
                            $currentFoundation = old('foundation', $house->structure?->foundation);
                            $defaultFoundations = [
                                'BATU KALI / BATU BELAH',
                                'BETON BERTULANG / TAPAK',
                                'BATA MERAH / ROLAG',
                                'KAYU / CERUCUK',
                                'BAMBU',
                                'TANPA PONDASI / MENUMPANG TANAH',
                                'LAINNYA',
                            ];
                            $foundationValues = isset($cats['FOUNDATION_TYPE']) && $cats['FOUNDATION_TYPE']->values->isNotEmpty()
                                ? $cats['FOUNDATION_TYPE']->values->pluck('label')->toArray()
                                : $defaultFoundations;
                        @endphp
                        <select id="foundation" name="foundation">
                            <option value="">-- Pilih Jenis Pondasi --</option>
                            @foreach($foundationValues as $opt)
                                <option value="{{ $opt }}" @selected(strtoupper(trim((string)$currentFoundation)) === strtoupper(trim($opt)))>
                                    {{ $opt }}
                                </option>
                            @endforeach
                            @if($currentFoundation && !in_array(strtoupper(trim($currentFoundation)), array_map('strtoupper', array_map('trim', $foundationValues))))
                                <option value="{{ $currentFoundation }}" selected>{{ $currentFoundation }}</option>
                            @endif
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="sloof_condition_id">Kondisi Sloof / Balok Dasar</label>
                        <select id="sloof_condition_id" name="sloof_condition_id" class="score-calc-select">
                            <option value="" data-score="0">-- Pilih Kondisi Sloof --</option>
                            @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                                @endphp
                                <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old('sloof_condition_id', $house->structure?->sloof_condition_id) == $v->id)>
                                    {{ $v->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid2">
                    {{-- KONDISI TIANG / KOLOM --}}
                    <div class="form-field">
                        <label for="column_condition_id">
                            Kondisi Tiang / Kolom Penopang
                        </label>
                        <select id="column_condition_id" name="column_condition_id" class="score-calc-select">
                            <option value="" data-score="0">-- Pilih Kondisi Tiang --</option>
                            @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                                @endphp
                                <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old('column_condition_id', $house->structure?->column_condition_id) == $v->id)>
                                    {{ $v->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                        <small class="muted">Kondisi fisik tiang penopang / kolom struktur utama.</small>
                    </div>

                    <div class="form-field">
                        <label for="beam_condition_id">Kondisi Balok Pengikat</label>
                        <select id="beam_condition_id" name="beam_condition_id" class="score-calc-select">
                            <option value="" data-score="0">-- Pilih Kondisi Balok --</option>
                            @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                                @endphp
                                <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old('beam_condition_id', $house->structure?->beam_condition_id) == $v->id)>
                                    {{ $v->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- RANGKA ATAP (KAYU / METAL & KONDISI) --}}
                <div class="grid2" style="background:var(--slate-50); border:1px solid var(--slate-200); border-radius:var(--radius-lg); padding:16px; margin-top:8px;">
                    <div class="form-field" style="margin-bottom:0;">
                        <label for="roof_frame_material_id">
                            Bahan Rangka Atap (Kayu / Metal)
                        </label>
                        <select id="roof_frame_material_id" name="roof_frame_material_id">
                            <option value="">-- Pilih Bahan Rangka Atap --</option>
                            @foreach(($cats['ROOF_FRAME_MATERIAL']->values ?? []) as $v)
                                <option value="{{ $v->id }}" @selected(old('roof_frame_material_id', $house->roof?->frame_material_id) == $v->id)>
                                    {{ $v->label }}
                                </option>
                            @endforeach
                        </select>
                        <small class="muted">Pilihan rangka atap: Kayu atau Metal / Baja Ringan.</small>
                    </div>

                    <div class="form-field" style="margin-bottom:0;">
                        <label for="roof_frame_condition_id">Kondisi Rangka Atap</label>
                        <select id="roof_frame_condition_id" name="roof_frame_condition_id" class="score-calc-select">
                            <option value="" data-score="0">-- Pilih Kondisi Rangka --</option>
                            @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                                @endphp
                                <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old('roof_frame_condition_id', $house->roof?->frame_condition_id) == $v->id)>
                                    {{ $v->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- SUBSECTION 2C: KOMPONEN ARSITEKTUR (LANTAI, DINDING, PLAFON, PENUTUP ATAP) --}}
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-badge purple" style="font-weight:800; font-size:13px;">
                        2C
                    </div>
                    <div>
                        <h3 class="form-section-title">Komponen Arsitektur Hunian</h3>
                        <p class="form-section-desc">Material dan kondisi fisik penutup lantai, dinding, penutup atap, dan plafon.</p>
                    </div>
                </div>

                <div class="grid2">
                    <div class="form-field">
                        <label for="floor_material_id">Bahan Material Lantai</label>
                        <select id="floor_material_id" name="floor_material_id">
                            <option value="">-- Pilih Material Lantai --</option>
                            @foreach(($cats['FLOOR_MATERIAL']->values ?? []) as $v)
                                <option value="{{ $v->id }}" @selected(old('floor_material_id', $house->floor?->material_id) == $v->id)>
                                    {{ $v->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="floor_condition_id">Kondisi Lantai</label>
                        <select id="floor_condition_id" name="floor_condition_id" class="score-calc-select">
                            <option value="" data-score="0">-- Pilih Kondisi Lantai --</option>
                            @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                                @endphp
                                <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old('floor_condition_id', $house->floor?->condition_id) == $v->id)>
                                    {{ $v->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="wall_material_id">Bahan Material Dinding</label>
                        <select id="wall_material_id" name="wall_material_id">
                            <option value="">-- Pilih Material Dinding --</option>
                            @foreach(($cats['WALL_MATERIAL']->values ?? []) as $v)
                                <option value="{{ $v->id }}" @selected(old('wall_material_id', $house->wall?->material_id) == $v->id)>
                                    {{ $v->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="wall_condition_id">Kondisi Dinding</label>
                        <select id="wall_condition_id" name="wall_condition_id" class="score-calc-select">
                            <option value="" data-score="0">-- Pilih Kondisi Dinding --</option>
                            @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                                @endphp
                                <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old('wall_condition_id', $house->wall?->condition_id) == $v->id)>
                                    {{ $v->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="roof_material_id">Bahan Penutup Atap</label>
                        <select id="roof_material_id" name="roof_material_id">
                            <option value="">-- Pilih Penutup Atap --</option>
                            @foreach(($cats['ROOF_MATERIAL']->values ?? []) as $v)
                                <option value="{{ $v->id }}" @selected(old('roof_material_id', $house->roof?->material_id) == $v->id)>
                                    {{ $v->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="roof_condition_id">Kondisi Penutup Atap</label>
                        <select id="roof_condition_id" name="roof_condition_id" class="score-calc-select">
                            <option value="" data-score="0">-- Pilih Kondisi Penutup Atap --</option>
                            @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                                @endphp
                                <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old('roof_condition_id', $house->roof?->condition_id) == $v->id)>
                                    {{ $v->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field" style="grid-column: span 2;">
                        <label for="ceiling_condition_id">Kondisi Plafon / Langit-langit</label>
                        <select id="ceiling_condition_id" name="ceiling_condition_id" class="score-calc-select">
                            <option value="" data-score="0">-- Pilih Kondisi Plafon --</option>
                            @foreach(($cats['HOUSE_CONDITION']->values ?? []) as $v)
                                @php
                                    $sc = \App\Services\HouseAssessmentService::getScoreForCodeOrLabel($v->code, $v->label, $v->sort_order);
                                @endphp
                                <option value="{{ $v->id }}" data-score="{{ $sc }}" @selected(old('ceiling_condition_id', $house->ceiling?->condition_id) == $v->id)>
                                    {{ $v->label }} (Skor: {{ $sc }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- SUBSECTION 2D: SANITASI & UTILITAS --}}
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-badge amber" style="font-weight:800; font-size:13px;">
                        2D
                    </div>
                    <div>
                        <h3 class="form-section-title">Pencahayaan, Air & Sanitasi Lingkungan</h3>
                        <p class="form-section-desc">Ketersediaan air minum bersih, sanitasi air kotor, jamban, ventilasi dan penerangan.</p>
                    </div>
                </div>

                <div class="grid2">
                    {{-- AIR MINUM (TERMASUK 'BELUM ADA') --}}
                    <div class="form-field">
                        <label for="water_source_id">
                            Sumber Air Minum
                        </label>
                        <select id="water_source_id" name="water_source_id">
                            <option value="">-- Pilih Sumber Air Minum --</option>
                            @foreach(($cats['WATER_SOURCE']->values ?? []) as $v)
                                <option value="{{ $v->id }}" @selected(old('water_source_id', $house->sanitation?->water_source_id) == $v->id)>
                                    {{ $v->label }}
                                </option>
                            @endforeach
                        </select>
                        <small class="muted">Tersedia pilihan sumber air PDAM, Sumur, Lainnya, maupun <strong>Belum Ada</strong>.</small>
                    </div>

                    {{-- SANITASI AIR KOTOR (AIR KALI, SUNGAI) --}}
                    <div class="form-field">
                        <label for="wastewater_disposal_id">
                            Sanitasi Air Kotor
                        </label>
                        <select id="wastewater_disposal_id" name="wastewater_disposal_id">
                            <option value="">-- Pilih Sanitasi Air Kotor --</option>
                            @foreach(($cats['WASTEWATER_DISPOSAL']->values ?? []) as $v)
                                <option value="{{ $v->id }}" @selected(old('wastewater_disposal_id', $house->sanitation?->wastewater_disposal_id) == $v->id)>
                                    {{ $v->label }}
                                </option>
                            @endforeach
                        </select>
                        <small class="muted">Pembuangan sanitasi air kotor rumah tangga: Air Kali atau Sungai.</small>
                    </div>
                </div>

                <div class="grid3">
                    <div class="form-field">
                        <label for="toilet_available">Ketersediaan Jamban / KM</label>
                        <select id="toilet_available" name="toilet_available">
                            <option value="">-- Pilih Ketersediaan --</option>
                            <option value="1" @selected(old('toilet_available', is_null($house->sanitation?->toilet_available) ? '' : (int)$house->sanitation?->toilet_available) === 1)>ADA / MILIK SENDIRI</option>
                            <option value="0" @selected(old('toilet_available', is_null($house->sanitation?->toilet_available) ? '' : (int)$house->sanitation?->toilet_available) === 0)>TIDAK PUNYA / MENUMPANG</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="toilet_type_id">Jenis Jamban</label>
                        <select id="toilet_type_id" name="toilet_type_id">
                            <option value="">-- Pilih Jenis --</option>
                            @foreach(($cats['TOILET_TYPE']->values ?? []) as $v)
                                <option value="{{ $v->id }}" @selected(old('toilet_type_id', $house->sanitation?->toilet_type_id) == $v->id)>
                                    {{ $v->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-field">
                        <label for="fecal_disposal_type_id">TPA Tinja (Tempat Pembuangan Akhir)</label>
                        <select id="fecal_disposal_type_id" name="fecal_disposal_type_id">
                            <option value="">-- Pilih TPA Tinja --</option>
                            @foreach(($cats['FECAL_DISPOSAL']->values ?? []) as $v)
                                <option value="{{ $v->id }}" @selected(old('fecal_disposal_type_id', $house->sanitation?->fecal_disposal_type_id) == $v->id)>
                                    {{ $v->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid3">
                    <div class="form-field">
                        <label for="water_fecal_distance">Jarak Air ke Pembuangan</label>
                        <input
                            type="text"
                            id="water_fecal_distance"
                            name="water_fecal_distance"
                            value="{{ old('water_fecal_distance', $house->sanitation?->water_fecal_distance) }}"
                            placeholder="Contoh: < 10 meter / > 10 meter"
                        >
                    </div>

                    <div class="form-field">
                        <label for="light_opening">Bukaan Cahaya / Jendela</label>
                        <input
                            type="text"
                            id="light_opening"
                            name="light_opening"
                            value="{{ old('light_opening', $house->utility?->light_opening) }}"
                            placeholder="Contoh: Cukup / Kurang"
                        >
                    </div>

                    <div class="form-field">
                        <label for="lighting_source_id">Penerangan / Listrik</label>
                        <select id="lighting_source_id" name="lighting_source_id">
                            <option value="">-- Pilih Sumber Listrik --</option>
                            @foreach(($cats['LIGHTING_SOURCE']->values ?? []) as $v)
                                <option value="{{ $v->id }}" @selected(old('lighting_source_id', $house->utility?->lighting_source_id) == $v->id)>
                                    {{ $v->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- SUBSECTION 2E: TITIK KOORDINAT GPS LAPANGAN --}}
            <div class="form-section">
                <div class="form-section-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                    <div style="display:flex; align-items:center; gap:14px;">
                        <div class="form-section-badge emerald" style="font-weight:800; font-size:13px;">
                            2E
                        </div>
                        <div>
                            <h3 class="form-section-title">Koordinat Geospasial Lapangan (WGS84)</h3>
                            <p class="form-section-desc" id="gps-status">Ambil titik GPS otomatis menggunakan perangkat Anda atau sesuaikan pin marker.</p>
                        </div>
                    </div>
                    <button type="button" class="btn small" onclick="getCurrentLocation()" style="background:#fff; border-color:var(--slate-300);">
                        Ambil Titik GPS Saya
                    </button>
                </div>

                <div class="grid2">
                    <div class="form-field">
                        <label for="latitude">Latitude</label>
                        <input
                            type="number"
                            step="any"
                            id="latitude"
                            name="latitude"
                            value="{{ old('latitude', $house->latitude) }}"
                            placeholder="-6.732..."
                            onchange="updateMapFromInputs()"
                        >
                    </div>

                    <div class="form-field">
                        <label for="longitude">Longitude</label>
                        <input
                            type="number"
                            step="any"
                            id="longitude"
                            name="longitude"
                            value="{{ old('longitude', $house->longitude) }}"
                            placeholder="108.552..."
                            onchange="updateMapFromInputs()"
                        >
                    </div>
                </div>

                <div style="margin-top:10px;">
                    <div id="form-map" style="height:240px; width:100%; border-radius:8px; border:1px solid var(--slate-300); z-index:1;"></div>
                </div>
            </div>

            {{-- SUBSECTION 2F: DOKUMENTASI FOTO FISIK --}}
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-badge rose" style="font-weight:800; font-size:13px;">
                        2F
                    </div>
                    <div>
                        <h3 class="form-section-title">Dokumentasi Foto Fisik Lapangan</h3>
                        <p class="form-section-desc">Unggah foto dokumentasi kondisi fisik rumah dari berbagai sudut.</p>
                    </div>
                </div>

                @php
                    $typeMap = [
                        'photo_front' => 'front',
                        'photo_angle' => 'angle_45',
                        'photo_side' => 'side',
                        'photo_back' => 'back',
                        'photo_family_room' => 'family_room',
                        'photo_bathroom' => 'bathroom',
                    ];
                @endphp

                <div class="photo-upload-grid">
                    @foreach([
                        ['photo_front', 'Tampak Depan'],
                        ['photo_angle', 'Tampak Sudut 45°'],
                        ['photo_side', 'Tampak Samping'],
                        ['photo_back', 'Tampak Belakang'],
                        ['photo_family_room', 'Ruang Dalam / Keluarga'],
                        ['photo_bathroom', 'Kamar Mandi / Sanitasi']
                    ] as [$field, $label])
                        @php
                            $photoType = $typeMap[$field] ?? '';
                            $existingPhoto = $house->photos ? $house->photos->where('type', $photoType)->first() : null;
                            $existingUrl = $existingPhoto ? asset('storage/' . $existingPhoto->path) : null;
                        @endphp

                        <div class="photo-dropzone" onclick="document.getElementById('{{ $field }}').click()">
                            <strong style="font-size:13px; color:var(--slate-800); display:block; margin-bottom:4px;">{{ $label }}</strong>
                            <span class="muted" style="font-size:11px;">
                                {{ $existingUrl ? 'Klik ganti foto' : 'Klik unggah foto' }}
                            </span>
                            <input
                                type="file"
                                id="{{ $field }}"
                                name="{{ $field }}"
                                accept="image/*"
                                onchange="previewImage(this, 'preview-{{ $field }}')"
                            >
                            <img
                                id="preview-{{ $field }}"
                                class="photo-preview"
                                src="{{ $existingUrl ?? '' }}"
                                alt="Preview {{ $label }}"
                                style="{{ $existingUrl ? 'display:block;' : 'display:none;' }}"
                            >
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- SUBSECTION 2G: CATATAN LAPANGAN --}}
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-badge" style="background:#f1f5f9; color:var(--slate-700); font-weight:800; font-size:13px;">
                        2G
                    </div>
                    <div>
                        <h3 class="form-section-title">Catatan Tambahan Lapangan</h3>
                        <p class="form-section-desc">Catatan khusus mengenai kondisi lansia/balita, kendala medan, atau urgensi fisik.</p>
                    </div>
                </div>

                <div class="form-field" style="margin-bottom:0;">
                    <textarea
                        id="assessment_notes"
                        name="assessment_notes"
                        rows="3"
                        placeholder="Tuliskan catatan kondisi khusus hunian bila ada..."
                    >{{ old('assessment_notes') }}</textarea>
                </div>
            </div>

            {{-- STEP 2 FOOTER NAVIGATION --}}
            <div class="step-navigation-bar">
                <button type="button" class="btn" onclick="switchStep(1)" style="background:#fff; border-color:var(--slate-300);">
                    &larr; Kembali ke Data CPBM (Step 1)
                </button>

                <div style="display:flex; gap:10px; align-items:center;">
                    <button type="button" class="btn" onclick="submitAsDraft()" style="background:#ffffff; color:var(--slate-700); border-color:var(--slate-300);">
                        Simpan Draft
                    </button>
                    <button type="button" class="btn primary" onclick="switchStep(3)">
                        Lanjut: Anggaran & Proposal (Step 3) &rarr;
                    </button>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- STEP 3: SUMBER ANGGARAN & PROPOSAL USULAN --}}
        {{-- ========================================================================= --}}
        <div class="form-step-pane" id="pane-step-3">
            <div class="form-section">
                <div class="form-section-header">
                    <div class="form-section-badge" style="background:#fef3c7; color:#b45309; font-weight:800; font-size:13px;">
                        03
                    </div>
                    <div>
                        <h3 class="form-section-title">Langkah 3: Sumber Anggaran & Berkas Usulan Proposal</h3>
                        <p class="form-section-desc">Pendataan sumber pembiayaan, nilai alokasi anggaran, status realisasi, dan upload berkas proposal.</p>
                    </div>
                </div>

                <div class="grid2">
                    {{-- SUMBER ANGGARAN --}}
                    <div class="form-field">
                        <label for="funding_source">
                            Sumber Anggaran
                        </label>
                        @php
                            $currentFunding = old('funding_source', $house->funding_source);
                            $fundingOptions = [
                                'APBD Kabupaten Cirebon',
                                'APBD Provinsi Jawa Barat',
                                'BSPS (Kementerian PKP / PUPR)',
                                'BAZNAS Kabupaten Cirebon',
                                'DAK Fisik Perumahan',
                                'CSR / Swadaya Masyarakat',
                                'Lainnya',
                            ];
                        @endphp
                        <select id="funding_source" name="funding_source">
                            <option value="">-- Pilih Sumber Anggaran --</option>
                            @foreach($fundingOptions as $fOpt)
                                <option value="{{ $fOpt }}" @selected(strtoupper(trim((string)$currentFunding)) === strtoupper(trim($fOpt)))>
                                    {{ $fOpt }}
                                </option>
                            @endforeach
                            @if($currentFunding && !in_array(strtoupper(trim($currentFunding)), array_map('strtoupper', array_map('trim', $fundingOptions))))
                                <option value="{{ $currentFunding }}" selected>{{ $currentFunding }}</option>
                            @endif
                        </select>
                        <small class="muted">Pilih alokasi pembiayaan atau sumber bantuan penanganan RTLH.</small>
                    </div>

                    {{-- NILAI KEGIATAN / ANGGARAN --}}
                    <div class="form-field">
                        <label for="budget_amount">
                            Nilai Anggaran / Kegiatan (Rp)
                        </label>
                        <input
                            type="number"
                            id="budget_amount"
                            name="budget_amount"
                            value="{{ old('budget_amount', $house->budget_amount ? (int)$house->budget_amount : '') }}"
                            placeholder="Contoh: 20000000"
                            min="0"
                            step="100000"
                        >
                        <small class="muted">Nominal usulan biaya bantuan perbaikan (misal: 20.000.000).</small>
                    </div>
                </div>

                {{-- STATUS REALISASI DENGAN INDIKATOR WARNA KUNING & HIJAU --}}
                <div style="margin-top:10px; margin-bottom:20px; background:var(--slate-50); border:1px solid var(--slate-200); border-radius:var(--radius-lg); padding:20px;">
                    <label style="font-size:14px; font-weight:800; color:var(--slate-800); display:block; margin-bottom:12px;">
                        Status Realisasi RTLH
                    </label>

                    @php
                        $realStatus = old('realization_status', $house->realization_status ?: 'not_realized');
                    @endphp

                    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px;">
                        {{-- KUNING: BELUM TEREALISASI --}}
                        <label class="realization-radio-card {{ $realStatus !== 'realized' ? 'active-kuning' : '' }}" id="card-not-realized" onclick="selectRealizationCard('not_realized')">
                            <div style="display:flex; align-items:flex-start; gap:12px;">
                                <input
                                    type="radio"
                                    name="realization_status"
                                    id="status_not_realized"
                                    value="not_realized"
                                    @checked($realStatus !== 'realized')
                                    style="margin-top:4px;"
                                >
                                <div>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde047; font-weight:800; font-size:12px;">
                                            Belum Terealisasi
                                        </span>
                                    </div>
                                    <p class="muted" style="margin:6px 0 0 0; font-size:12px; line-height:1.4;">
                                        Data rumah RTLH tercatat dalam basis data / proposal usulan, namun belum dilaksanakan pekerjaan fisik atau pencairan dana.
                                    </p>
                                </div>
                            </div>
                        </label>

                        {{-- HIJAU: SUDAH TEREALISASI --}}
                        <label class="realization-radio-card {{ $realStatus === 'realized' ? 'active-hijau' : '' }}" id="card-realized" onclick="selectRealizationCard('realized')">
                            <div style="display:flex; align-items:flex-start; gap:12px;">
                                <input
                                    type="radio"
                                    name="realization_status"
                                    id="status_realized"
                                    value="realized"
                                    @checked($realStatus === 'realized')
                                    style="margin-top:4px;"
                                >
                                <div>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #86efac; font-weight:800; font-size:12px;">
                                            Sudah Terealisasi 
                                        </span>
                                    </div>
                                    <p class="muted" style="margin:6px 0 0 0; font-size:12px; line-height:1.4;">
                                        Bantuan perbaikan rumah telah selesai direalisasikan dan diverifikasi di lapangan.
                                    </p>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- DOKUMEN BERKAS ADMINISTRASI TERPISAH (KK, AKTE, SURAT TANAH, PROPOSAL) --}}
                <div style="margin-top: 10px; margin-bottom: 24px;">
                    <label style="font-size:14px; font-weight:800; color:var(--slate-800); display:block; margin-bottom:4px;">
                        Berkas Dokumen Administrasi & Kepemilikan (Terpisah)
                    </label>
                    <p class="muted" style="font-size:12px; margin-bottom:16px;">
                        Unggah masing-masing dokumen secara terpisah dalam format PDF, DOCX, ZIP, atau Gambar scan (Maks. 10 MB per berkas).
                    </p>

                    <div class="grid2" style="gap:20px;">
                        {{-- 1. BERKAS KARTU KELUARGA (KK) --}}
                        <div class="form-field" style="margin-bottom:0;">
                            <label for="kk_document" style="font-weight:700;">
                                Berkas Kartu Keluarga (KK)
                            </label>

                            <div style="border:2px dashed var(--slate-300); border-radius:var(--radius-lg); padding:20px; text-align:center; background:var(--slate-50); position:relative;">
                                <input
                                    type="file"
                                    id="kk_document"
                                    name="kk_document"
                                    accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png"
                                    style="margin:0 auto; width:100%; max-width:280px;"
                                >
                                <div class="muted" style="font-size:11.5px; margin-top:8px;">
                                    Scan / Foto Kartu Keluarga Kepala Keluarga.
                                </div>

                                @if($house->kk_document_path)
                                    <div style="margin-top:12px; padding:8px 12px; background:#eff6ff; border-radius:8px; border:1px solid #bfdbfe; display:inline-flex; align-items:center; gap:8px; font-size:11.5px;">
                                        <span style="font-weight:700; color:#1e40af;">File KK Tersimpan:</span>
                                        <a href="{{ asset('storage/' . $house->kk_document_path) }}" target="_blank" style="color:#2563eb; text-decoration:underline; font-weight:700;">
                                            Lihat Dokumen
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- 2. BERKAS AKTE KELAHIRAN / AKTA --}}
                        <div class="form-field" style="margin-bottom:0;">
                            <label for="akte_document" style="font-weight:700;">
                                Berkas Akte / Akta Kependudukan
                            </label>

                            <div style="border:2px dashed var(--slate-300); border-radius:var(--radius-lg); padding:20px; text-align:center; background:var(--slate-50); position:relative;">
                                <input
                                    type="file"
                                    id="akte_document"
                                    name="akte_document"
                                    accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png"
                                    style="margin:0 auto; width:100%; max-width:280px;"
                                >
                                <div class="muted" style="font-size:11.5px; margin-top:8px;">
                                    Scan / Foto Akte Kelahiran / Akta Kependudukan.
                                </div>

                                @if($house->akte_document_path)
                                    <div style="margin-top:12px; padding:8px 12px; background:#eff6ff; border-radius:8px; border:1px solid #bfdbfe; display:inline-flex; align-items:center; gap:8px; font-size:11.5px;">
                                        <span style="font-weight:700; color:#1e40af;">File Akte Tersimpan:</span>
                                        <a href="{{ asset('storage/' . $house->akte_document_path) }}" target="_blank" style="color:#2563eb; text-decoration:underline; font-weight:700;">
                                            Lihat Dokumen
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- 3. BERKAS SURAT TANAH / SERTIFIKAT --}}
                        <div class="form-field" style="margin-bottom:0;">
                            <label for="land_document" style="font-weight:700;">
                                Berkas Surat Tanah / Sertifikat Kepemilikan Lahan
                            </label>

                            <div style="border:2px dashed var(--slate-300); border-radius:var(--radius-lg); padding:20px; text-align:center; background:var(--slate-50); position:relative;">
                                <input
                                    type="file"
                                    id="land_document"
                                    name="land_document"
                                    accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png"
                                    style="margin:0 auto; width:100%; max-width:280px;"
                                >
                                <div class="muted" style="font-size:11.5px; margin-top:8px;">
                                    Sertifikat Hak Milik, Letter C, AJB, atau Surat Keterangan Desa.
                                </div>

                                @if($house->land_document_path)
                                    <div style="margin-top:12px; padding:8px 12px; background:#eff6ff; border-radius:8px; border:1px solid #bfdbfe; display:inline-flex; align-items:center; gap:8px; font-size:11.5px;">
                                        <span style="font-weight:700; color:#1e40af;">File Surat Tanah:</span>
                                        <a href="{{ asset('storage/' . $house->land_document_path) }}" target="_blank" style="color:#2563eb; text-decoration:underline; font-weight:700;">
                                            Lihat Dokumen
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- 4. BERKAS PROPOSAL USULAN / SURAT PENGANTAR --}}
                        <div class="form-field" style="margin-bottom:0;">
                            <label for="proposal_document" style="font-weight:700;">
                                Berkas Proposal Usulan / Surat Pengantar Desa
                            </label>

                            <div style="border:2px dashed var(--slate-300); border-radius:var(--radius-lg); padding:20px; text-align:center; background:var(--slate-50); position:relative;">
                                <input
                                    type="file"
                                    id="proposal_document"
                                    name="proposal_document"
                                    accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png"
                                    style="margin:0 auto; width:100%; max-width:280px;"
                                >
                                <div class="muted" style="font-size:11.5px; margin-top:8px;">
                                    Berkas Proposal Usulan, RAB, atau Berkas Pendukung Kegiatan.
                                </div>

                                @if($house->proposal_document_path)
                                    <div style="margin-top:12px; padding:8px 12px; background:#eff6ff; border-radius:8px; border:1px solid #bfdbfe; display:inline-flex; align-items:center; gap:8px; font-size:11.5px;">
                                        <span style="font-weight:700; color:#1e40af;">File Proposal:</span>
                                        <a href="{{ asset('storage/' . $house->proposal_document_path) }}" target="_blank" style="color:#2563eb; text-decoration:underline; font-weight:700;">
                                            Lihat Dokumen
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- CATATAN USULAN --}}
                <div class="form-field" style="margin-bottom:0;">
                    <label for="proposal_notes">Catatan Usulan / Administrasi Proposal</label>
                    <textarea
                        id="proposal_notes"
                        name="proposal_notes"
                        rows="3"
                        placeholder="Tuliskan keterangan mengenai nomor surat usulan desa, SK verifikasi, atau sumber usulan kegiatan..."
                    >{{ old('proposal_notes', $house->proposal_notes) }}</textarea>
                </div>
            </div>

            {{-- STEP 3 FOOTER & FINAL SUBMIT BAR --}}
            <div class="step-navigation-bar" style="background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-radius: var(--radius-xl); box-shadow: var(--shadow-xl);">
                <button type="button" class="btn" onclick="switchStep(2)" style="background:transparent; color:#cbd5e1; border-color:rgba(255,255,255,0.25);">
                    &larr; Kembali ke Data Fisik (Step 2)
                </button>

                <div class="actions" style="margin:0; display:flex; gap:10px; align-items:center;">
                    <button type="button" class="btn" onclick="submitAsDraft()" style="background:#334155; color:#ffffff; border-color:#475569;">
                        Simpan Draft
                    </button>

                    @if($panel === 'surveyor')
                        <button type="submit" class="btn primary" name="submit" value="1">
                            Simpan & Kirim Review
                        </button>
                    @else
                        <button type="submit" class="btn primary" name="submit" value="1">
                            Simpan Data RTLH
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <input type="hidden" name="submit" id="submit-flag" value="1">
    </form>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            let currentStep = 1;
            let formMap, formMarker;

            function switchStep(step) {
                if (step < 1 || step > 3) return;

                // Update active tab buttons
                for (let i = 1; i <= 3; i++) {
                    const tab = document.getElementById(`step-tab-${i}`);
                    const pane = document.getElementById(`pane-step-${i}`);

                    if (tab) {
                        tab.classList.remove('active');
                        if (i < step) {
                            tab.classList.add('completed');
                        } else {
                            tab.classList.remove('completed');
                        }
                    }

                    if (pane) {
                        pane.classList.remove('active');
                    }
                }

                const currentTab = document.getElementById(`step-tab-${step}`);
                const currentPane = document.getElementById(`pane-step-${step}`);
                if (currentTab) currentTab.classList.add('active');
                if (currentPane) currentPane.classList.add('active');

                currentStep = step;

                // When entering step 2, initialize Leaflet map if not yet done
                if (step === 2 && formMap) {
                    setTimeout(() => {
                        formMap.invalidateSize();
                    }, 200);
                }

                window.scrollTo({ top: 0, behavior: 'smooth' });
            }

            function submitAsDraft() {
                const form = document.getElementById('rtlh-form');
                if (!form) return;

                const region = document.getElementById('region_id');
                if (region && !region.value) {
                    alert('Untuk menyimpan draft, silakan pilih Wilayah Administratif (Kelurahan) pada Step 1 terlebih dahulu.');
                    switchStep(1);
                    region.focus();
                    return;
                }

                let submitFlag = document.getElementById('submit-flag');
                if (!submitFlag) {
                    submitFlag = document.createElement('input');
                    submitFlag.type = 'hidden';
                    submitFlag.id = 'submit-flag';
                    submitFlag.name = 'submit';
                    form.appendChild(submitFlag);
                }
                submitFlag.value = '0';

                // Bypass client-side HTML5 validation so incomplete draft fields don't block saving
                form.noValidate = true;
                form.submit();
            }

            function validateAndGoStep(targetStep) {
                if (targetStep === 2) {
                    const region = document.getElementById('region_id');

                    if (!region.value) {
                        alert('Silakan pilih Wilayah Administratif (Kelurahan) terlebih dahulu.');
                        region.focus();
                        return;
                    }
                }

                switchStep(targetStep);
            }

            function selectRealizationCard(val) {
                const radioNot = document.getElementById('status_not_realized');
                const radioYes = document.getElementById('status_realized');
                const cardNot = document.getElementById('card-not-realized');
                const cardYes = document.getElementById('card-realized');

                if (val === 'realized') {
                    if (radioYes) radioYes.checked = true;
                    if (cardYes) cardYes.classList.add('active-hijau');
                    if (cardNot) cardNot.classList.remove('active-kuning');
                } else {
                    if (radioNot) radioNot.checked = true;
                    if (cardNot) cardNot.classList.add('active-kuning');
                    if (cardYes) cardYes.classList.remove('active-hijau');
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                const regionInput = document.getElementById('region_id');
                const houseCodeInput = document.getElementById('house_code');

                // Batas wilayah Kota Cirebon (bukan Kabupaten)
                const KOTA_CIREBON_BOUNDS = L.latLngBounds(
                    L.latLng(-6.7500, 108.5350),  // Southwest corner
                    L.latLng(-6.6900, 108.5800)   // Northeast corner
                );
                const KOTA_CIREBON_CENTER = [-6.7060, 108.5565];

                let initLat = parseFloat(latInput?.value) || KOTA_CIREBON_CENTER[0];
                let initLng = parseFloat(lngInput?.value) || KOTA_CIREBON_CENTER[1];
                let zoomLevel = (latInput?.value && lngInput?.value) ? 16 : 13;

                const mapEl = document.getElementById('form-map');
                if (mapEl) {
                    formMap = L.map('form-map', {
                        maxBounds: KOTA_CIREBON_BOUNDS.pad(0.05), // sedikit padding agar tidak terlalu ketat
                        maxBoundsViscosity: 1.0,                  // tidak bisa geser keluar batas
                        minZoom: 12
                    }).setView([initLat, initLng], zoomLevel);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap | Kota Cirebon'
                    }).addTo(formMap);

                    formMarker = L.marker([initLat, initLng], { draggable: true }).addTo(formMap);

                    // Update inputs when marker is dragged
                    formMarker.on('dragend', function (e) {
                        const coord = e.target.getLatLng();
                        if (!KOTA_CIREBON_BOUNDS.contains(coord)) {
                            alert('Titik berada di luar cakupan wilayah Kota Cirebon.');
                            formMarker.setLatLng([initLat, initLng]);
                            return;
                        }
                        latInput.value = coord.lat.toFixed(7);
                        lngInput.value = coord.lng.toFixed(7);
                    });

                    // Update marker when map is clicked
                    formMap.on('click', function (e) {
                        const coord = e.latlng;
                        if (!KOTA_CIREBON_BOUNDS.contains(coord)) {
                            alert('Titik berada di luar wilayah Kota Cirebon. Silakan pilih lokasi dalam batas kota.');
                            return;
                        }
                        formMarker.setLatLng(coord);
                        latInput.value = coord.lat.toFixed(7);
                        lngInput.value = coord.lng.toFixed(7);
                    });
                }

                if (regionInput) {
                    regionInput.addEventListener('change', updateCodePreview);
                    updateCodePreview();
                }

                if (!latInput?.value || !lngInput?.value) {
                    getCurrentLocation(true);
                }

                // Kalkulasi Skor Otomatis Form Survey
                const scoreSelects = document.querySelectorAll('.score-calc-select');
                const scoreDisplay = document.getElementById('form-live-score');
                const priorityDisplay = document.getElementById('form-live-priority');

                function updateFormScore() {
                    let total = 0;
                    let hasStructuralSevere = false;

                    scoreSelects.forEach(select => {
                        const opt = select.options[select.selectedIndex];
                        const score = parseInt(opt?.dataset?.score || 0, 10);
                        total += score;

                        if (['sloof_condition_id', 'column_condition_id', 'beam_condition_id', 'roof_frame_condition_id'].includes(select.id)) {
                            if (score >= 3) {
                                hasStructuralSevere = true;
                            }
                        }
                    });

                    if (scoreDisplay) scoreDisplay.textContent = total;

                    if (priorityDisplay) {
                        if (total >= 12 || hasStructuralSevere) {
                            priorityDisplay.className = 'badge priority-high';
                            priorityDisplay.textContent = 'Prioritas Tinggi (Rusak Berat)';
                        } else if (total >= 6) {
                            priorityDisplay.className = 'badge priority-medium';
                            priorityDisplay.textContent = 'Prioritas Sedang (Rusak Sedang)';
                        } else {
                            priorityDisplay.className = 'badge priority-low';
                            priorityDisplay.textContent = 'Prioritas Rendah (Rusak Ringan)';
                        }
                    }
                }

                scoreSelects.forEach(select => {
                    select.addEventListener('change', updateFormScore);
                });

                updateFormScore();
            });

            function updateCodePreview() {
                const regionInput = document.getElementById('region_id');
                const houseCodeInput = document.getElementById('house_code');
                if (!houseCodeInput || !regionInput) return;
                if ((!houseCodeInput.value && !regionInput.value) || (houseCodeInput.value && !houseCodeInput.value.endsWith('-XXXX'))) return;

                const regionName = regionInput.options[regionInput.selectedIndex]?.dataset.regionName || '';
                const prefix = regionName
                    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                    .toUpperCase().replace(/[^A-Z0-9]+/g, '-').replace(/^-|-$/g, '');
                houseCodeInput.value = prefix ? `RTLH-${prefix}-XXXX` : '';
            }

            function updateMapFromInputs() {
                const lat = parseFloat(document.getElementById('latitude')?.value);
                const lng = parseFloat(document.getElementById('longitude')?.value);

                if (!isNaN(lat) && !isNaN(lng) && formMap && formMarker) {
                    const newLatLng = L.latLng(lat, lng);
                    if (typeof KOTA_CIREBON_BOUNDS !== 'undefined' && !KOTA_CIREBON_BOUNDS.contains(newLatLng)) {
                        alert('Koordinat yang dimasukkan berada di luar wilayah Kota Cirebon. Silakan perbaiki.');
                        return;
                    }
                    formMarker.setLatLng(newLatLng);
                    formMap.panTo(newLatLng);
                }
            }

            function getCurrentLocation(isAutomatic = false) {
                if (!navigator.geolocation) return;

                const latInput = document.getElementById('latitude');
                const lngInput = document.getElementById('longitude');
                const gpsStatus = document.getElementById('gps-status');

                if (latInput && !latInput.value) latInput.placeholder = 'Mengambil GPS...';
                if (lngInput && !lngInput.value) lngInput.placeholder = 'Mengambil GPS...';

                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude.toFixed(7);
                        const lng = position.coords.longitude.toFixed(7);
                        const detectedLatLng = L.latLng(parseFloat(lat), parseFloat(lng));

                        // Validasi: lokasi GPS harus dalam wilayah Kota Cirebon
                        if (typeof KOTA_CIREBON_BOUNDS !== 'undefined' && !KOTA_CIREBON_BOUNDS.contains(detectedLatLng)) {
                            if (gpsStatus) gpsStatus.textContent = 'Lokasi GPS Anda terdeteksi di luar wilayah Kota Cirebon. Silakan pilih titik secara manual pada peta.';
                            if (!isAutomatic) alert('Lokasi GPS Anda berada di luar wilayah Kota Cirebon. Silakan tentukan titik koordinat secara manual pada peta.');
                            return;
                        }

                        if (latInput) latInput.value = lat;
                        if (lngInput) lngInput.value = lng;

                        updateMapFromInputs();
                        if (gpsStatus) {
                            const accuracy = Math.round(position.coords.accuracy);
                            gpsStatus.textContent = `Lokasi GPS terkini aktif (akurasi ~${accuracy}m). Pin peta dapat digeser.`;
                        }
                    },
                    function(error) {
                        if (gpsStatus) gpsStatus.textContent = 'GPS otomatis tidak aktif. Silakan pilih titik koordinat langsung pada peta.';
                        if (!isAutomatic) alert('Gagal mendeteksi lokasi GPS: ' + error.message);
                    },
                    { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
                );
            }

            function previewImage(input, previewId) {
                const preview = document.getElementById(previewId);
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                const form = document.getElementById('rtlh-form');
                if (form) {
                    form.addEventListener('submit', function (e) {
                        const submitFlag = document.getElementById('submit-flag');
                        if (submitFlag && submitFlag.value === '0') {
                            form.noValidate = true;
                            return true;
                        }

                        // Final submit: region is mandatory
                        const region = document.getElementById('region_id');
                        if (region && !region.value) {
                            e.preventDefault();
                            alert('Silakan pilih Wilayah Administratif (Kelurahan) pada Step 1 terlebih dahulu.');
                            switchStep(1);
                            region.focus();
                            return false;
                        }
                    });
                }

                @if ($errors->any())
                    const errorKeys = @js(array_keys($errors->messages()));
                    const step1Keys = ['region_id', 'nik', 'head_of_household_name', 'address', 'block', 'rt', 'rw'];
                    const step3Keys = ['funding_source', 'budget_amount', 'realization_status', 'proposal_document', 'kk_document', 'akte_document', 'land_document', 'proposal_notes'];

                    if (errorKeys.some(k => step1Keys.includes(k))) {
                        switchStep(1);
                    } else if (errorKeys.some(k => step3Keys.includes(k))) {
                        switchStep(3);
                    } else {
                        switchStep(2);
                    }
                @endif
            });
        </script>
    @endpush

</x-layouts.app>
