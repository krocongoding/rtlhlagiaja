<x-layouts.public title="Statistik & Analitik RTLH">

    <div class="pagehead">
        <div>
            <div class="kicker">
                <i class="fa-solid fa-chart-simple"></i> DASHBOARD ANALITIK
            </div>
            <h1>Statistik RTLH Kota Cirebon</h1>
            <p class="muted">
                Rekapitulasi data geospasial dan agregasi data rumah tidak layak huni terverifikasi.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('public.map') }}" class="btn">
                <i class="fa-solid fa-map-location-dot"></i> Buka Peta
            </a>
            <a href="{{ route('public.datasets') }}" class="btn">
                <i class="fa-solid fa-database"></i> Katalog Open Data
            </a>
            <a href="{{ route('api.public.statistics') }}" target="_blank" class="btn primary">
                <i class="fa-solid fa-code"></i> JSON API
            </a>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="grid4">
        <div class="stat-widget">
            <div class="stat-widget-icon emerald">
                <i class="fa-solid fa-house-circle-check"></i>
            </div>
            <div class="stat-label">Total RTLH Publik</div>
            <div class="stat">{{ number_format($total) }}</div>
            <div class="stat-desc">Data lolos verifikasi & publish</div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon blue">
                <i class="fa-solid fa-map"></i>
            </div>
            <div class="stat-label">Cakupan Kecamatan</div>
            <div class="stat">{{ number_format($districtCount) }}</div>
            <div class="stat-desc">Kecamatan administratif</div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon amber">
                <i class="fa-solid fa-city"></i>
            </div>
            <div class="stat-label">Cakupan Desa</div>
            <div class="stat">{{ number_format($villageCount) }}</div>
            <div class="stat-desc">Desa / kelurahan aktif</div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon purple">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <div class="stat-label">Rentang Periode</div>
            <div class="stat">{{ number_format($yearCount) }} <span style="font-size:16px; font-weight:600; color:var(--slate-500);">Tahun</span></div>
            <div class="stat-desc">Survei berkelanjutan</div>
        </div>
    </div>

    {{-- CHARTS SECTION --}}
    <div class="grid2" style="margin-top: 24px;">
        {{-- CHART TAHUN --}}
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                <div>
                    <h3 style="font-size:17px; font-weight:800; color:var(--slate-900); margin-bottom:4px;">
                        <i class="fa-solid fa-chart-column text-emerald-600" style="color:#059669;"></i> Tren RTLH per Tahun Survei
                    </h3>
                    <p class="muted" style="font-size:13px;">Jumlah unit RTLH terdata berdasarkan tahun pelaksanaan survei.</p>
                </div>
            </div>

            <div style="position: relative; width: 100%; height: 320px;">
                <canvas id="yearChart"></canvas>
            </div>
        </div>

        {{-- CHART KECAMATAN --}}
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                <div>
                    <h3 style="font-size:17px; font-weight:800; color:var(--slate-900); margin-bottom:4px;">
                        <i class="fa-solid fa-chart-bar text-sky-600" style="color:#0284c7;"></i> Sebaran Terbesar per Kecamatan
                    </h3>
                    <p class="muted" style="font-size:13px;">Distribusi unit RTLH pada wilayah kecamatan di Kota Cirebon.</p>
                </div>
            </div>

            <div style="position: relative; width: 100%; height: 320px;">
                <canvas id="districtChart"></canvas>
            </div>
        </div>
    </div>

    {{-- REKAP KECAMATAN TABLE --}}
    <div class="card" style="margin-top: 24px; padding: 0; overflow:hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-900); margin-bottom: 2px;">
                    <i class="fa-solid fa-building-columns text-emerald-600" style="color:#059669;"></i> Rekapitulasi per Kecamatan
                </h3>
                <p class="muted" style="font-size: 13px;">Ringkasan jumlah dan persentase unit RTLH per wilayah kecamatan.</p>
            </div>
            <span class="badge published" style="font-size: 12px;">
                {{ count($districts) }} Kecamatan Terdata
            </span>
        </div>

        <div class="tablewrap" style="border:none; border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">Peringkat</th>
                        <th>Nama Kecamatan</th>
                        <th>Kode Wilayah</th>
                        <th>Jumlah Unit RTLH</th>
                        <th>Distribusi Persentase</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $maxDistrictTotal = $districts->max('total') ?: 1;
                    @endphp
                    @forelse($districts as $index => $district)
                        @php
                            $percentage = $total > 0 ? round(($district->total / $total) * 100, 1) : 0;
                            $barWidth = round(($district->total / $maxDistrictTotal) * 100);
                        @endphp
                        <tr>
                            <td>
                                @if($index === 0)
                                    <span class="badge" style="background:#fef3c7; color:#b45309; font-weight:800;">🥇 1</span>
                                @elseif($index === 1)
                                    <span class="badge" style="background:#e2e8f0; color:#475569; font-weight:800;">🥈 2</span>
                                @elseif($index === 2)
                                    <span class="badge" style="background:#ffedd5; color:#9a3412; font-weight:800;">🥉 3</span>
                                @else
                                    <span style="font-weight:700; color:var(--slate-500); padding-left:8px;">#{{ $index + 1 }}</span>
                                @endif
                            </td>
                            <td>
                                <strong style="color:var(--slate-900);">{{ $district->district_name }}</strong>
                            </td>
                            <td>
                                <code style="background:var(--slate-100); padding:2px 6px; border-radius:4px; font-size:12px;">{{ $district->district_code }}</code>
                            </td>
                            <td>
                                <strong style="font-size:15px; color:var(--slate-900);">{{ number_format($district->total) }}</strong> <span class="muted">unit</span>
                            </td>
                            <td style="min-width: 180px;">
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="flex:1; height:8px; background:var(--slate-200); border-radius:999px; overflow:hidden;">
                                        <div style="width: {{ $barWidth }}%; height:100%; background:linear-gradient(90deg, #10b981 0%, #059669 100%); border-radius:999px;"></div>
                                    </div>
                                    <span style="font-size:12px; font-weight:700; color:var(--slate-700); min-width:42px;">{{ $percentage }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:32px;" class="muted">
                                Belum ada data publik per kecamatan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- REKAP DESA / KELURAHAN TABLE --}}
    <div class="card" style="margin-top: 24px; padding: 0; overflow:hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;">
            <div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-900); margin-bottom: 2px;">
                    Rekapitulasi per Desa / Kelurahan
                </h3>
                <p class="muted" style="font-size: 13px;">Gunakan filter untuk melihat data per kecamatan secara spesifik.</p>
            </div>

            <div class="toolbar" style="margin:0;">
                <select id="district-filter" style="min-width:220px;">
                    <option value="">Semua Kecamatan ({{ count($regionDistricts) }})</option>
                    @foreach($regionDistricts as $district)
                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                    @endforeach
                </select>

                <button type="button" class="btn small" onclick="resetVillageFilter()">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </button>
            </div>
        </div>

        <div class="tablewrap" style="border:none; border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">No</th>
                        <th>Desa / Kelurahan</th>
                        <th>Kecamatan</th>
                        <th>Kode Desa</th>
                        <th>Jumlah Unit RTLH</th>
                    </tr>
                </thead>
                <tbody id="village-table">
                    @forelse($villages as $village)
                        <tr data-district-id="{{ $village->district_id }}">
                            <td class="village-number" style="color:var(--slate-500); font-weight:600;">#</td>
                            <td>
                                <strong style="color:var(--slate-900);">{{ $village->village_name }}</strong>
                            </td>
                            <td>
                                <span class="badge" style="background:var(--slate-100); color:var(--slate-700);">
                                    <i class="fa-solid fa-location-dot"></i> {{ $village->district_name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <code style="background:var(--slate-100); padding:2px 6px; border-radius:4px; font-size:12px;">{{ $village->village_code }}</code>
                            </td>
                            <td>
                                <strong style="font-size:15px; color:var(--slate-900);">{{ number_format($village->total) }}</strong> <span class="muted">unit</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:32px;" class="muted">
                                Belum ada data publik per desa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        const yearLabels = @json($years->pluck('survey_year')->values());
        const yearValues = @json($years->pluck('total')->values());

        const districtLabels = @json($districts->take(12)->pluck('district_name')->values());
        const districtValues = @json($districts->take(12)->pluck('total')->values());

        // Year Chart
        const yearCanvas = document.getElementById('yearChart');
        if (yearCanvas && yearLabels.length > 0) {
            const ctxYear = yearCanvas.getContext('2d');
            const gradientYear = ctxYear.createLinearGradient(0, 0, 0, 300);
            gradientYear.addColorStop(0, 'rgba(16, 185, 129, 0.9)');
            gradientYear.addColorStop(1, 'rgba(5, 150, 105, 0.4)');

            new Chart(yearCanvas, {
                type: 'bar',
                data: {
                    labels: yearLabels,
                    datasets: [{
                        label: 'Jumlah RTLH',
                        data: yearValues,
                        backgroundColor: gradientYear,
                        borderColor: '#047857',
                        borderWidth: 1.5,
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 48
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: 'bold' },
                            bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: { precision: 0, font: { family: 'Plus Jakarta Sans' } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { font: { family: 'Plus Jakarta Sans', weight: '600' } }
                        }
                    }
                }
            });
        }

        // District Chart
        const districtCanvas = document.getElementById('districtChart');
        if (districtCanvas && districtLabels.length > 0) {
            const ctxDist = districtCanvas.getContext('2d');
            const gradientDist = ctxDist.createLinearGradient(0, 0, 400, 0);
            gradientDist.addColorStop(0, 'rgba(2, 132, 199, 0.4)');
            gradientDist.addColorStop(1, 'rgba(2, 132, 199, 0.9)');

            new Chart(districtCanvas, {
                type: 'bar',
                data: {
                    labels: districtLabels,
                    datasets: [{
                        label: 'Jumlah RTLH',
                        data: districtValues,
                        backgroundColor: gradientDist,
                        borderColor: '#0284c7',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        borderSkipped: false,
                        maxBarThickness: 24
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: 'bold' },
                            bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                            cornerRadius: 8
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#f1f5f9' },
                            ticks: { precision: 0, font: { family: 'Plus Jakarta Sans' } }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { font: { family: 'Plus Jakarta Sans', weight: '600' } }
                        }
                    }
                }
            });
        }

        // Filter Desa Table
        function filterVillages() {
            const selectedDistrict = document.getElementById('district-filter').value;
            const rows = document.querySelectorAll('#village-table tr[data-district-id]');
            let number = 1;

            rows.forEach(row => {
                const districtId = row.dataset.districtId;
                const visible = selectedDistrict === '' || districtId === selectedDistrict;

                if (visible) {
                    row.style.display = '';
                    const numberCell = row.querySelector('.village-number');
                    if (numberCell) numberCell.textContent = number++;
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function resetVillageFilter() {
            document.getElementById('district-filter').value = '';
            filterVillages();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const filter = document.getElementById('district-filter');
            if (filter) {
                filter.addEventListener('change', filterVillages);
            }
            filterVillages();
        });
    </script>

</x-layouts.public>