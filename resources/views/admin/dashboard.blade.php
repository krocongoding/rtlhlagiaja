<x-layouts.app title="Admin Command Center">

    <div class="pagehead">
        <div>
            <div class="kicker">
                ADMINISTRATOR WORKSPACE
            </div>
            <h1>Command Center RTLH</h1>
            <p class="muted">
                Monitoring status integrasi data survei lapangan, proses review verifikasi, dan kontrol publikasi portal.
            </p>
        </div>

        <div class="actions">
            <a class="btn primary" href="{{ route('admin.review.index') }}">
                <i class="fa-solid fa-clipboard-check"></i> Antrian Review
                @if($submitted > 0)
                    <span class="badge" style="background:#fef3c7; color:#b45309; padding:2px 6px; font-size:11px;">
                        {{ $submitted }}
                    </span>
                @endif
            </a>
            <a class="btn" href="{{ route('houses.create') }}">
                <i class="fa-solid fa-plus"></i> Tambah Data
            </a>
        </div>
    </div>

    {{-- METRIC CARDS --}}
    <div class="grid3">
        <div class="stat-widget">
            <div class="stat-widget-icon blue">
                <i class="fa-solid fa-folder-tree"></i>
            </div>
            <div class="stat-label">Total Keseluruhan</div>
            <div class="stat">{{ number_format($total) }}</div>
            <div class="stat-desc">Semua status unit rumah</div>
        </div>

        <div class="stat-widget" style="{{ $submitted > 0 ? 'border-color:#fde68a; background:#fffbeb;' : '' }}">
            <div class="stat-widget-icon amber">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div class="stat-label" style="{{ $submitted > 0 ? 'color:#b45309;' : '' }}">Menunggu Review</div>
            <div class="stat" style="{{ $submitted > 0 ? 'color:#b45309;' : '' }}">{{ number_format($submitted) }}</div>
            <div class="stat-desc">
                @if($submitted > 0)
                    <a href="{{ route('admin.review.index') }}" style="color:#b45309; font-weight:700; text-decoration:none;">
                        Buka antrian review <i class="fa-solid fa-arrow-right"></i>
                    </a>
                @else
                    Tidak ada antrian pending
                @endif
            </div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon" style="background:var(--slate-100); color:var(--slate-600);">
                <i class="fa-solid fa-file-pen"></i>
            </div>
            <div class="stat-label">Draft Surveyor</div>
            <div class="stat">{{ number_format($draft) }}</div>
            <div class="stat-desc">Sedang dilengkapi petugas</div>
        </div>
    </div>

    <div class="grid3" style="margin-top:20px;">
        <div class="stat-widget">
            <div class="stat-widget-icon purple">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="stat-label">Verified (Terverifikasi)</div>
            <div class="stat" style="color:#7c3aed;">{{ number_format($verified) }}</div>
            <div class="stat-desc">Siap untuk dipublikasikan ke portal</div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon emerald">
                <i class="fa-solid fa-globe"></i>
            </div>
            <div class="stat-label">Published (Publik)</div>
            <div class="stat" style="color:#059669;">{{ number_format($published) }}</div>
            <div class="stat-desc">Tampil di Peta GIS & Open Data</div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon rose">
                <i class="fa-solid fa-rotate-left"></i>
            </div>
            <div class="stat-label">Perlu Revisi</div>
            <div class="stat" style="color:#e11d48;">{{ number_format($revision) }}</div>
            <div class="stat-desc">Dikembalikan ke surveyor lapangan</div>
        </div>
    </div>

    {{-- QUICK ACTION HUB --}}
    <div class="card" style="margin-top: 24px; padding: 24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
            <div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-900); margin:0;">
                    <i class="fa-solid fa-bolt text-amber-500" style="color:#f59e0b;"></i> Pintasan Cepat Sistem
                </h3>
                <p class="muted" style="margin:2px 0 0 0; font-size:13px;">Akses cepat untuk memproses data, verifikasi, dan manajemen sistem.</p>
            </div>
        </div>

        <div class="actions" style="gap:10px;">
            <a class="btn primary" href="{{ route('admin.review.index') }}">
                <i class="fa-solid fa-clipboard-check"></i> Proses Review & Publikasi
            </a>

            <a class="btn" href="{{ route('houses.index') }}">
                <i class="fa-solid fa-list-check"></i> Kelola Semua Data RTLH
            </a>

            <a class="btn" href="{{ route('admin.surveyors.index') }}">
                <i class="fa-solid fa-users-gear"></i> Kelola Petugas Surveyor
            </a>

            <a class="btn" href="{{ route('regions.index') }}">
                <i class="fa-solid fa-map-pin"></i> Kelola Wilayah
            </a>

            <a class="btn" href="{{ route('surveyor.rtlh.index') }}">
                <i class="fa-solid fa-briefcase"></i> Mode Surveyor
            </a>

            <a class="btn" href="{{ route('public.home') }}" target="_blank">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Cek Portal Publik
            </a>
        </div>
    </div>

    {{-- INTERACTIVE VISUAL CHARTS --}}
    <div class="grid2" style="margin-top: 24px;">
        {{-- DONUT CHART: REALISASI ANGGARAN & BANTUAN --}}
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-900); margin:0;">
                        <i class="fa-solid fa-chart-pie text-emerald-600" style="color:#059669;"></i> Realisasi Bantuan RTLH
                    </h3>
                    <p class="muted" style="font-size:12px; margin-top:2px;">Rasio unit yang telah rampung mendapatkan realisasi bantuan.</p>
                </div>
                <span class="badge" style="background:var(--slate-100); color:var(--slate-700); font-weight:700;">
                    {{ number_format($total) }} Unit
                </span>
            </div>

            <div style="position: relative; width: 100%; height: 260px; display:flex; align-items:center; justify-content:center;">
                <canvas id="realizationDonutChart"></canvas>
            </div>

            <div style="display:flex; justify-content:center; gap:20px; margin-top:16px; font-size:12.5px; border-top:1px solid var(--slate-100); padding-top:14px;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="width:12px; height:12px; border-radius:50%; background:#10b981; display:inline-block;"></span>
                    <span style="color:var(--slate-700); font-weight:600;">Terealisasi: <strong>{{ number_format($realizedCount) }}</strong></span>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="width:12px; height:12px; border-radius:50%; background:#f59e0b; display:inline-block;"></span>
                    <span style="color:var(--slate-700); font-weight:600;">Belum Terealisasi: <strong>{{ number_format($notRealizedCount) }}</strong></span>
                </div>
            </div>
        </div>

        {{-- BAR CHART: SEBARAN TOP KELURAHAN --}}
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-900); margin:0;">
                        <i class="fa-solid fa-chart-column text-sky-600" style="color:#0284c7;"></i> Top Sebaran per Kelurahan
                    </h3>
                    <p class="muted" style="font-size:12px; margin-top:2px;">Kelurahan dengan akumulasi pendataan RTLH terbanyak.</p>
                </div>
                <a href="{{ route('public.statistics') }}" class="btn small" style="font-size:11px;">
                    Semua Wilayah &rarr;
                </a>
            </div>

            <div style="position: relative; width: 100%; height: 260px;">
                <canvas id="topRegionsChart"></canvas>
            </div>

            <div style="margin-top:16px; font-size:12px; color:var(--slate-500); text-align:center; border-top:1px solid var(--slate-100); padding-top:14px;">
                Data disinkronkan langsung dari basis data spasial aktif
            </div>
        </div>
    </div>

    {{-- DUA KOLOM: ANTRIAN VERIFIKASI MENDESAK & FEED AKTIVITAS TERKINI --}}
    <div class="grid2" style="margin-top: 24px;">
        {{-- KOLOM KIRI: ANTRIAN SUBMITTED TERLAMA --}}
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="font-size: 15px; font-weight: 800; color: var(--slate-900); margin:0;">
                        <i class="fa-solid fa-hourglass-half text-amber-500" style="color:#d97706;"></i> Antrian Review Mendesak
                    </h3>
                    <p class="muted" style="font-size: 11.5px; margin:2px 0 0 0;">Menunggu keputusan verifikasi Administrator</p>
                </div>
                <a href="{{ route('admin.review.index') }}" class="btn small primary" style="font-size:11.5px;">
                    Buka Antrian ({{ $submitted }})
                </a>
            </div>

            <div style="padding: 10px 16px;">
                @forelse($urgentReviews as $reviewHouse)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 6px; border-bottom:1px solid var(--slate-100); gap:12px;">
                        <div style="min-width:0;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <strong style="font-family:monospace; font-size:13px; color:var(--slate-900);">
                                    {{ $reviewHouse->house_code }}
                                </strong>
                                <span class="badge" style="background:#fef3c7; color:#b45309; font-size:10px; font-weight:700;">
                                    Menunggu
                                </span>
                            </div>
                            <div class="muted" style="font-size:12px; margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ $reviewHouse->head_of_household_name ?: 'Nama Belum Diisi' }} &bull; {{ $reviewHouse->region?->name ?? 'Cirebon' }}
                            </div>
                        </div>

                        <a href="{{ route('houses.show', $reviewHouse) }}" class="btn small" style="white-space:nowrap; font-size:11.5px;">
                            Periksa
                        </a>
                    </div>
                @empty
                    <div style="text-align:center; padding:36px 16px;" class="muted">
                        <i class="fa-solid fa-circle-check" style="font-size:32px; color:#10b981; margin-bottom:8px; opacity:0.8;"></i>
                        <p style="font-size:13px; margin:0;">Semua pengajuan telah diproses! Tidak ada antrian pending.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- KOLOM KANAN: AKTIVITAS & AUDIT LOG TERKINI --}}
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="font-size: 15px; font-weight: 800; color: var(--slate-900); margin:0;">
                        <i class="fa-solid fa-clock-rotate-left text-purple-600" style="color:#7c3aed;"></i> Aktivitas Sistem Terkini
                    </h3>
                    <p class="muted" style="font-size: 11.5px; margin:2px 0 0 0;">Riwayat tindakan audit dan operasi data terbaru</p>
                </div>
                <span class="badge" style="background:var(--slate-100); color:var(--slate-600); font-size:11px;">
                    Live Feed
                </span>
            </div>

            <div style="padding: 10px 16px;">
                @forelse($recentActivities as $log)
                    <div style="display:flex; align-items:flex-start; gap:12px; padding:12px 6px; border-bottom:1px solid var(--slate-100);">
                        <div style="width:34px; height:34px; border-radius:50%; background:#e0f2fe; color:#0284c7; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:12px; flex-shrink:0;">
                            {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:12.5px; color:var(--slate-800); line-height:1.4;">
                                <strong>{{ $log->user?->name ?? 'Sistem' }}</strong>
                                <span style="color:var(--slate-600);">melakukan aksi</span>
                                <code style="background:var(--slate-100); padding:1px 5px; border-radius:4px; font-size:11px; font-weight:700;">{{ $log->action }}</code>
                            </div>
                            <div class="muted" style="font-size:11px; margin-top:2px;">
                                <i class="fa-regular fa-clock"></i> {{ $log->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center; padding:36px 16px;" class="muted">
                        <i class="fa-solid fa-list-check" style="font-size:32px; color:var(--slate-400); margin-bottom:8px;"></i>
                        <p style="font-size:13px; margin:0;">Belum ada catatan aktivitas tercatat di sistem.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- CHART.JS SCRIPT INITIALIZATION --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Donut Chart Realisasi
            const donutCanvas = document.getElementById('realizationDonutChart');
            if (donutCanvas) {
                new Chart(donutCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Sudah Terealisasi', 'Belum Terealisasi'],
                        datasets: [{
                            data: [{{ $realizedCount }}, {{ $notRealizedCount }}],
                            backgroundColor: ['#10b981', '#f59e0b'],
                            hoverBackgroundColor: ['#059669', '#d97706'],
                            borderWidth: 2,
                            borderColor: '#ffffff',
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }

            // 2. Bar Chart Top Kelurahan
            const barCanvas = document.getElementById('topRegionsChart');
            const regionLabels = @json($topRegions->pluck('name'));
            const regionData = @json($topRegions->pluck('houses_count'));

            if (barCanvas && regionLabels.length > 0) {
                new Chart(barCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: regionLabels,
                        datasets: [{
                            label: 'Jumlah Unit RTLH',
                            data: regionData,
                            backgroundColor: '#0284c7',
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, font: { family: 'Plus Jakarta Sans' } },
                                grid: { color: 'rgba(226, 232, 240, 0.6)' }
                            },
                            x: {
                                ticks: { font: { family: 'Plus Jakarta Sans', size: 11, weight: '600' } },
                                grid: { display: false }
                            }
                        }
                    }
                });
            }
        });
    </script>

</x-layouts.app>