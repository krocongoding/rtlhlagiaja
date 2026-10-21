<x-layouts.app title="Surveyor Field Workspace">

    <div class="pagehead">
        <div>
            <div class="kicker">
                WORKSPACE SURVEYOR LAPANGAN
            </div>
            <h1>Field Workspace</h1>
            <p class="muted">
                Pendataan terstandar RTLH, input indikator fisik bangunan, titik GPS, dan pengiriman verifikasi ke Dinas.
            </p>
        </div>

        <div class="actions">
            <a class="btn primary large" href="{{ route('surveyor.rtlh.create') }}">
                <i class="fa-solid fa-plus"></i> Tambah Data RTLH Baru
            </a>
        </div>
    </div>

    {{-- ALERT REVISI JIKA ADA --}}
    @if($revision > 0)
        <div class="alert danger" style="border-left: 4px solid #e11d48;">
            <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
            <div>
                <strong>Perhatian!</strong> Ada <strong>{{ $revision }}</strong> data rumah yang perlu perbaikan/revisi sesuai catatan Admin verifikator.
                <a href="{{ route('surveyor.rtlh.index', ['status' => 'revision']) }}" style="color:#991b1b; font-weight:700; text-decoration:underline; margin-left:6px;">
                    Lihat Data Revisi →
                </a>
            </div>
        </div>
    @endif

    {{-- STATS WIDGETS --}}
    <div class="grid3">
        <div class="stat-widget">
            <div class="stat-widget-icon blue">
                <i class="fa-solid fa-folder-open"></i>
            </div>
            <div class="stat-label">Total Data Saya</div>
            <div class="stat">{{ number_format($mine) }}</div>
            <div class="stat-desc">Keseluruhan input saya</div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon" style="background:var(--slate-100); color:var(--slate-600);">
                <i class="fa-solid fa-file-pen"></i>
            </div>
            <div class="stat-label">Draft Disimpan</div>
            <div class="stat">{{ number_format($draft) }}</div>
            <div class="stat-desc">Belum dikirim ke review</div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon amber">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div class="stat-label">Menunggu Review</div>
            <div class="stat">{{ number_format($submitted) }}</div>
            <div class="stat-desc">Sedang ditinjau Admin</div>
        </div>
    </div>

    <div class="grid3" style="margin-top:20px;">
        <a href="{{ route('surveyor.rtlh.index', ['status' => 'revision']) }}" style="text-decoration:none; color:inherit; display:block;">
            <div class="stat-widget" style="{{ $revision > 0 ? 'border-color:#fecaca; background:#fff1f2;' : '' }}">
                <div class="stat-widget-icon rose">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <div class="stat-label" style="{{ $revision > 0 ? 'color:#be123c;' : '' }}">Perlu Revisi</div>
                <div class="stat" style="{{ $revision > 0 ? 'color:#be123c;' : '' }}">{{ number_format($revision) }}</div>
                <div class="stat-desc">Perbaikan indikator/foto</div>
            </div>
        </a>

        <div class="stat-widget">
            <div class="stat-widget-icon purple">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="stat-label">Verified</div>
            <div class="stat" style="color:#7c3aed;">{{ number_format($verified) }}</div>
            <div class="stat-desc">Telah disetujui verifikator</div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon emerald">
                <i class="fa-solid fa-globe"></i>
            </div>
            <div class="stat-label">Published</div>
            <div class="stat" style="color:#059669;">{{ number_format($published) }}</div>
            <div class="stat-desc">Tampil di portal publik</div>
        </div>
    </div>

    {{-- REALIZATION CHARTS SECTION SURVEYOR --}}
    <div class="grid2" style="margin-top: 24px;">
        {{-- 1. DONUT REALISASI SURVEI SAYA --}}
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-900); margin:0;">
                        <i class="fa-solid fa-chart-pie text-emerald-600" style="color:#059669;"></i> Realisasi Bantuan Data Saya
                    </h3>
                    <p class="muted" style="font-size:12px; margin-top:2px;">Perbandingan unit yang sudah dan belum menerima realisasi.</p>
                </div>
                <span class="badge" style="background:var(--slate-100); color:var(--slate-700); font-weight:700;">
                    {{ number_format($mine) }} Unit
                </span>
            </div>

            <div style="position: relative; width: 100%; height: 230px; display:flex; align-items:center; justify-content:center;">
                <canvas id="surveyorRealizationDonut"></canvas>
            </div>

            <div style="display:flex; justify-content:center; gap:20px; margin-top:14px; font-size:12.5px; border-top:1px solid var(--slate-100); padding-top:12px;">
                <a href="{{ route('houses.index', ['realization_status' => 'realized']) }}" style="text-decoration:none; display:flex; align-items:center; gap:6px;">
                    <span style="width:12px; height:12px; border-radius:50%; background:#10b981; display:inline-block;"></span>
                    <span style="color:#047857; font-weight:700;">Terealisasi: {{ number_format($realizedCount) }}</span>
                </a>
                <a href="{{ route('houses.index', ['realization_status' => 'not_realized']) }}" style="text-decoration:none; display:flex; align-items:center; gap:6px;">
                    <span style="width:12px; height:12px; border-radius:50%; background:#f59e0b; display:inline-block;"></span>
                    <span style="color:#b45309; font-weight:700;">Belum: {{ number_format($notRealizedCount) }}</span>
                </a>
            </div>
        </div>

        {{-- 2. BAR CHART: REALISASI PER KELURAHAN SAYA --}}
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-900); margin:0;">
                        <i class="fa-solid fa-chart-column text-sky-600" style="color:#0284c7;"></i> Sebaran Realisasi per Kelurahan
                    </h3>
                    <p class="muted" style="font-size:12px; margin-top:2px;">Realisasi unit yang Anda survei di tiap kelurahan.</p>
                </div>
                <a href="{{ route('houses.index') }}" class="btn small" style="font-size:11px;">
                    Semua Data &rarr;
                </a>
            </div>

            <div style="position: relative; width: 100%; height: 230px;">
                <canvas id="surveyorRegionChart"></canvas>
            </div>

            <div style="display:flex; justify-content:center; gap:16px; margin-top:14px; font-size:11.5px; border-top:1px solid var(--slate-100); padding-top:12px;">
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="width:10px; height:10px; border-radius:3px; background:#10b981; display:inline-block;"></span>
                    <span style="color:var(--slate-600); font-weight:600;">Sudah Terealisasi</span>
                </div>
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="width:10px; height:10px; border-radius:3px; background:#f59e0b; display:inline-block;"></span>
                    <span style="color:var(--slate-600); font-weight:600;">Belum Terealisasi</span>
                </div>
            </div>
        </div>
    </div>

    {{-- LATEST DRAFT QUICK RESUME --}}
    @if($latestDraft)
        <div class="card" style="margin-top: 24px; padding: 20px 24px; background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border: 1.5px solid #86efac; border-radius: var(--radius-xl);">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px;">
                <div style="display:flex; align-items:center; gap:16px;">
                    <div style="width:48px; height:48px; border-radius:14px; background:#10b981; color:#fff; display:flex; align-items:center; justify-content:center; font-size:20px; box-shadow:0 4px 10px rgba(16, 185, 129, 0.3);">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                    <div>
                        <div style="font-size:12px; font-weight:800; color:#047857; text-transform:uppercase; letter-spacing:0.5px;">
                            Lanjutkan Pekerjaan Terakhir
                        </div>
                        <h3 style="font-size:17px; font-weight:800; color:#064e3b; margin:2px 0;">
                            {{ $latestDraft->house_code }} &bull; {{ $latestDraft->head_of_household_name ?: 'Calon Penerima Belum Dipilih' }}
                        </h3>
                        <p class="muted" style="margin:0; font-size:12.5px; color:#065f46;">
                            Kelurahan {{ $latestDraft->region?->name ?? '-' }} &bull; Terakhir disimpan {{ $latestDraft->updated_at->diffForHumans() }}
                        </p>
                    </div>
                </div>

                <div style="display:flex; gap:10px;">
                    <a href="{{ route('houses.edit', $latestDraft) }}" class="btn primary" style="box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);">
                        <i class="fa-solid fa-arrow-right"></i> Lanjutkan Isi Form
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- DUA KOLOM: DATA TERBARU SAYA & PANDUAN SOP --}}
    <div class="grid2" style="margin-top: 24px;">
        {{-- DAFTAR INPUT SAYA TERBARU --}}
        <div class="card" style="padding:0; overflow:hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="font-size: 15px; font-weight: 800; color: var(--slate-900); margin:0;">
                        <i class="fa-solid fa-clock-rotate-left text-emerald-600" style="color:#059669;"></i> Survei Terakhir Saya
                    </h3>
                    <p class="muted" style="font-size: 11.5px; margin:2px 0 0 0;">5 data survei yang paling baru Anda perbarui</p>
                </div>
                <a href="{{ route('surveyor.rtlh.index') }}" class="btn small" style="font-size:11.5px;">
                    Lihat Semua
                </a>
            </div>

            <div style="padding: 6px 16px;">
                @forelse($recentSubmissions as $item)
                    <div style="display:flex; justify-content:space-between; align-items:center; padding:12px 4px; border-bottom:1px solid var(--slate-100); gap:12px;">
                        <div style="min-width:0;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <strong style="font-family:monospace; font-size:13px; color:var(--slate-900);">
                                    {{ $item->house_code }}
                                </strong>
                                @if($item->status === 'published')
                                    <span class="badge published" style="font-size:10px;">Published</span>
                                @elseif($item->status === 'verified')
                                    <span class="badge" style="background:#ede9fe; color:#6d28d9; font-size:10px;">Verified</span>
                                @elseif($item->status === 'submitted')
                                    <span class="badge submitted" style="font-size:10px;">Review</span>
                                @elseif($item->status === 'revision')
                                    <span class="badge" style="background:#fee2e2; color:#b91c1c; font-size:10px;">Revisi</span>
                                @else
                                    <span class="badge draft" style="font-size:10px;">Draft</span>
                                @endif
                            </div>
                            <div class="muted" style="font-size:12px; margin-top:2px;">
                                {{ $item->head_of_household_name ?: 'Nama Belum Diisi' }} &bull; {{ $item->region?->name ?? 'Cirebon' }}
                            </div>
                        </div>

                        <a href="{{ route('houses.show', $item) }}" class="btn small" style="white-space:nowrap; font-size:11px;">
                            Detail
                        </a>
                    </div>
                @empty
                    <div style="text-align:center; padding:32px 16px;" class="muted">
                        <i class="fa-solid fa-folder-open" style="font-size:28px; opacity:0.6; margin-bottom:6px;"></i>
                        <p style="font-size:12.5px; margin:0;">Belum ada data survei yang diinput.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- SOP & PANDUAN TEKNIS --}}
        <div class="card" style="background: var(--slate-50); border: 1px solid var(--slate-200);">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <div style="width:36px; height:36px; border-radius:10px; background:#e0f2fe; color:#0284c7; display:flex; align-items:center; justify-content:center; font-size:16px;">
                    <i class="fa-solid fa-compass-drafting"></i>
                </div>
                <div>
                    <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-900); margin:0;">
                        Standar Operasional Survei (SOP)
                    </h3>
                    <p class="muted" style="font-size:12px; margin:0;">Petunjuk teknis verifikasi kondisi fisik bangunan</p>
                </div>
            </div>

            <ul style="padding-left: 20px; font-size: 13px; color: var(--slate-700); line-height: 1.8; margin-bottom: 20px;">
                <li><strong>GPS Presisi:</strong> Pastikan berdiri di depan bangunan saat mencatat koordinat agar akurasi satelit tinggi (&plusmn;5-10 meter).</li>
                <li><strong>Foto Wajib:</strong> Lampirkan minimal foto tampak depan, kondisi atap/plafon, dinding, serta fasilitas jamban/sanitasi.</li>
                <li><strong>Verifikasi Dokumen:</strong> Cek kelengkapan NIK, Kartu Keluarga, dan kejelasan status kepemilikan tanah.</li>
                <li><strong>Simpan Bertahap:</strong> Gunakan tombol <em>Simpan Draft</em> jika masih mengumpulkan berkas sebelum mengirim ke antrian review.</li>
            </ul>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a class="btn primary small" href="{{ route('surveyor.rtlh.create') }}">
                    <i class="fa-solid fa-circle-plus"></i> Input RTLH Baru
                </a>
                <a class="btn small" href="{{ route('surveyor.rtlh.index') }}">
                    <i class="fa-solid fa-table-list"></i> Basis Data Saya
                </a>
            </div>
        </div>
    </div>

    {{-- CHART.JS SCRIPT INITIALIZATION --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Donut Realisasi
            const donutEl = document.getElementById('surveyorRealizationDonut');
            if (donutEl) {
                new Chart(donutEl.getContext('2d'), {
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

            // 2. Bar Chart Realisasi per Kelurahan
            const barEl = document.getElementById('surveyorRegionChart');
            const regionLabels = @json($regionStats->pluck('name'));
            const realizedData = @json($regionStats->pluck('realized_houses'));
            const notRealizedData = @json($regionStats->pluck('not_realized_houses'));

            if (barEl && regionLabels.length > 0) {
                new Chart(barEl.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: regionLabels,
                        datasets: [
                            {
                                label: 'Sudah Terealisasi',
                                data: realizedData,
                                backgroundColor: '#10b981',
                                borderRadius: 4,
                            },
                            {
                                label: 'Belum Terealisasi',
                                data: notRealizedData,
                                backgroundColor: '#f59e0b',
                                borderRadius: 4,
                            }
                        ]
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