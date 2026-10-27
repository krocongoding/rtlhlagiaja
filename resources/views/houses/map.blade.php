<x-app-layout title="Peta Sebaran & Realisasi RTLH">

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
                width: 30px;
                height: 30px;
                border-radius: 50% 50% 50% 0;
                transform: rotate(-45deg);
                box-shadow: 0 4px 10px rgba(0,0,0,0.35);
                border: 2px solid #ffffff;
                transition: transform 0.15s ease, box-shadow 0.15s ease;
            }
            .custom-pin:hover {
                transform: rotate(-45deg) scale(1.18);
                box-shadow: 0 6px 16px rgba(0,0,0,0.45);
                z-index: 1000 !important;
            }
            .custom-pin.pin-realized {
                background: #16a34a; /* HIJAU: Sudah Terealisasi */
            }
            .custom-pin.pin-not-realized {
                background: #eab308; /* KUNING: Belum Terealisasi */
            }
            .pin-inner-dot {
                width: 9px;
                height: 9px;
                border-radius: 50%;
                background: #ffffff;
                display: block;
                box-shadow: 0 1px 2px rgba(0,0,0,0.3);
            }
            .user-loc-pin {
                background: transparent;
                border: none;
            }
            .user-pulse {
                width: 18px;
                height: 18px;
                background: #0284c7;
                border: 3px solid #ffffff;
                border-radius: 50%;
                box-shadow: 0 0 0 rgba(2, 132, 199, 0.6);
                animation: pulseUserLoc 1.8s infinite;
            }
            @keyframes pulseUserLoc {
                0% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0.7); }
                70% { box-shadow: 0 0 0 16px rgba(2, 132, 199, 0); }
                100% { box-shadow: 0 0 0 0 rgba(2, 132, 199, 0); }
            }
            .map-layer-btn {
                border: none;
                background: transparent;
                padding: 6px 12px;
                border-radius: 8px;
                font-size: 12px;
                font-weight: 700;
                color: var(--slate-700);
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: all 0.18s ease;
            }
            .map-layer-btn:hover {
                background: rgba(0,0,0,0.06);
                color: var(--slate-900);
            }
            .map-layer-btn.active {
                background: #0284c7;
                color: #ffffff;
                box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35);
            }
            /* Popup styling */
            .leaflet-popup-content-wrapper {
                border-radius: 12px;
                padding: 0;
                overflow: hidden;
                box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
            }
            .leaflet-popup-content {
                margin: 0;
                line-height: 1.4;
            }
        </style>
    @endpush

    <x-slot name="header">
        <div>
            <div class="kicker">
                <i class="fa-solid fa-earth-asia"></i> SISTEM INFORMASI GEOGRAFIS (GIS) PETUGAS
            </div>
            <h1>Peta Sebaran & Realisasi RTLH Kota Cirebon</h1>
            <p class="muted">
                Visualisasi spasial data survei fisik hunian untuk memantau rumah yang <strong>Sudah Terealisasi</strong> dan <strong>Belum Terealisasi</strong>.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('houses.index') }}" class="btn">
                <i class="fa-solid fa-table-list"></i> Tabel Data RTLH
            </a>
            <a href="{{ route('public.map') }}" target="_blank" class="btn" title="Buka Peta Publik">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Peta Publik
            </a>
        </div>
    </x-slot>

    {{-- STAT WIDGETS REALISASI --}}
    @php
        $realizedPercent = $totalCoordinates > 0 ? round(($realizedCount / $totalCoordinates) * 100, 1) : 0;
    @endphp
    <div class="grid4" style="margin-bottom: 22px;">
        <div class="card stat-card" style="border-left: 4px solid var(--primary-600);">
            <div style="font-size:11.5px; font-weight:700; color:var(--slate-500); text-transform:uppercase;">
                Total Titik Terkoordinat
            </div>
            <div style="font-size:26px; font-weight:900; color:var(--slate-900); margin-top:4px;">
                {{ number_format($totalCoordinates) }}
            </div>
            <div style="font-size:12px; color:var(--slate-500); margin-top:2px;">
                Data fisik berlokasi WGS84
            </div>
        </div>

        <div class="card stat-card" style="border-left: 4px solid #16a34a; background:linear-gradient(180deg, #f0fdf4 0%, #ffffff 100%);">
            <div style="font-size:11.5px; font-weight:700; color:#15803d; text-transform:uppercase;">
                Sudah Terealisasi
            </div>
            <div style="font-size:26px; font-weight:900; color:#16a34a; margin-top:4px;">
                {{ number_format($realizedCount) }}
            </div>
            <div style="font-size:12px; color:#15803d; margin-top:2px;">
                <i class="fa-solid fa-circle-check"></i> Ditandai Pin Hijau
            </div>
        </div>

        <div class="card stat-card" style="border-left: 4px solid #ca8a04; background:linear-gradient(180deg, #fefce8 0%, #ffffff 100%);">
            <div style="font-size:11.5px; font-weight:700; color:#a16207; text-transform:uppercase;">
                Belum Terealisasi
            </div>
            <div style="font-size:26px; font-weight:900; color:#ca8a04; margin-top:4px;">
                {{ number_format($notRealizedCount) }}
            </div>
            <div style="font-size:12px; color:#a16207; margin-top:2px;">
                <i class="fa-solid fa-clock"></i> Ditandai Pin Kuning
            </div>
        </div>

        <div class="card stat-card" style="border-left: 4px solid #0284c7;">
            <div style="font-size:11.5px; font-weight:700; color:var(--slate-500); text-transform:uppercase;">
                Capaian Realisasi
            </div>
            <div style="font-size:26px; font-weight:900; color:#0284c7; margin-top:4px;">
                {{ $realizedPercent }}%
            </div>
            <div style="font-size:12px; color:var(--slate-500); margin-top:2px;">
                Rasio selesai perbaikan
            </div>
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="card" style="margin-bottom: 20px; padding: 18px 22px;">
        <form id="map-filter" onsubmit="event.preventDefault(); reloadMap();" style="display:flex; flex-wrap:wrap; gap:12px; align-items:center;">
            {{-- SEARCH --}}
            <div style="flex: 1; min-width: 220px; position:relative;">
                <input
                    id="search"
                    type="text"
                    placeholder="Cari kode RTLH atau nama..."
                    autocomplete="off"
                    style="padding-left: 36px;"
                >
                <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--slate-400);"></i>
            </div>

            {{-- FILTER STATUS REALISASI --}}
            <div style="min-width: 200px;">
                <select id="realization_status" onchange="reloadMap()" style="font-weight:700; color:var(--slate-800);">
                    <option value="">Semua Status Realisasi</option>
                    <option value="realized">Sudah Terealisasi (Pin Hijau)</option>
                    <option value="not_realized">Belum Terealisasi (Pin Kuning)</option>
                </select>
            </div>

            {{-- FILTER KECAMATAN / WILAYAH --}}
            <div style="min-width: 180px;">
                <select id="region_id" onchange="reloadMap()">
                    <option value="">Semua Kecamatan</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- FILTER TAHUN --}}
            <div style="min-width: 130px;">
                <select id="year" onchange="reloadMap()">
                    <option value="">Semua Tahun</option>
                    @for($y = date('Y'); $y >= 2020; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            </div>

            {{-- TOGGLE HANYA DATA SAYA (SURVEYOR) --}}
            @if(!auth()->user()->hasRole('admin'))
                <label style="display:inline-flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; font-weight:700; color:var(--slate-700); background:var(--slate-50); padding:7px 12px; border-radius:var(--radius-md); border:1px solid var(--slate-200);">
                    <input type="checkbox" id="mine" onchange="reloadMap()" style="width:16px; height:16px;">
                    <span>Hanya Data Saya</span>
                </label>
            @endif

            {{-- TOMBOL FILTER --}}
            <button type="submit" class="btn primary">
                <i class="fa-solid fa-filter"></i> Filter
            </button>

            <button type="button" class="btn" onclick="resetMapFilter()">
                <i class="fa-solid fa-rotate-left"></i> Reset
            </button>

            <button type="button" class="btn" onclick="fitToCirebon()" title="Kembali ke fokus Kota Cirebon">
                <i class="fa-solid fa-location-crosshairs"></i> Fokus Cirebon
            </button>
        </form>
    </div>

    {{-- STATUS BAR & LEGENDA REALISASI --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; font-size:13.5px; flex-wrap:wrap; gap:12px;">
        <div id="map-status" style="color:var(--slate-600); font-weight:700; display:flex; align-items:center; gap:8px;">
            <i class="fa-solid fa-circle-notch fa-spin text-emerald-600"></i> Memuat data titik RTLH...
        </div>

        <div style="display:flex; align-items:center; gap:14px; font-size:12px; color:var(--slate-700); background:#ffffff; padding:6px 14px; border-radius:var(--radius-md); border:1px solid var(--slate-200); box-shadow:var(--shadow-sm);">
            <strong style="color:var(--slate-800); font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">Legenda Peta:</strong>
            <span style="display:inline-flex; align-items:center; gap:6px;">
                <span style="display:inline-block; width:13px; height:13px; border-radius:50%; background:#16a34a; border:2px solid #ffffff; box-shadow:0 0 0 1.5px #16a34a;"></span>
                <span style="font-weight:700; color:#15803d;">Sudah Terealisasi</span>
            </span>
            <span style="display:inline-flex; align-items:center; gap:6px;">
                <span style="display:inline-block; width:13px; height:13px; border-radius:50%; background:#eab308; border:2px solid #ffffff; box-shadow:0 0 0 1.5px #ca8a04;"></span>
                <span style="font-weight:700; color:#a16207;">Belum Terealisasi</span>
            </span>
        </div>
    </div>

    {{-- MAP CONTAINER DENGAN FLOATING TOOLBAR --}}
    <div style="position:relative; border-radius:var(--radius-xl); overflow:hidden; box-shadow:var(--shadow-md); border:1px solid var(--slate-200);" id="map-wrapper">
        {{-- FLOATING TOOLBAR OVER MAP --}}
        <div class="map-floating-bar" style="position:absolute; top:14px; right:14px; z-index:1000; display:flex; gap:6px; flex-wrap:wrap; background:rgba(255,255,255,0.95); backdrop-filter:blur(10px); padding:6px; border-radius:12px; box-shadow:0 4px 18px rgba(0,0,0,0.15); border:1px solid rgba(226,232,240,0.9);">
            <button type="button" class="map-layer-btn active" id="btn-layer-osm" onclick="switchTileLayer('osm')" title="Peta Jalan Vektor">
                <i class="fa-solid fa-map"></i> Peta Jalan
            </button>
            <button type="button" class="map-layer-btn" id="btn-layer-satellite" onclick="switchTileLayer('satellite')" title="Citra Satelit Resolusi Tinggi">
                <i class="fa-solid fa-satellite"></i> Citra Satelit
            </button>
            <div style="width:1px; background:#cbd5e1; margin:4px 2px;"></div>
            <button type="button" class="map-layer-btn" id="btn-locate" onclick="locateUser()" title="Temukan Lokasi Saya Sekarang (GPS)">
                <i class="fa-solid fa-crosshairs"></i> Lokasi Saya
            </button>
            <button type="button" class="map-layer-btn" id="btn-fullscreen" onclick="toggleMapFullscreen()" title="Tampilan Layar Penuh">
                <i class="fa-solid fa-expand"></i> Layar Penuh
            </button>
        </div>

        <div id="map" class="mapbox" style="height:640px; width:100%;"></div>
    </div>

    {{-- LEAFLET JS SCRIPT --}}
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            let map = null;
            let layer = null;
            let currentTileLayer = null;
            let userMarker = null;

            const cirebonCenter = [-6.7060, 108.5565];

            // Batas wilayah Kota Cirebon
            const KOTA_CIREBON_BOUNDS = L.latLngBounds(
                L.latLng(-6.7500, 108.5350),
                L.latLng(-6.6900, 108.5800)
            );

            // Tile Providers
            const osmTile = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors | Kota Cirebon'
            });

            const satelliteTile = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 19,
                attribution: 'Tiles &copy; Esri &mdash; Citra Satelit Resolusi Tinggi'
            });

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
                    maxBounds: KOTA_CIREBON_BOUNDS.pad(0.08),
                    maxBoundsViscosity: 0.9,
                    minZoom: 11
                }).setView(cirebonCenter, 13);

                currentTileLayer = osmTile.addTo(map);
                layer = L.layerGroup().addTo(map);

                setTimeout(() => {
                    map.invalidateSize();
                }, 300);

                reloadMap();
            }

            function switchTileLayer(type) {
                if (!map) return;
                map.removeLayer(currentTileLayer);

                const btnOsm = document.getElementById('btn-layer-osm');
                const btnSat = document.getElementById('btn-layer-satellite');

                if (type === 'satellite') {
                    currentTileLayer = satelliteTile.addTo(map);
                    if (btnSat) btnSat.classList.add('active');
                    if (btnOsm) btnOsm.classList.remove('active');
                } else {
                    currentTileLayer = osmTile.addTo(map);
                    if (btnOsm) btnOsm.classList.add('active');
                    if (btnSat) btnSat.classList.remove('active');
                }
            }

            function locateUser() {
                if (!navigator.geolocation) {
                    alert('Fitur Geolocation tidak didukung oleh perangkat atau browser Anda.');
                    return;
                }

                const btn = document.getElementById('btn-locate');
                const originalHtml = btn ? btn.innerHTML : '';
                if (btn) btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mendeteksi GPS...';

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;

                        if (userMarker && map) {
                            map.removeLayer(userMarker);
                        }

                        const userIcon = L.divIcon({
                            className: 'user-loc-pin',
                            html: '<div class="user-pulse"></div>',
                            iconSize: [20, 20],
                            iconAnchor: [10, 10]
                        });

                        userMarker = L.marker([lat, lng], { icon: userIcon }).addTo(map);
                        userMarker.bindPopup(`
                            <div style="font-size:12.5px; line-height:1.4; padding:6px;">
                                <strong style="color:#0284c7;"><i class="fa-solid fa-location-crosshairs"></i> Posisi Anda Saat Ini</strong>
                                <div style="color:#64748b; font-size:11px; margin-top:2px;">Akurasi perkiraan: &plusmn;${Math.round(pos.coords.accuracy)} meter</div>
                            </div>
                        `).openPopup();

                        map.setView([lat, lng], 16);
                        if (btn) btn.innerHTML = originalHtml;
                    },
                    (err) => {
                        alert('Gagal mendeteksi lokasi GPS: ' + (err.message || 'Izin lokasi tidak diberikan.'));
                        if (btn) btn.innerHTML = originalHtml;
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            }

            function toggleMapFullscreen() {
                const wrapper = document.getElementById('map-wrapper');
                const btn = document.getElementById('btn-fullscreen');

                if (!document.fullscreenElement) {
                    if (wrapper.requestFullscreen) {
                        wrapper.requestFullscreen();
                    } else if (wrapper.webkitRequestFullscreen) {
                        wrapper.webkitRequestFullscreen();
                    }
                    if (btn) btn.innerHTML = '<i class="fa-solid fa-compress"></i> Perkecil';
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    }
                    if (btn) btn.innerHTML = '<i class="fa-solid fa-expand"></i> Layar Penuh';
                }
            }

            document.addEventListener('fullscreenchange', () => {
                if (map) {
                    setTimeout(() => map.invalidateSize(), 250);
                }
                const btn = document.getElementById('btn-fullscreen');
                if (btn && !document.fullscreenElement) {
                    btn.innerHTML = '<i class="fa-solid fa-expand"></i> Layar Penuh';
                }
            });

            function fitToCirebon() {
                if (map) {
                    map.fitBounds(KOTA_CIREBON_BOUNDS, { padding: [20, 20] });
                }
            }

            function resetMapFilter() {
                document.getElementById('search').value = '';
                document.getElementById('realization_status').value = '';
                document.getElementById('region_id').value = '';
                document.getElementById('year').value = '';
                const mineCheckbox = document.getElementById('mine');
                if (mineCheckbox) mineCheckbox.checked = false;

                reloadMap();
            }

            function createHouseIcon(isRealized) {
                const pinClass = isRealized ? 'pin-realized' : 'pin-not-realized';
                return L.divIcon({
                    className: 'custom-leaflet-pin',
                    html: `<div class="custom-pin ${pinClass}"><span class="pin-inner-dot"></span></div>`,
                    iconSize: [30, 30],
                    iconAnchor: [15, 30],
                    popupAnchor: [0, -30]
                });
            }

            async function reloadMap() {
                const status = document.getElementById('map-status');
                const search = document.getElementById('search').value.trim();
                const region = document.getElementById('region_id').value;
                const year = document.getElementById('year').value;
                const realizationStatus = document.getElementById('realization_status').value;
                const mineCheckbox = document.getElementById('mine');
                const isMine = mineCheckbox ? mineCheckbox.checked : false;

                layer.clearLayers();

                const params = new URLSearchParams();
                params.set('per_page', '500');

                if (search) params.set('search', search);
                if (region) params.set('region_id', region);
                if (year) params.set('year', year);
                if (realizationStatus) params.set('realization_status', realizationStatus);
                if (isMine) params.set('mine', '1');

                status.innerHTML = `<i class="fa-solid fa-circle-notch fa-spin text-emerald-600"></i> Mengambil data titik RTLH dari server...`;

                try {
                    const response = await fetch('/api/v1/public/rtlh?' + params.toString());
                    if (!response.ok) throw new Error('API RTLH gagal merespons.');

                    const result = await response.json();
                    const houses = Array.isArray(result.data) ? result.data : [];

                    let markerCount = 0;
                    let realizedCount = 0;
                    let notRealizedCount = 0;
                    const bounds = [];

                    houses.forEach(house => {
                        const coordinates = house.coordinates;
                        if (!Array.isArray(coordinates) || coordinates.length < 2) return;

                        const lng = Number(coordinates[0]);
                        const lat = Number(coordinates[1]);

                        if (isNaN(lat) || isNaN(lng) || lat === 0 || lng === 0) return;

                        const isRealized = (house.realization_status === 'realized');
                        if (isRealized) realizedCount++; else notRealizedCount++;

                        const icon = createHouseIcon(isRealized);
                        const marker = L.marker([lat, lng], { icon: icon });

                        // Status badge text
                        const realizationBadge = isRealized
                            ? `<span style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:800;"><i class="fa-solid fa-circle-check"></i> Sudah Terealisasi</span>`
                            : `<span style="background:#fef9c3; color:#854d0e; border:1px solid #fef08a; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:800;"><i class="fa-solid fa-clock"></i> Belum Terealisasi</span>`;

                        const beneficiaryName = house.head_of_household_name || 'Penerima Bantuan';
                        const regionName = house.region ? house.region.name : '-';
                        const addressText = house.address ? escapeHtml(house.address) : `Kel. ${regionName}`;

                        // Budget string
                        let budgetInfo = '';
                        if (house.budget_amount) {
                            const formattedBudget = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(house.budget_amount);
                            budgetInfo = `
                                <div style="margin-top:6px; font-size:11.5px; color:#475569; background:#f8fafc; padding:4px 8px; border-radius:6px; border:1px solid #e2e8f0;">
                                    <strong>Anggaran:</strong> ${formattedBudget} ${house.funding_source ? `(${escapeHtml(house.funding_source)})` : ''}
                                </div>
                            `;
                        }

                        const popupHtml = `
                            <div style="min-width:240px; max-width:280px; font-family:'Plus Jakarta Sans',sans-serif; padding:12px 14px;">
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                    <strong style="font-size:13px; color:#0f172a; font-family:monospace;">${escapeHtml(house.house_code)}</strong>
                                    <span style="font-size:10px; font-weight:700; text-transform:uppercase; color:#64748b; background:#f1f5f9; padding:2px 6px; border-radius:4px;">${escapeHtml(house.status || 'draft')}</span>
                                </div>

                                <div style="margin-bottom:8px;">
                                    ${realizationBadge}
                                </div>

                                <div style="font-size:13px; font-weight:800; color:#1e293b; margin-bottom:2px;">
                                    ${escapeHtml(beneficiaryName)}
                                </div>

                                <div style="font-size:11.5px; color:#64748b; margin-bottom:6px;">
                                    <i class="fa-solid fa-location-dot" style="color:#ef4444; margin-right:4px;"></i>${addressText}
                                </div>

                                ${budgetInfo}

                                <div style="margin-top:12px; display:flex; gap:6px;">
                                    <a href="/houses/${house.id}" class="btn small primary" style="flex:1; text-align:center; font-size:11px; padding:5px 8px; text-decoration:none; font-weight:700;">
                                        Lihat Detail &rarr;
                                    </a>
                                </div>
                            </div>
                        `;

                        marker.bindPopup(popupHtml);
                        layer.addLayer(marker);

                        bounds.push([lat, lng]);
                        markerCount++;
                    });

                    status.innerHTML = `
                        <span style="color:#059669;"><i class="fa-solid fa-check"></i> Menampilkan <strong>${markerCount}</strong> titik RTLH</span>
                        <span style="color:var(--slate-400); margin:0 4px;">•</span>
                        <span style="color:#15803d; font-weight:700;"><i class="fa-solid fa-circle" style="font-size:8px;"></i> ${realizedCount} Terealisasi</span>
                        <span style="color:var(--slate-400); margin:0 4px;">•</span>
                        <span style="color:#a16207; font-weight:700;"><i class="fa-solid fa-circle" style="font-size:8px;"></i> ${notRealizedCount} Belum</span>
                    `;

                    if (bounds.length > 0 && !search && !region) {
                        // Keep current view or fit
                    } else if (bounds.length > 0) {
                        map.fitBounds(bounds, { padding: [30, 30], maxZoom: 16 });
                    }
                } catch (error) {
                    console.error('RELOAD MAP ERROR:', error);
                    status.innerHTML = `<span style="color:#dc2626;"><i class="fa-solid fa-triangle-exclamation"></i> Gagal memuat data titik RTLH.</span>`;
                }
            }

            document.addEventListener('DOMContentLoaded', initMap);
        </script>
    @endpush
</x-app-layout>
