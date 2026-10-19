<x-layouts.public title="Peta WebGIS RTLH">

    @push('head')
        {{-- Leaflet CSS --}}
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <style>
            .custom-leaflet-pin {
                background: transparent;
                border: none;
            }
            .custom-pin {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 28px;
                height: 28px;
                border-radius: 50% 50% 50% 0;
                transform: rotate(-45deg);
                box-shadow: 0 3px 8px rgba(0,0,0,0.3);
                border: 2px solid #ffffff;
                transition: transform 0.15s ease, box-shadow 0.15s ease;
            }
            .custom-pin:hover {
                transform: rotate(-45deg) scale(1.15);
                box-shadow: 0 6px 14px rgba(0,0,0,0.4);
            }
            .custom-pin.pin-realized {
                background: #16a34a; /* HIJAU: Sudah Terealisasi */
            }
            .custom-pin.pin-not-realized {
                background: #eab308; /* KUNING: Belum Terealisasi */
            }
            .pin-inner-dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #ffffff;
                display: block;
                box-shadow: 0 1px 2px rgba(0,0,0,0.25);
            }
            .map-control-btn {
                background: #ffffff;
                border: 1px solid var(--slate-300);
                padding: 8px 14px;
                border-radius: var(--radius-md);
                cursor: pointer;
                font-weight: 600;
                font-size: 13px;
                color: var(--slate-700);
                display: inline-flex;
                align-items: center;
                gap: 6px;
                box-shadow: var(--shadow-sm);
                transition: all 0.2s;
            }
            .map-control-btn:hover {
                background: var(--slate-100);
                color: var(--slate-900);
            }
        </style>
    @endpush

    <div class="pagehead">
        <div>
            <div class="kicker">
                <i class="fa-solid fa-earth-asia"></i> SISTEM INFORMASI GEOGRAFIS
            </div>
            <h1>Peta Sebaran RTLH Kabupaten Cirebon</h1>
            <p class="muted">
                Visualisasi titik koordinat rumah tidak layak huni yang telah diverifikasi dan dipublikasikan.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('public.datasets') }}" class="btn">
                <i class="fa-solid fa-table-list"></i> Lihat Tabel Data
            </a>
            <a href="{{ route('public.statistics') }}" class="btn">
                <i class="fa-solid fa-chart-pie"></i> Statistik Wilayah
            </a>
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="card" style="margin-bottom: 20px; padding: 18px 24px;">
        <form id="map-filter" onsubmit="event.preventDefault(); reloadMap();" style="display:flex; flex-wrap:wrap; gap:14px; align-items:center;">
            <div style="flex: 1; min-width: 220px; position:relative;">
                <input
                    id="search"
                    type="text"
                    placeholder="Cari berdasarkan kode RTLH..."
                    autocomplete="off"
                    style="padding-left: 36px;"
                >
                <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--slate-400);"></i>
            </div>

            <div style="min-width: 200px;">
                <select id="region">
                    <option value="">Semua Kecamatan</option>
                </select>
            </div>

            <div style="min-width: 140px;">
                <select id="year">
                    <option value="">Semua Tahun</option>
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div style="min-width: 180px;">
                <select id="realization_status">
                    <option value="">Semua Realisasi</option>
                    <option value="realized">Sudah Terealisasi (Hijau)</option>
                    <option value="not_realized">Belum Terealisasi (Kuning)</option>
                </select>
            </div>

            <button type="submit" class="btn primary">
                <i class="fa-solid fa-filter"></i> Terapkan Filter
            </button>

            <button type="button" class="btn" onclick="resetMapFilter()">
                <i class="fa-solid fa-rotate-left"></i> Reset
            </button>

            <button type="button" class="btn" onclick="fitToCirebon()" title="Kembali ke fokus Kabupaten Cirebon">
                <i class="fa-solid fa-location-crosshairs"></i> Fokus Cirebon
            </button>
        </form>
    </div>

    {{-- STATUS BAR & LEGENDA REALISASI --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; font-size:14px; flex-wrap:wrap; gap:12px;">
        <div id="map-status" style="color:var(--slate-600); font-weight:600; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-circle-notch fa-spin text-emerald-600"></i> Memuat data geospasial...
        </div>

        <div style="display:flex; align-items:center; gap:14px; font-size:12px; color:var(--slate-700); background:#ffffff; padding:6px 14px; border-radius:var(--radius-md); border:1px solid var(--slate-200); box-shadow:var(--shadow-sm);">
            <strong style="color:var(--slate-800); font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">Status Realisasi:</strong>
            <span style="display:inline-flex; align-items:center; gap:6px;">
                <span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:#16a34a; border:2px solid #ffffff; box-shadow:0 0 0 1.5px #16a34a;"></span>
                <span style="font-weight:600; color:var(--slate-700);">Sudah Terealisasi</span>
            </span>
            <span style="display:inline-flex; align-items:center; gap:6px;">
                <span style="display:inline-block; width:12px; height:12px; border-radius:50%; background:#eab308; border:2px solid #ffffff; box-shadow:0 0 0 1.5px #ca8a04;"></span>
                <span style="font-weight:600; color:var(--slate-700);">Belum Terealisasi (Kuning)</span>
            </span>
        </div>
    </div>

    {{-- MAP CONTAINER --}}
    <div id="map" class="mapbox"></div>

    {{-- MAP INFO & LEGEND --}}
    <div class="grid3" style="margin-top: 24px;">
        <div class="card">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                <div class="stat-widget-icon emerald" style="width:36px; height:36px; font-size:16px; margin:0;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h4 style="font-size:15px; font-weight:700; margin:0;">Privasi Data</h4>
            </div>
            <p class="muted" style="font-size:13px;">
                Nama penghuni dan nomor identitas kependudukan dilindungi secara hukum dan hanya informasi fisik rumah yang ditampilkan ke publik.
            </p>
        </div>

        <div class="card">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                <div class="stat-widget-icon blue" style="width:36px; height:36px; font-size:16px; margin:0;">
                    <i class="fa-solid fa-crosshairs"></i>
                </div>
                <h4 style="font-size:15px; font-weight:700; margin:0;">Akurasi Koordinat GPS</h4>
            </div>
            <p class="muted" style="font-size:13px;">
                Titik lokasi diambil secara langsung oleh petugas surveyor di lapangan menggunakan perangkat mobile berakurasi tinggi (WGS84).
            </p>
        </div>

        <div class="card">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:10px;">
                <div class="stat-widget-icon purple" style="width:36px; height:36px; font-size:16px; margin:0;">
                    <i class="fa-solid fa-code"></i>
                </div>
                <h4 style="font-size:15px; font-weight:700; margin:0;">Akses Integrasi API</h4>
            </div>
            <p class="muted" style="font-size:13px;">
                Data spasial ini dapat diakses secara terprogram melalui REST API publik untuk keperluan analisis GIS dan penelitian akademis.
            </p>
        </div>
    </div>

    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <script>
        let map = null;
        let layer = null;
        const cirebonCenter = [-6.7060, 108.5565];

        // Batas wilayah Kota Cirebon
        const KOTA_CIREBON_BOUNDS = L.latLngBounds(
            L.latLng(-6.7500, 108.5350),  // Southwest corner
            L.latLng(-6.6900, 108.5800)   // Northeast corner
        );

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function initMap() {
            map = L.map('map', {
                maxBounds: KOTA_CIREBON_BOUNDS.pad(0.05),
                maxBoundsViscosity: 1.0,
                minZoom: 12
            }).setView(cirebonCenter, 13);
            layer = L.layerGroup().addTo(map);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | Kota Cirebon'
            }).addTo(map);

            setTimeout(() => {
                map.invalidateSize();
            }, 300);
        }

        function fitToCirebon() {
            if (map) {
                map.fitBounds(KOTA_CIREBON_BOUNDS, { padding: [20, 20] });
            }
        }

        async function loadRegions() {
            const select = document.getElementById('region');

            try {
                const response = await fetch('/api/v1/public/regions');
                if (!response.ok) throw new Error('Gagal mengambil data wilayah.');

                const result = await response.json();
                const regions = Array.isArray(result.data) ? result.data : [];

                regions
                    .filter(region => region.type === 'district' || region.type === 'kecamatan' || region.level === 1)
                    .forEach(region => {
                        const option = document.createElement('option');
                        option.value = region.id;
                        option.textContent = region.name;
                        select.appendChild(option);
                    });
            } catch (error) {
                console.error('LOAD REGIONS ERROR:', error);
            }
        }

        // Custom icon maker with dynamic color: Hijau (Sudah Terealisasi) / Kuning (Belum Terealisasi)
        function createHouseIcon(isRealized) {
            const pinClass = isRealized ? 'pin-realized' : 'pin-not-realized';
            return L.divIcon({
                className: 'custom-leaflet-pin',
                html: `<div class="custom-pin ${pinClass}"><span class="pin-inner-dot"></span></div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 28],
                popupAnchor: [0, -28]
            });
        }

        async function reloadMap() {
            const status = document.getElementById('map-status');
            const search = document.getElementById('search').value.trim();
            const region = document.getElementById('region').value;
            const year = document.getElementById('year').value;
            const realizationStatus = document.getElementById('realization_status').value;

            layer.clearLayers();

            const params = new URLSearchParams();
            params.set('per_page', '500');

            if (search) params.set('search', search);
            if (region) params.set('region_id', region);
            if (year) params.set('year', year);
            if (realizationStatus) params.set('realization_status', realizationStatus);

            status.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin" style="color:#059669;"></i> Mengambil data RTLH dari server...`;

            try {
                const response = await fetch('/api/v1/public/rtlh?' + params.toString());
                if (!response.ok) throw new Error('API RTLH gagal merespons.');

                const result = await response.json();
                const houses = Array.isArray(result.data) ? result.data : [];

                let markerCount = 0;
                const bounds = [];

                houses.forEach(house => {
                    const coordinates = house.coordinates;
                    if (!Array.isArray(coordinates) || coordinates.length < 2) return;

                    const lng = Number(coordinates[0]);
                    const lat = Number(coordinates[1]);

                    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

                    const isRealized = (house.realization_status === 'realized');
                    const marker = L.marker([lat, lng], { icon: createHouseIcon(isRealized) });

                    const code = escapeHtml(house.house_code ?? '-');
                    const regionName = escapeHtml(house.region?.name ?? '-');
                    const area = escapeHtml(house.area_m2 ?? '-');
                    const surveyYear = escapeHtml(house.survey_year ?? '-');
                    const address = escapeHtml(house.address ?? '-');
                    const realBadge = isRealized
                        ? `<span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #86efac; font-weight:800; font-size:10px;">Sudah Terealisasi</span>`
                        : `<span class="badge" style="background:#fef9c3; color:#854d0e; border:1px solid #facc15; font-weight:800; font-size:10px;">Belum Terealisasi</span>`;
                    const statusBadge = (house.status === 'published')
                        ? `<span class="badge published" style="font-size:10px;">PUBLIK</span>`
                        : `<span class="badge" style="font-size:10px; background:#f1f5f9; color:#475569; border:1px solid #cbd5e1;">${escapeHtml((house.status ?? 'draft').toUpperCase())}</span>`;

                    marker.bindPopup(`
                        <div class="map-popup-card">
                            <div class="map-popup-header">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; gap:6px;">
                                    ${statusBadge}
                                    ${realBadge}
                                </div>
                                <div class="map-popup-code">${code}</div>
                                <div class="map-popup-region"><i class="fa-solid fa-location-dot"></i> ${regionName}</div>
                            </div>

                            <div class="map-popup-row">
                                <span class="map-popup-label">Tahun Survei:</span>
                                <span class="map-popup-val">${surveyYear}</span>
                            </div>

                            <div class="map-popup-row">
                                <span class="map-popup-label">Luas Bangunan:</span>
                                <span class="map-popup-val">${area} m²</span>
                            </div>

                            <div class="map-popup-row">
                                <span class="map-popup-label">Alamat / Blok:</span>
                                <span class="map-popup-val">${address !== '-' ? address : 'Blok Desa'}</span>
                            </div>

                            <div style="margin-top: 14px; border-top:1px solid var(--slate-100); padding-top:10px;">
                                <a href="/rtlh/${house.id}" class="btn small primary" style="width:100%; text-align:center;">
                                    Lihat Detail Lengkap
                                </a>
                            </div>
                        </div>
                    `);

                    marker.addTo(layer);
                    bounds.push([lat, lng]);
                    markerCount++;
                });

                if (bounds.length > 0) {
                    map.fitBounds(bounds, { padding: [40, 40], maxZoom: 16 });
                } else {
                    map.fitBounds(KOTA_CIREBON_BOUNDS, { padding: [20, 20] });
                }

                const total = Number(result.total ?? houses.length);
                if (total === 0) {
                    status.innerHTML = `<i class="fa-solid fa-circle-exclamation text-amber-500"></i> Tidak ditemukan data RTLH yang sesuai filter.`;
                } else if (markerCount < total) {
                    status.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-600" style="color:#059669;"></i> Ditemukan <strong>${total}</strong> data (<strong>${markerCount}</strong> memiliki titik koordinat peta).`;
                } else {
                    status.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-600" style="color:#059669;"></i> Menampilkan <strong>${total}</strong> titik RTLH di peta.`;
                }

            } catch (error) {
                console.error('LOAD MAP ERROR:', error);
                status.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-red-500"></i> Terjadi kendala saat memuat peta geospasial.`;
            }
        }

        function resetMapFilter() {
            document.getElementById('search').value = '';
            document.getElementById('region').value = '';
            document.getElementById('year').value = '';
            document.getElementById('realization_status').value = '';
            reloadMap();
        }

        document.addEventListener('DOMContentLoaded', async function () {
            initMap();
            await loadRegions();
            await reloadMap();
        });
    </script>

</x-layouts.public>