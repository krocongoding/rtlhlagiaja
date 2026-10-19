<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="kicker">
                SANITASI & UTILITAS
            </div>
            <h1>Sanitasi & Utilitas — {{ $house->house_code }}</h1>
            <p class="muted">
                Kelola informasi sumber air minum, fasilitas jamban keluarga, dan penerangan rumah.
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

        <form method="POST" action="{{ route('houses.sanitation-utility.update', $house) }}">
            @csrf
            @method('PUT')

            {{-- SANITASI --}}
            <div class="card" style="margin-bottom: 20px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                    <div class="stat-widget-icon blue" style="width:34px; height:34px; font-size:15px; margin:0;">
                        1
                    </div>
                    <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                        Fasilitas Air Bersih & Sanitasi
                    </h3>
                </div>

                <div class="grid2">
                    <div class="form-group">
                        <label for="water_source_id">Sumber Air Minum</label>
                        <select id="water_source_id" name="water_source_id">
                            <option value="">-- Pilih Sumber Air --</option>
                            @foreach ($waterSources as $item)
                                <option value="{{ $item->id }}" @selected(old('water_source_id', $house->sanitation?->water_source_id) == $item->id)>
                                    {{ $item->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="toilet_available">Ketersediaan Jamban / WC</label>
                        <select id="toilet_available" name="toilet_available">
                            <option value="">-- Pilih Ketersediaan --</option>
                            <option value="1" @selected(old('toilet_available', is_null($house->sanitation?->toilet_available) ? '' : (int)$house->sanitation?->toilet_available) === 1)>ADA / MEMILIKI SENDIRI</option>
                            <option value="0" @selected(old('toilet_available', is_null($house->sanitation?->toilet_available) ? '' : (int)$house->sanitation?->toilet_available) === 0)>TIDAK PUNYA / MENUMPANG</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="toilet_type_id">Jenis Jamban</label>
                        <select id="toilet_type_id" name="toilet_type_id">
                            <option value="">-- Pilih Jenis Jamban --</option>
                            @foreach ($toiletTypes as $item)
                                <option value="{{ $item->id }}" @selected(old('toilet_type_id', $house->sanitation?->toilet_type_id) == $item->id)>
                                    {{ $item->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecal_disposal_type_id">Tempat Pembuangan Akhir (TPA) Tinja</label>
                        <select id="fecal_disposal_type_id" name="fecal_disposal_type_id">
                            <option value="">-- Pilih TPA Tinja --</option>
                            @foreach ($fecalDisposals as $item)
                                <option value="{{ $item->id }}" @selected(old('fecal_disposal_type_id', $house->sanitation?->fecal_disposal_type_id) == $item->id)>
                                    {{ $item->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" style="grid-column: span 2;">
                        <label for="water_fecal_distance">Jarak Sumber Air ke TPA Tinja</label>
                        <input
                            type="text"
                            id="water_fecal_distance"
                            name="water_fecal_distance"
                            value="{{ old('water_fecal_distance', $house->sanitation?->water_fecal_distance) }}"
                            placeholder="Contoh: < 10 meter / > 10 meter"
                        >
                    </div>
                </div>
            </div>

            {{-- UTILITAS --}}
            <div class="card" style="margin-bottom: 24px;">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:18px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                    <div class="stat-widget-icon amber" style="width:34px; height:34px; font-size:15px; margin:0;">
                        2
                    </div>
                    <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                        Utilitas, Sirkulasi & Penerangan
                    </h3>
                </div>

                <div class="grid3">
                    <div class="form-group">
                        <label for="lighting_source_id">Sumber Penerangan / Listrik</label>
                        <select id="lighting_source_id" name="lighting_source_id">
                            <option value="">-- Pilih Penerangan --</option>
                            @foreach ($lightingSources as $item)
                                <option value="{{ $item->id }}" @selected(old('lighting_source_id', $house->utility?->lighting_source_id) == $item->id)>
                                    {{ $item->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="light_opening">Bukaan Cahaya / Jendela</label>
                        <input
                            type="text"
                            id="light_opening"
                            name="light_opening"
                            value="{{ old('light_opening', $house->utility?->light_opening) }}"
                            placeholder="Contoh: Ada / Kurang"
                        >
                    </div>

                    <div class="form-group">
                        <label for="ventilation">Ventilasi Udara</label>
                        <input
                            type="text"
                            id="ventilation"
                            name="ventilation"
                            value="{{ old('ventilation', $house->utility?->ventilation) }}"
                            placeholder="Contoh: Ada / Kurang"
                        >
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="actions" style="justify-content: flex-end;">
                <a href="{{ route('houses.show', $house) }}" class="btn">
                    Batal
                </a>
                <button type="submit" class="btn primary">
                    Simpan Sanitasi & Utilitas
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
