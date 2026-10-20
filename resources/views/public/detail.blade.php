<x-layouts.public title="Detail Unit RTLH {{ $house->house_code }}">

    @push('head')
        @if($house->latitude && $house->longitude)
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        @endif
    @endpush

    {{-- DETAIL HERO HEADER --}}
    <div class="pagehead">
        <div>
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                <span class="badge published">
                    <i class="fa-solid fa-circle-check"></i> DATA TERVERIFIKASI
                </span>
                @if($house->latestAssessment?->priority_level)
                    @php
                        $pri = strtolower($house->latestAssessment->priority_level);
                        $badgeClass = $pri === 'tinggi' || $pri === 'berat' ? 'badge priority-high' : ($pri === 'sedang' ? 'badge priority-medium' : 'badge priority-low');
                    @endphp
                    <span class="{{ $badgeClass }}">
                        Prioritas {{ ucfirst($house->latestAssessment->priority_level) }}
                    </span>
                @endif
            </div>

            <h1 style="display:flex; align-items:center; gap:12px; font-size:32px;">
                <i class="fa-solid fa-house text-emerald-600" style="color:#059669;"></i>
                {{ $house->house_code }}
            </h1>

            <p class="muted" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap;">
                <span><i class="fa-solid fa-location-dot"></i> {{ $house->region?->name ?? 'Kabupaten Cirebon' }}</span>
                @if($house->survey_year)
                    <span>•</span>
                    <span><i class="fa-regular fa-calendar"></i> Tahun Survei {{ $house->survey_year }}</span>
                @endif
            </p>
        </div>

        <div class="actions">
            <a class="btn" href="{{ route('public.map') }}">
                <i class="fa-solid fa-map-location-dot"></i> Lihat di Peta
            </a>
            <a class="btn" href="{{ route('public.datasets') }}">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Data
            </a>
        </div>
    </div>

    {{-- QUICK SPECS BAR --}}
    <div class="grid4" style="margin-bottom: 24px;">
        <div class="stat-widget">
            <div class="stat-label">Luas Bangunan</div>
            <div class="stat" style="font-size: 26px;">
                {{ $house->area_m2 ? number_format((float) $house->area_m2, 1, ',', '.') : '-' }} <span style="font-size:16px; font-weight:600; color:var(--slate-500);">m²</span>
            </div>
            <div class="stat-desc">Luas tapak lantai rumah</div>
        </div>

        <div class="stat-widget">
            <div class="stat-label">Tahun Pendataan</div>
            <div class="stat" style="font-size: 26px;">
                {{ $house->survey_year ?? '-' }}
            </div>
            <div class="stat-desc">Pelaksanaan survei lapangan</div>
        </div>

        <div class="stat-widget">
            <div class="stat-label">Skor Assessment</div>
            <div class="stat" style="font-size: 26px; color: var(--primary-700);">
                {{ $house->latestAssessment?->score ?? 'N/A' }}
            </div>
            <div class="stat-desc">Tingkat kelayakan hunian</div>
        </div>

        <div class="stat-widget">
            <div class="stat-label">Status Verifikasi</div>
            <div class="stat" style="font-size: 22px; color: #15803d;">
                <i class="fa-solid fa-badge-check"></i> Published
            </div>
            <div class="stat-desc">Dinas Perumahan Kab. Cirebon</div>
        </div>
    </div>

    {{-- GRID 2 COLUMNS: INFO & LOCATION MAP --}}
    <div class="grid2">
        {{-- ADMINISTRASI & FISIK DASAR --}}
        <div class="card">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:18px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                <div class="stat-widget-icon blue" style="width:36px; height:36px; font-size:16px; margin:0;">
                    <i class="fa-solid fa-id-card"></i>
                </div>
                <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                    Informasi Administratif & Kepemilikan
                </h3>
            </div>

            <p class="muted" style="font-size:12px; margin:-6px 0 14px;">Ringkasan ini berasal dari isian surveyor saat pendataan lapangan.</p>

            <table style="font-size:13px;">
                <tbody>
                    <tr>
                        <td style="width:160px; color:var(--slate-500); font-weight:600;">Kode RTLH</td>
                        <td><strong style="color:var(--slate-900);">{{ $house->house_code }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">Desa / Kelurahan</td>
                        <td>{{ $house->region?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">Kecamatan</td>
                        <td>{{ $house->region?->parent?->name ?? ($house->region?->level === 1 ? $house->region?->name : '-') }}</td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">Alamat / Blok</td>
                        <td>{{ auth()->check() ? ($house->address ?: ($house->block ? 'Blok ' . $house->block : 'Desa ' . ($house->region?->name ?? '-'))) : ('Wilayah Kelurahan ' . ($house->region?->name ?? '-')) }}</td>
                    </tr>
                    @auth
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">RT / RW</td>
                        <td>RT {{ $house->rt ?? '-' }} / RW {{ $house->rw ?? '-' }}</td>
                    </tr>
                    @endauth
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">Kawasan Permukiman</td>
                        <td>{{ $house->settlementCondition?->label ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">Fungsi Ruang</td>
                        <td>{{ $house->roomFunction?->label ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">Status Kepemilikan</td>
                        <td>{{ $house->ownershipStatus?->label ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">Status Tanah</td>
                        <td>{{ $house->landStatus?->label ?? '-' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- LOKASI GEOSPASIAL & MINI MAP --}}
        <div class="card">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div class="stat-widget-icon emerald" style="width:36px; height:36px; font-size:16px; margin:0;">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                        Titik Koordinat Geospasial
                    </h3>
                </div>
                @if(!empty($isFuzzed))
                    <span class="badge" style="background:#f0fdf4; color:#166534; font-size:11px; font-weight:700; border:1px solid #bbf7d0;">
                        <i class="fa-solid fa-shield-halved"></i> Titik Disamarkan ~50m
                    </span>
                @endif
            </div>

            @if(($displayLat ?? $house->latitude) !== null && ($displayLng ?? $house->longitude) !== null)
                <div id="mini-map" style="height:230px; width:100%; border-radius:var(--radius-md); border:1px solid var(--slate-200); margin-bottom:16px;"></div>

                <div style="display:flex; justify-content:space-between; gap:12px; background:var(--slate-50); padding:12px 16px; border-radius:var(--radius-md); font-size:13px;">
                    <div>
                        <span class="muted">Latitude:</span>
                        <code style="font-weight:700; color:var(--slate-800); margin-left:4px;">{{ $displayLat ?? $house->latitude }}</code>
                    </div>
                    <div>
                        <span class="muted">Longitude:</span>
                        <code style="font-weight:700; color:var(--slate-800); margin-left:4px;">{{ $displayLng ?? $house->longitude }}</code>
                    </div>
                </div>
            @else
                <div style="padding:48px 24px; text-align:center; background:var(--slate-50); border-radius:var(--radius-md);" class="muted">
                    <i class="fa-solid fa-location-pin-slash fa-2x" style="color:var(--slate-400); margin-bottom:10px;"></i>
                    <p>Titik koordinat GPS belum tersedia untuk unit ini.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- KONDISI FISIK, SANITASI, DAN UTILITAS --}}
    <div class="grid3" style="margin-top:24px;">
        {{-- STRUKTUR BANGUNAN --}}
        <div class="card">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
                <i class="fa-solid fa-cubes text-emerald-600" style="color:#059669;"></i>
                <h4 style="font-size:15px; font-weight:800; color:var(--slate-900); margin:0;">Struktur Utama</h4>
            </div>

            <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-200); padding-bottom:6px;">
                    <span class="muted">Pondasi / Sloof:</span>
                    <strong>{{ $house->structure?->sloofCondition?->label ?? '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-200); padding-bottom:6px;">
                    <span class="muted">Kolom / Tiang:</span>
                    <strong>{{ $house->structure?->columnCondition?->label ?? '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-200); padding-bottom:6px;">
                    <span class="muted">Balok Pengikat:</span>
                    <strong>{{ $house->structure?->beamCondition?->label ?? '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="muted">Rangka Atap:</span>
                    <strong>{{ $house->roof?->frameCondition?->label ?? '-' }}</strong>
                </div>
            </div>
        </div>

        {{-- KOMPONEN BANGUNAN --}}
        <div class="card">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
                <i class="fa-solid fa-house-chimney text-sky-600" style="color:#0284c7;"></i>
                <h4 style="font-size:15px; font-weight:800; color:var(--slate-900); margin:0;">Komponen Bangunan</h4>
            </div>

            <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-200); padding-bottom:6px;">
                    <span class="muted">Material Lantai:</span>
                    <strong>{{ $house->floor?->material?->label ?? '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-200); padding-bottom:6px;">
                    <span class="muted">Material Dinding:</span>
                    <strong>{{ $house->wall?->material?->label ?? '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-200); padding-bottom:6px;">
                    <span class="muted">Penutup Atap:</span>
                    <strong>{{ $house->roof?->material?->label ?? '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="muted">Plafon:</span>
                    <strong>{{ $house->ceiling?->condition?->label ?? '-' }}</strong>
                </div>
            </div>
        </div>

        {{-- SANITASI & UTILITAS --}}
        <div class="card">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
                <i class="fa-solid fa-faucet-detergent text-purple-600" style="color:#7c3aed;"></i>
                <h4 style="font-size:15px; font-weight:800; color:var(--slate-900); margin:0;">Sanitasi & Utilitas</h4>
            </div>

            <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-200); padding-bottom:6px;">
                    <span class="muted">Sumber Air:</span>
                    <strong>{{ $house->sanitation?->waterSource?->label ?? '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-200); padding-bottom:6px;">
                    <span class="muted">Jamban Keluarga:</span>
                    <strong>
                        @if($house->sanitation?->toilet_available === true)
                            <span class="badge published" style="font-size:11px;">Ada</span>
                        @elseif($house->sanitation?->toilet_available === false)
                            <span class="badge revision" style="font-size:11px;">Tidak Ada</span>
                        @else
                            -
                        @endif
                    </strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-200); padding-bottom:6px;">
                    <span class="muted">Penerangan / Listrik:</span>
                    <strong>{{ $house->utility?->lightingSource?->label ?? '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="muted">Ventilasi Udara:</span>
                    <strong>{{ $house->utility?->ventilation ?? '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- HASIL ASSESSMENT --}}
    @php
        $assessment = $house->latestAssessment;
    @endphp

    @if($assessment)
        <div class="card" style="margin-top:24px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <div class="stat-widget-icon purple" style="width:36px; height:36px; font-size:16px; margin:0;">
                        <i class="fa-solid fa-clipboard-list"></i>
                    </div>
                    <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                        Hasil Penilaian (Assessment Terakhir)
                    </h3>
                </div>

                <div>
                    <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700;">
                        Tanggal: {{ $assessment->assessment_date?->format('d/m/Y') ?? '-' }}
                    </span>
                </div>
            </div>

            <div class="grid3" style="margin-top:0;">
                <div>
                    <span class="muted" style="font-size:12px;">SKOR TOTAL</span>
                    <div style="font-size:24px; font-weight:800; color:var(--primary-700);">{{ $assessment->score ?? '-' }}</div>
                </div>

                <div>
                    <span class="muted" style="font-size:12px;">PRIORITAS PENANGANAN</span>
                    <div style="font-size:20px; font-weight:700; color:var(--slate-900);">{{ ucfirst($assessment->priority_level ?? '-') }}</div>
                </div>

                <div>
                    <span class="muted" style="font-size:12px;">STATUS VERIFIKASI</span>
                    <div style="font-size:18px; font-weight:700; color:#15803d;"><i class="fa-solid fa-check"></i> {{ ucfirst($assessment->status ?? 'Verified') }}</div>
                </div>
            </div>

            @if($assessment->notes)
                <div style="margin-top:16px; background:var(--slate-50); padding:14px 18px; border-radius:var(--radius-md); border-left:4px solid var(--primary-500);">
                    <div class="stat-label" style="font-size:11px; margin-bottom:4px;">Catatan Surveyor / Tim Verifikator</div>
                    <p style="font-size:13px; color:var(--slate-700); margin:0;">{{ $assessment->notes }}</p>
                </div>
            @endif

            @if($assessment->items && $assessment->items->isNotEmpty())
                <div style="margin-top:20px;">
                    <h4 style="font-size:14px; font-weight:700; margin-bottom:10px; color:var(--slate-800);">
                        Rincian Parameter Kondisi Hunian
                    </h4>
                    <div class="tablewrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Kategori Komponen</th>
                                    <th>Kondisi Terukur</th>
                                    <th>Catatan Tambahan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assessment->items as $item)
                                    <tr>
                                        <td><strong>{{ ucwords(str_replace('_', ' ', $item->category)) }}</strong></td>
                                        <td>
                                            <span class="badge" style="background:var(--slate-100); color:var(--slate-800);">
                                                {{ $item->value ?? '-' }}
                                            </span>
                                            @if(strtoupper($item->value ?? '') === 'OTHER' && $item->other_value)
                                                <div class="muted" style="font-size:12px; margin-top:2px;">{{ $item->other_value }}</div>
                                            @endif
                                        </td>
                                        <td class="muted">{{ $item->notes ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- FOTO HASIL SURVEI --}}
    <section class="card" style="margin-top:24px;">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
            <div class="stat-widget-icon purple" style="width:36px; height:36px; font-size:16px; margin:0;">
                <i class="fa-solid fa-images"></i>
            </div>
            <div>
                <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">Dokumentasi Hasil Survei</h3>
                <p class="muted" style="font-size:12px; margin:2px 0 0;">Foto kondisi fisik rumah yang diunggah oleh surveyor.</p>
            </div>
        </div>

        @if($house->photos->isNotEmpty())
            <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:16px;">
                @foreach($house->photos as $photo)
                    <figure style="margin:0; overflow:hidden; border:1px solid var(--slate-200); border-radius:var(--radius-md); background:var(--slate-50);">
                        <button type="button" onclick="openPhotoPreview(@js(asset('storage/' . $photo->path)), @js($photo->caption ?: ucwords(str_replace('_', ' ', $photo->type))))" style="display:block; width:100%; border:0; padding:0; background:none; cursor:zoom-in;" title="Klik untuk melihat foto lebih besar">
                            <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $photo->caption ?: 'Foto survei ' . str_replace('_', ' ', $photo->type) }}" style="display:block; width:100%; height:160px; object-fit:cover;" loading="lazy">
                        </button>
                        <figcaption style="padding:10px 12px; font-size:12px; font-weight:700; color:var(--slate-700);">{{ $photo->caption ?: ucwords(str_replace('_', ' ', $photo->type)) }}</figcaption>
                    </figure>
                @endforeach
            </div>
        @else
            <div class="muted" style="padding:28px; text-align:center; background:var(--slate-50); border-radius:var(--radius-md);">
                <i class="fa-solid fa-image" style="margin-right:6px;"></i> Belum ada foto survei yang dipublikasikan untuk unit ini.
            </div>
        @endif
    </section>

    <div id="photo-preview-modal" role="dialog" aria-modal="true" aria-label="Pratinjau foto dokumentasi" onclick="closePhotoPreview()" style="display:none; position:fixed; inset:0; z-index:2000; padding:24px; background:rgba(2, 6, 23, .88); align-items:center; justify-content:center;">
        <div onclick="event.stopPropagation()" style="position:relative; max-width:min(1100px, 100%); max-height:100%;">
            <button type="button" onclick="closePhotoPreview()" aria-label="Tutup pratinjau foto" style="position:absolute; top:12px; right:12px; z-index:1; width:38px; height:38px; border:0; border-radius:50%; background:rgba(15,23,42,.8); color:#fff; font-size:20px; cursor:pointer;"><i class="fa-solid fa-xmark"></i></button>
            <img id="photo-preview-image" src="" alt="" style="display:block; max-width:100%; max-height:82vh; border-radius:var(--radius-md); object-fit:contain;">
            <p id="photo-preview-caption" style="margin:10px 0 0; text-align:center; color:#fff; font-size:14px;"></p>
        </div>
    </div>

    {{-- PRIVACY DISCLAIMER --}}
    <div class="alert info" style="margin-top:24px;">
        <i class="fa-solid fa-shield-halved fa-lg"></i>
        <div style="font-size:13px;">
            Informasi pada halaman ini merupakan ringkasan data publik Rumah Tidak Layak Huni (RTLH) yang diterbitkan oleh Dinas Perumahan, Kawasan Permukiman dan Pertanahan Kabupaten Cirebon. Informasi identitas pribadi dilindungi sesuai ketentuan peraturan yang berlaku.
        </div>
    </div>

    @push('scripts')
        <script>
            function openPhotoPreview(source, caption) {
                document.getElementById('photo-preview-image').src = source;
                document.getElementById('photo-preview-image').alt = caption;
                document.getElementById('photo-preview-caption').textContent = caption;
                document.getElementById('photo-preview-modal').style.display = 'flex';
            }

            function closePhotoPreview() {
                document.getElementById('photo-preview-modal').style.display = 'none';
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') closePhotoPreview();
            });
        </script>
        @if(($displayLat ?? $house->latitude) !== null && ($displayLng ?? $house->longitude) !== null)
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const lat = {{ $displayLat ?? $house->latitude }};
                    const lng = {{ $displayLng ?? $house->longitude }};
                    const miniMap = L.map('mini-map', { zoomControl: false, scrollWheelZoom: false }).setView([lat, lng], 15);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(miniMap);

                    const isRealized = {{ $house->isRealized() ? 'true' : 'false' }};
                    const pinColor = isRealized ? '#16a34a' : '#eab308';
                    const customIcon = L.divIcon({
                        className: 'custom-house-pin',
                        html: `<div style="background:${pinColor}; width:20px; height:20px; border-radius:50%; border:3px solid #ffffff; box-shadow:0 2px 8px rgba(0,0,0,0.35);"></div>`,
                        iconSize: [20, 20],
                        iconAnchor: [10, 10],
                        popupAnchor: [0, -12]
                    });

                    L.marker([lat, lng], { icon: customIcon }).addTo(miniMap)
                        .bindPopup(`
                            <div style="font-family:sans-serif; font-size:12px; line-height:1.4;">
                                <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:4px;">
                                    <strong style="color:#0f172a; font-family:monospace;">{{ $house->house_code }}</strong>
                                    <span style="font-size:10px; font-weight:800; padding:2px 6px; border-radius:999px; ${isRealized ? 'background:#dcfce7; color:#15803d;' : 'background:#fef3c7; color:#b45309;'}">
                                        ${isRealized ? 'Sudah Terealisasi' : 'Belum Terealisasi'}
                                    </span>
                                </div>
                                <span style="color:#475569;">{{ $house->region?->name ?? 'Kabupaten Cirebon' }}</span>
                            </div>
                        `)
                        .openPopup();
                });
            </script>
        @endif
    @endpush

</x-layouts.public>
