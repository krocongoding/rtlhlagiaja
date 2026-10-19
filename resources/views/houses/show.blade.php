<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:16px; width:100%;">
            <div>
                <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; flex-wrap:wrap;">
                    @if($house->realization_status === 'realized')
                        <span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #86efac; font-weight:700;">
                            Sudah Terealisasi 
                        </span>
                    @else
                        <span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde047; font-weight:700;">
                            Belum Terealisasi 
                        </span>
                    @endif

                    @if($house->status === 'published')
                        <span class="badge published">Published</span>
                    @elseif($house->status === 'verified')
                        <span class="badge" style="background:#ede9fe; color:#6d28d9;">Verified</span>
                    @elseif($house->status === 'submitted')
                        <span class="badge submitted">Submitted</span>
                    @elseif($house->status === 'revision')
                        <span class="badge revision">Perlu Revisi</span>
                    @else
                        <span class="badge draft">{{ ucfirst($house->status) }}</span>
                    @endif

                    @php
                        $latestAssessment = $house->assessments
                            ->sortByDesc('assessment_year')
                            ->sortByDesc('id')
                            ->first() ?? $house->latestAssessment;
                    @endphp

                    @if($latestAssessment?->priority_level)
                        @php
                            $pri = strtolower($latestAssessment->priority_level);
                            $badgeClass = ($pri === 'tinggi' || $pri === 'berat') ? 'badge priority-high' : (($pri === 'sedang') ? 'badge priority-medium' : 'badge priority-low');
                        @endphp
                        <span class="{{ $badgeClass }}">
                            Prioritas {{ ucfirst($latestAssessment->priority_level) }}
                        </span>
                    @endif
                </div>

                <h1 style="font-size:28px; font-weight:800; color:var(--slate-900); margin:0;">
                    {{ $house->house_code }}
                </h1>

                <p class="muted" style="margin-top:4px; font-size:14px;">
                    {{ $house->region?->name ?? 'Kabupaten Cirebon' }} &bull; Tahun Survei {{ $house->survey_year }}
                </p>
            </div>

            {{-- ACTION HUB --}}
            <div class="actions" style="margin:0;">
                @can('update', $house)
                    <a href="{{ route('houses.edit', $house) }}" class="btn small" title="Edit Data Pokok">
                        Edit Pokok
                    </a>
                    <a href="{{ route('houses.condition.edit', $house) }}" class="btn small" title="Edit Kondisi Fisik">
                        Kondisi Fisik
                    </a>
                    <a href="{{ route('houses.sanitation-utility.edit', $house) }}" class="btn small" title="Edit Sanitasi">
                        Sanitasi
                    </a>
                    <a href="{{ route('houses.occupants.edit', $house) }}" class="btn small" title="Kelola Penghuni">
                        Penghuni ({{ $house->occupants->count() }})
                    </a>
                    <a href="{{ route('houses.assessment.edit', $house) }}" class="btn small" title="Edit Assessment">
                        Assessment
                    </a>
                @endcan

                @can('delete', $house)
                    <form method="POST" action="{{ route('houses.destroy', $house) }}" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data RTLH ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn small danger">
                            Hapus
                        </button>
                    </form>
                @endcan

                <a href="{{ (isset($panel) && $panel === 'surveyor') ? route('surveyor.rtlh.index') : route('houses.index') }}" class="btn small">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    @push('head')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <style>
            #mini-map {
                z-index: 1;
            }
            .leaflet-popup-content-wrapper {
                border-radius: 8px;
            }
        </style>
    @endpush

    {{-- FLASH SUCCESS MESSAGE --}}
    @if (session('success') || session('ok'))
        <div class="alert ok" style="margin-bottom: 24px;">
            <div>{{ session('success') ?? session('ok') }}</div>
        </div>
    @endif

    {{-- PERINGATAN REVISI / CATATAN ADMIN --}}
    @if($house->revision_notes)
        <div class="alert danger" style="margin-bottom: 24px; border-left: 5px solid #e11d48; background: #fff1f2; border-color: #fecaca; border-radius: var(--radius-lg); padding: 18px 20px;">
            <div style="width:100%;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:8px;">
                    <div>
                        <h4 style="font-size:15px; font-weight:800; color:#9f1239; margin:0 0 2px 0;">
                            {{ $house->status === 'revision' ? 'Catatan Revisi dari Admin / Verifikator' : 'Riwayat Catatan Revisi Sebelumnya' }}
                        </h4>
                        <p class="muted" style="font-size:12px; margin:0; color:#be123c;">
                            @if($house->revised_at)
                                Diberikan pada {{ $house->revised_at->translatedFormat('d F Y, H:i') }} WIB
                            @endif
                            @if($house->reviser)
                                oleh <strong>{{ $house->reviser->name }}</strong> (Admin)
                            @endif
                        </p>
                    </div>

                    @if($house->status === 'revision')
                        <span class="badge" style="background:#e11d48; color:#fff; font-weight:700; font-size:11px;">
                            Status: Perlu Revisi
                        </span>
                    @else
                        <span class="badge" style="background:#fef3c7; color:#92400e; font-weight:700; font-size:11px;">
                            Catatan Sebelumnya
                        </span>
                    @endif
                </div>

                <div style="margin-top:12px; padding:12px 16px; background:#ffffff; border-radius:var(--radius-md); border:1px solid #fed7aa; color:#881337; font-size:13.5px; line-height:1.6; white-space:pre-wrap; font-weight:500;">{{ $house->revision_notes }}</div>

                @if($house->status === 'revision')
                    @can('update', $house)
                        <div style="margin-top:14px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                            <a href="{{ route('houses.edit', $house) }}" class="btn small danger" style="background:#e11d48; border-color:#e11d48; color:#fff;">
                                Mulai Perbaiki Data Sekarang
                            </a>
                            <span class="muted" style="font-size:12px; color:#be123c;">Setelah diperbaiki, kirim ulang data untuk diverifikasi kembali.</span>
                        </div>
                    @endcan
                @endif
            </div>
        </div>
    @endif

    {{-- ADMIN REVIEW ACTION BAR --}}
    @if(auth()->check() && auth()->user()->hasRole('admin') && in_array($house->status, ['submitted', 'verified']))
        <div class="card" style="margin-bottom: 24px; padding:16px 20px; background:linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-left:4px solid var(--primary-600); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h4 style="font-size:14px; font-weight:800; color:var(--slate-900); margin:0;">Aksi Review Verifikator</h4>
                <p class="muted" style="font-size:12px; margin:2px 0 0 0;">Status saat ini: <strong>{{ ucfirst($house->status) }}</strong>. Tinjau kelengkapan data lalu tentukan keputusan.</p>
            </div>

            <div style="display:flex; gap:8px; align-items:center;">
                @if($house->status === 'submitted')
                    <form method="POST" action="{{ route('admin.review.verify', $house) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn small primary" title="Verifikasi Data">
                            Verifikasi Data
                        </button>
                    </form>
                @elseif($house->status === 'verified')
                    <form method="POST" action="{{ route('admin.review.publish', $house) }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn small" style="background:#059669; color:#fff; border-color:#059669;" title="Terbitkan ke Peta Publik">
                            Publikasikan
                        </button>
                    </form>
                @endif

                <button type="button" class="btn small danger" onclick="openRevisionModal('{{ route('admin.review.revision', $house) }}', '{{ $house->house_code }}', @js($house->revision_notes ?? ''))">
                    Minta Revisi
                </button>
            </div>
        </div>
    @endif

    {{-- QUICK SPECS KPI --}}
    <div class="grid4" style="margin-bottom: 24px;">
        <div class="stat-widget">
            <div class="stat-label">Luas Bangunan</div>
            <div class="stat" style="font-size: 24px;">
                {{ $house->area_m2 ? number_format((float) $house->area_m2, 1, ',', '.') : '-' }} <span style="font-size:15px; font-weight:600; color:var(--slate-500);">m²</span>
            </div>
            <div class="stat-desc">Luas lantai hunian</div>
        </div>

        <div class="stat-widget">
            <div class="stat-label">Penghuni & KK</div>
            <div class="stat" style="font-size: 24px;">
                {{ $house->occupant_count ?? $house->occupants->count() }} <span style="font-size:15px; font-weight:600; color:var(--slate-500);">Jiwa</span>
            </div>
            <div class="stat-desc">{{ $house->household_count ?: 1 }} Kepala Keluarga</div>
        </div>

        <div class="stat-widget">
            <div class="stat-label">Skor Penilaian</div>
            <div class="stat" style="font-size: 24px; color: var(--primary-700);">
                {{ $latestAssessment?->score ?? 'N/A' }}
            </div>
            <div class="stat-desc">Prioritas: {{ ucfirst($latestAssessment?->priority_level ?? '-') }}</div>
        </div>

        <div class="stat-widget">
            <div class="stat-label">Status Alur Data</div>
            <div class="stat" style="font-size: 20px; color: #059669;">
                {{ ucfirst($house->status) }}
            </div>
            <div class="stat-desc">{{ $house->is_public ? 'Publik (Peta Aktif)' : 'Data Internal / Draft' }}</div>
        </div>
    </div>

    {{-- GRID 2 COLUMNS: IDENTITAS & MINI-MAP --}}
    <div class="grid2" style="margin-bottom: 24px;">
        {{-- INFORMASI POKOK --}}
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                    Identitas & Wilayah Rumah
                </h3>
                @can('update', $house)
                    <a href="{{ route('houses.edit', $house) }}" class="btn small" style="padding:4px 8px; font-size:11px;">
                        Edit
                    </a>
                @endcan
            </div>

            <table style="font-size:13px;">
                <tbody>
                    <tr>
                        <td style="width:160px; color:var(--slate-500); font-weight:600;">Kode RTLH</td>
                        <td><strong style="color:var(--slate-900); font-family:monospace;">{{ $house->house_code }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">NIK Kepala Keluarga</td>
                        <td><strong style="color:var(--slate-900); font-family:monospace;">{{ $house->nik ?: '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">Nama Penerima (CPBM)</td>
                        <td><strong style="color:var(--slate-900);">{{ $house->head_of_household_name ?: '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">Wilayah (Desa)</td>
                        <td><span class="badge" style="background:var(--slate-100);">{{ $house->region?->name ?? '-' }}</span></td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">Alamat Lengkap</td>
                        <td>{{ $house->address ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">Dusun / Blok</td>
                        <td>{{ $house->block ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">RT / RW</td>
                        <td>RT {{ $house->rt ?: '-' }} / RW {{ $house->rw ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">Tahun Survei</td>
                        <td>{{ $house->survey_year }}</td>
                    </tr>
                    <tr>
                        <td style="color:var(--slate-500); font-weight:600;">Status Kepemilikan</td>
                        <td>
                            <div>Rumah: <strong>{{ $house->ownershipStatus?->label ?? 'Milik Sendiri' }}</strong></div>
                            <div style="font-size:12px; color:var(--slate-600); margin-top:2px;">Tanah: <strong>{{ $house->landStatus?->label ?? '-' }}</strong></div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- GEOSPASIAL & MINI MAP --}}
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid var(--slate-100); padding-bottom:12px; flex-wrap:wrap; gap:8px;">
                <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                    Lokasi Geospasial
                </h3>

                @if($house->latitude !== null && $house->longitude !== null)
                    <a href="https://www.google.com/maps?q={{ $house->latitude }},{{ $house->longitude }}" target="_blank" rel="noopener noreferrer" class="btn small" style="font-size:11px; padding:4px 10px;" title="Buka lokasi di Google Maps">
                        Buka Google Maps
                    </a>
                @endif
            </div>

            @if($house->latitude !== null && $house->longitude !== null)
                <div id="mini-map" style="height:230px; width:100%; border-radius:var(--radius-md); border:1px solid var(--slate-200); margin-bottom:14px;"></div>

                <div style="display:flex; justify-content:space-between; gap:12px; background:var(--slate-50); padding:10px 14px; border-radius:var(--radius-md); font-size:12px;">
                    <div>
                        <span class="muted">Latitude:</span>
                        <code style="font-weight:700; color:var(--slate-800);">{{ $house->latitude }}</code>
                    </div>
                    <div>
                        <span class="muted">Longitude:</span>
                        <code style="font-weight:700; color:var(--slate-800);">{{ $house->longitude }}</code>
                    </div>
                </div>
            @else
                <div style="padding:40px 24px; text-align:center; background:var(--slate-50); border-radius:var(--radius-md); border:1px dashed var(--slate-300);" class="muted">
                    <p style="font-size:13px; color:var(--slate-700); margin:0 0 12px 0;">Titik koordinat GPS rumah belum diinput.</p>
                    @can('update', $house)
                        <a href="{{ route('houses.edit', $house) }}" class="btn small primary">
                            Input Titik Koordinat GPS
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>

    {{-- CARD ANGGARAN, REALISASI & PROPOSAL USULAN --}}
    <div class="card" style="margin-bottom: 24px; border-left: 4px solid {{ $house->realization_status === 'realized' ? '#10b981' : '#f59e0b' }};">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid var(--slate-100); padding-bottom:12px; flex-wrap:wrap; gap:10px;">
            <div>
                <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                    Sumber Anggaran, Realisasi & Berkas Proposal Usulan
                </h3>
                <p class="muted" style="margin:2px 0 0 0; font-size:12px;">Informasi pendanaan penanganan perbaikan dan administrasi usulan desa.</p>
            </div>

            <div style="display:flex; align-items:center; gap:8px;">
                @if($house->realization_status === 'realized')
                    <span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #86efac; font-weight:700; font-size:12px; padding:6px 12px;">
                        Sudah Terealisasi 
                    </span>
                    <button type="button" class="btn small" onclick="openRealizationModal()" style="font-size:12px; padding:6px 12px;" title="Ubah atau tinjau data realisasi">
                        Kelola Realisasi
                    </button>
                @else
                    <span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde047; font-weight:700; font-size:12px; padding:6px 12px;">
                        Belum Terealisasi 
                    </span>
                    <button type="button" class="btn small" style="background:#059669; color:#fff; border-color:#059669; font-size:12px; padding:6px 14px; font-weight:700;" onclick="openRealizationModal()" title="Tandai bantuan RTLH ini telah selesai direalisasikan">
                        Tandai Sudah Terealisasi
                    </button>
                @endif
            </div>
        </div>

        @if($house->realization_status === 'realized')
            <div style="margin-bottom:16px; padding:12px 16px; background:#f0fdf4; border-radius:var(--radius-md); border:1px solid #bbf7d0; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <div style="font-size:13px; font-weight:800; color:#166534;">
                        Bantuan RTLH Telah Selesai Direalisasikan
                    </div>
                    <div class="muted" style="font-size:11.5px; color:#15803d;">
                        @if($house->realized_at)
                            Tanggal Realisasi: <strong>{{ $house->realized_at->translatedFormat('d F Y') }}</strong>
                        @endif
                        @if($house->realizer)
                            &bull; Diverifikasi oleh: <strong>{{ $house->realizer->name }}</strong>
                        @endif
                    </div>
                </div>

                @if($house->realization_notes)
                    <div style="width:100%; font-size:12.5px; color:#14532d; background:#ffffff; padding:8px 12px; border-radius:6px; border:1px solid #dcfce7; margin-top:4px;">
                        <strong>Catatan Penanganan:</strong> {{ $house->realization_notes }}
                    </div>
                @endif
            </div>
        @endif

        <div class="grid2" style="margin-bottom: 16px;">
            <div style="background:var(--slate-50); padding:14px; border-radius:var(--radius-md); border:1px solid var(--slate-200);">
                <span class="muted" style="font-size:11px; font-weight:700; text-transform:uppercase;">Sumber Anggaran</span>
                <div style="font-size:16px; font-weight:800; color:var(--slate-900); margin-top:4px;">
                    {{ $house->funding_source ?: 'Belum ditentukan' }}
                </div>
            </div>

            <div style="background:var(--slate-50); padding:14px; border-radius:var(--radius-md); border:1px solid var(--slate-200);">
                <span class="muted" style="font-size:11px; font-weight:700; text-transform:uppercase;">Nilai Usulan / Anggaran</span>
                <div style="font-size:18px; font-weight:900; color:#15803d; margin-top:4px;">
                    @if($house->budget_amount)
                        Rp {{ number_format((float)$house->budget_amount, 0, ',', '.') }}
                    @else
                        <span class="muted" style="font-size:14px; font-weight:600;">Belum dianggarkan</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- GRID 4 DOKUMEN TERPISAH: KK, AKTE, SURAT TANAH, PROPOSAL --}}
        <div style="margin-top: 8px;">
            <div style="font-size:12px; font-weight:800; text-transform:uppercase; color:var(--slate-700); letter-spacing:0.5px; margin-bottom:10px;">
                Berkas Dokumen Administrasi & Kepemilikan (Terpisah)
            </div>

            <div class="grid4" style="gap:14px;">
                {{-- KK --}}
                <div style="background:var(--slate-50); padding:12px 14px; border-radius:var(--radius-md); border:1px solid var(--slate-200);">
                    <span class="muted" style="font-size:11px; font-weight:700; text-transform:uppercase; display:block;">1. Kartu Keluarga (KK)</span>
                    <div style="margin-top:8px;">
                        @if($house->kk_document_path)
                            <a href="{{ asset('storage/' . $house->kk_document_path) }}" target="_blank" class="btn small primary" style="font-size:11px; padding:3px 10px; width:100%; text-align:center;">
                                Buka Dokumen KK
                            </a>
                        @else
                            <span class="muted" style="font-size:12px;">Belum diunggah</span>
                        @endif
                    </div>
                </div>

                {{-- AKTE --}}
                <div style="background:var(--slate-50); padding:12px 14px; border-radius:var(--radius-md); border:1px solid var(--slate-200);">
                    <span class="muted" style="font-size:11px; font-weight:700; text-transform:uppercase; display:block;">2. Akte / Akta</span>
                    <div style="margin-top:8px;">
                        @if($house->akte_document_path)
                            <a href="{{ asset('storage/' . $house->akte_document_path) }}" target="_blank" class="btn small primary" style="font-size:11px; padding:3px 10px; width:100%; text-align:center;">
                                Buka Dokumen Akte
                            </a>
                        @else
                            <span class="muted" style="font-size:12px;">Belum diunggah</span>
                        @endif
                    </div>
                </div>

                {{-- SURAT TANAH --}}
                <div style="background:var(--slate-50); padding:12px 14px; border-radius:var(--radius-md); border:1px solid var(--slate-200);">
                    <span class="muted" style="font-size:11px; font-weight:700; text-transform:uppercase; display:block;">3. Surat Tanah</span>
                    <div style="margin-top:8px;">
                        @if($house->land_document_path)
                            <a href="{{ asset('storage/' . $house->land_document_path) }}" target="_blank" class="btn small primary" style="font-size:11px; padding:3px 10px; width:100%; text-align:center;">
                                Buka Surat Tanah
                            </a>
                        @else
                            <span class="muted" style="font-size:12px;">Belum diunggah</span>
                        @endif
                    </div>
                </div>

                {{-- PROPOSAL --}}
                <div style="background:var(--slate-50); padding:12px 14px; border-radius:var(--radius-md); border:1px solid var(--slate-200);">
                    <span class="muted" style="font-size:11px; font-weight:700; text-transform:uppercase; display:block;">4. Proposal Usulan</span>
                    <div style="margin-top:8px;">
                        @if($house->proposal_document_path)
                            <a href="{{ asset('storage/' . $house->proposal_document_path) }}" target="_blank" class="btn small primary" style="font-size:11px; padding:3px 10px; width:100%; text-align:center;">
                                Buka Proposal
                            </a>
                        @else
                            <span class="muted" style="font-size:12px;">Belum diunggah</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($house->proposal_notes)
            <div style="margin-top:14px; padding:10px 14px; background:#f8fafc; border-radius:8px; border:1px solid var(--slate-200); font-size:13px;">
                <strong style="color:var(--slate-800);">Catatan Usulan:</strong>
                <span style="color:var(--slate-600); margin-left:6px;">{{ $house->proposal_notes }}</span>
            </div>
        @endif
    </div>

    {{-- KONDISI FISIK, SANITASI, UTILITAS --}}
    <div class="grid3" style="margin-bottom: 24px;">
        {{-- STRUKTUR --}}
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid var(--slate-100); padding-bottom:8px;">
                <h4 style="font-size:15px; font-weight:800; color:var(--slate-900); margin:0;">
                    Struktur Utama Bangunan
                </h4>
                @can('update', $house)
                    <a href="{{ route('houses.condition.edit', $house) }}" class="btn small" style="padding:2px 8px; font-size:11px;">Edit</a>
                @endcan
            </div>

            <div style="display:flex; flex-direction:column; gap:8px; font-size:13px;">
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-100); padding-bottom:4px;">
                    <span class="muted">Pondasi:</span>
                    <strong>{{ $house->structure?->foundation ?: '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-100); padding-bottom:4px;">
                    <span class="muted">Sloof:</span>
                    <strong>{{ $house->structure?->sloofCondition?->label ?: '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-100); padding-bottom:4px;">
                    <span class="muted">Kondisi Tiang:</span>
                    <strong style="color:var(--primary-700);">{{ $house->structure?->columnCondition?->label ?: '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-100); padding-bottom:4px;">
                    <span class="muted">Balok:</span>
                    <strong>{{ $house->structure?->beamCondition?->label ?: '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="muted">Rangka Atap:</span>
                    <span>
                        <strong>{{ $house->roof?->frameMaterial?->label ?: 'Kayu' }}</strong>
                        <span class="muted">({{ $house->roof?->frameCondition?->label ?: '-' }})</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- KOMPONEN RUMAH --}}
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid var(--slate-100); padding-bottom:8px;">
                <h4 style="font-size:15px; font-weight:800; color:var(--slate-900); margin:0;">
                    Komponen Fisik
                </h4>
                @can('update', $house)
                    <a href="{{ route('houses.condition.edit', $house) }}" class="btn small" style="padding:2px 8px; font-size:11px;">Edit</a>
                @endcan
            </div>

            <div style="display:flex; flex-direction:column; gap:8px; font-size:13px;">
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-100); padding-bottom:4px;">
                    <span class="muted">Lantai:</span>
                    <span>{{ $house->floor?->material?->label ?: '-' }} ({{ $house->floor?->condition?->label ?: '-' }})</span>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-100); padding-bottom:4px;">
                    <span class="muted">Dinding:</span>
                    <span>{{ $house->wall?->material?->label ?: '-' }} ({{ $house->wall?->condition?->label ?: '-' }})</span>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-100); padding-bottom:4px;">
                    <span class="muted">Penutup Atap:</span>
                    <span>{{ $house->roof?->material?->label ?: '-' }} ({{ $house->roof?->condition?->label ?: '-' }})</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="muted">Plafon:</span>
                    <span>{{ $house->ceiling?->condition?->label ?: '-' }}</span>
                </div>
            </div>
        </div>

        {{-- SANITASI & UTILITAS --}}
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid var(--slate-100); padding-bottom:8px;">
                <h4 style="font-size:15px; font-weight:800; color:var(--slate-900); margin:0;">
                    Sanitasi & Utilitas
                </h4>
                @can('update', $house)
                    <a href="{{ route('houses.sanitation-utility.edit', $house) }}" class="btn small" style="padding:2px 8px; font-size:11px;">Edit</a>
                @endcan
            </div>

            <div style="display:flex; flex-direction:column; gap:8px; font-size:13px;">
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-100); padding-bottom:4px;">
                    <span class="muted">Sumber Air:</span>
                    <strong>{{ $house->sanitation?->waterSource?->label ?: '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-100); padding-bottom:4px;">
                    <span class="muted">Sanitasi Air Kotor:</span>
                    <strong style="color:#d97706;">{{ $house->sanitation?->wastewaterDisposal?->label ?: '-' }}</strong>
                </div>
                <div style="display:flex; justify-content:space-between; border-bottom:1px dashed var(--slate-100); padding-bottom:4px;">
                    <span class="muted">Jamban:</span>
                    <strong>{{ $house->sanitation?->toilet_available ? 'Ada' : 'Tidak Punya' }} ({{ $house->sanitation?->toiletType?->label ?: '-' }})</strong>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="muted">Penerangan:</span>
                    <strong>{{ $house->utility?->lightingSource?->label ?: '-' }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- FOTO DOKUMENTASI FISIK --}}
    @if($house->photos && $house->photos->isNotEmpty())
        <div class="card" style="margin-bottom: 24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                    Dokumentasi Foto Lapangan
                </h3>
                <span class="badge" style="background:var(--slate-100);">{{ $house->photos->count() }} Foto Tersimpan</span>
            </div>

            <div class="photos" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(200px, 1fr)); gap:16px;">
                @foreach($house->photos as $photo)

    @php
        $photoDisk = $photo->disk ?: 'public';
        $photoUrl = \Illuminate\Support\Facades\Storage::disk($photoDisk)->url($photo->path);

        $photoCaption = $photo->caption
            ?? ucfirst(str_replace(
                ['photo_', '_'],
                ['', ' '],
                $photo->type ?? 'Foto'
            ));
    @endphp

    <div style="border:1px solid var(--slate-200); border-radius:var(--radius-md); overflow:hidden; background:var(--slate-50);">

        <button
            type="button"
            onclick="openPhotoPreview(@js($photoUrl), @js($photoCaption))"
            style="display:block; width:100%; border:0; padding:0; background:none; cursor:zoom-in;"
            title="Klik untuk melihat foto lebih besar"
        >
            <img
                src="{{ $photoUrl }}"
                alt="{{ $photoCaption }}"
                style="width:100%; height:160px; object-fit:cover; display:block;"
                onerror="this.style.display='none'; this.parentElement.nextElementSibling.style.display='flex';"
            >
        </button>

        <div
            style="display:none; height:160px; align-items:center; justify-content:center; background:var(--slate-100); color:var(--slate-400); font-size:13px;"
        >
            Foto tidak ditemukan
        </div>

        <div style="padding:8px 12px; font-size:12px; font-weight:700; color:var(--slate-700);">
            {{ $photoCaption }}
        </div>

    </div>

@endforeach
            </div>
        </div>
    @endif

    <div id="photo-preview-modal" role="dialog" aria-modal="true" aria-label="Pratinjau foto dokumentasi" onclick="closePhotoPreview()" style="display:none; position:fixed; inset:0; z-index:2000; padding:24px; background:rgba(2, 6, 23, .88); align-items:center; justify-content:center;">
        <div onclick="event.stopPropagation()" style="position:relative; max-width:min(1100px, 100%); max-height:100%;">
            <button type="button" onclick="closePhotoPreview()" aria-label="Tutup pratinjau foto" style="position:absolute; top:12px; right:12px; z-index:1; width:38px; height:38px; border:0; border-radius:50%; background:rgba(15,23,42,.8); color:#fff; font-size:22px; line-height:1; cursor:pointer;">&times;</button>
            <img id="photo-preview-image" src="" alt="" style="display:block; max-width:100%; max-height:82vh; border-radius:var(--radius-md); object-fit:contain;">
            <p id="photo-preview-caption" style="margin:10px 0 0; text-align:center; color:#fff; font-size:14px;"></p>
        </div>
    </div>

    {{-- DATA PENGHUNI --}}
    @if($house->occupants && $house->occupants->isNotEmpty())
        <div class="card" style="margin-bottom: 24px; padding:0; overflow:hidden;">
            <div style="padding: 18px 24px; border-bottom: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center;">
                <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                    Daftar Anggota Penghuni Rumah
                </h3>
                @can('update', $house)
                    <a href="{{ route('houses.occupants.edit', $house) }}" class="btn small">
                        Kelola Penghuni
                    </a>
                @endcan
            </div>

            <div class="tablewrap" style="border:none; border-radius:0;">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>NIK</th>
                            <th>Hubungan</th>
                            <th>Jenis Kelamin</th>
                            <th>Usia</th>
                            <th>Pendidikan / Pekerjaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($house->occupants as $idx => $occ)
                            <tr>
                                <td class="muted">{{ $idx + 1 }}</td>
                                <td><strong>{{ $occ->name ?: '-' }}</strong></td>
                                <td><code style="background:var(--slate-100); padding:2px 6px; border-radius:4px;">{{ $occ->nik ?: '-' }}</code></td>
                                <td><span class="badge" style="background:var(--slate-100);">{{ $occ->relationship ?: '-' }}</span></td>
                                <td>{{ $occ->gender === 'M' || $occ->gender === 'L' ? 'Laki-laki' : ($occ->gender === 'F' || $occ->gender === 'P' ? 'Perempuan' : '-') }}</td>
                                <td>{{ $occ->age ? $occ->age . ' Th' : '-' }}</td>
                                <td>{{ $occ->education ?: '-' }} / {{ $occ->job ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- HASIL ASSESSMENT --}}
    @if($latestAssessment)
        <div class="card" style="margin-bottom: 24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid var(--slate-100); padding-bottom:12px;">
                <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                    Hasil Assessment & Skor Penilaian
                </h3>
                @can('update', $house)
                    <a href="{{ route('houses.assessment.edit', $house) }}" class="btn small primary">
                        Edit Assessment
                    </a>
                @endcan
            </div>

            <div class="grid4" style="margin-bottom:16px;">
                <div style="background:var(--slate-50); padding:12px 16px; border-radius:var(--radius-md);">
                    <div class="muted" style="font-size:11px;">TAHUN ASSESSMENT</div>
                    <div style="font-size:18px; font-weight:800; color:var(--slate-900);">{{ $latestAssessment->assessment_year }}</div>
                </div>
                <div style="background:var(--slate-50); padding:12px 16px; border-radius:var(--radius-md);">
                    <div class="muted" style="font-size:11px;">TANGGAL SURVEI</div>
                    <div style="font-size:18px; font-weight:800; color:var(--slate-900);">{{ $latestAssessment->assessment_date?->format('d/m/Y') ?? '-' }}</div>
                </div>
                <div style="background:var(--slate-50); padding:12px 16px; border-radius:var(--radius-md);">
                    <div class="muted" style="font-size:11px;">NILAI / SCORE TOTAL</div>
                    <div style="font-size:22px; font-weight:800; color:var(--primary-700);">{{ $latestAssessment->score ?? '-' }}</div>
                </div>
                <div style="background:var(--slate-50); padding:12px 16px; border-radius:var(--radius-md);">
                    <div class="muted" style="font-size:11px;">TINGKAT PRIORITAS</div>
                    <div style="font-size:18px; font-weight:800; color:var(--slate-900);">{{ ucfirst($latestAssessment->priority_level ?? '-') }}</div>
                </div>
            </div>

            @if($latestAssessment->notes)
                <div style="background:var(--slate-50); padding:14px 18px; border-radius:var(--radius-md); border-left:4px solid var(--primary-500); margin-bottom:16px;">
                    <div style="font-size:12px; font-weight:700; color:var(--slate-700); margin-bottom:2px;">Catatan Penilai:</div>
                    <p style="font-size:13px; color:var(--slate-700); margin:0;">{{ $latestAssessment->notes }}</p>
                </div>
            @endif

            @if($latestAssessment->items && $latestAssessment->items->isNotEmpty())
                <div class="tablewrap" style="margin-top:14px;">
                    <table>
                        <thead>
                            <tr>
                                <th>Kategori Parameter</th>
                                <th>Nilai / Kondisi</th>
                                <th>Catatan Tambahan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestAssessment->items as $item)
                                <tr>
                                    <td><strong>{{ ucwords(str_replace('_', ' ', $item->category)) }}</strong></td>
                                    <td>
                                        <span class="badge" style="background:var(--slate-100); color:var(--slate-800);">
                                            {{ $item->value ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="muted">{{ $item->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    @if(auth()->check() && auth()->user()->hasRole('admin'))
        {{-- MODAL CATATAN REVISI ADMIN --}}
        <div id="revision-modal" role="dialog" aria-modal="true" aria-labelledby="revision-modal-title" style="display:none; position:fixed; inset:0; z-index:2000; padding:20px; background:rgba(15, 23, 42, 0.75); align-items:center; justify-content:center; backdrop-filter:blur(4px);">
            <div style="background:#ffffff; border-radius:var(--radius-xl); max-width:540px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border:1px solid var(--slate-200); overflow:hidden;">
                <div style="padding:20px 24px; border-bottom:1px solid var(--slate-200); background:#fff1f2; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <h3 id="revision-modal-title" style="font-size:16px; font-weight:800; color:#9f1239; margin:0;">
                            Kembalikan untuk Revisi
                        </h3>
                        <p id="revision-house-code" class="muted" style="font-size:12px; margin:2px 0 0 0; font-family:monospace; color:#be123c;"></p>
                    </div>
                    <button type="button" onclick="closeRevisionModal()" style="border:none; background:none; color:var(--slate-400); font-size:22px; line-height:1; cursor:pointer; width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:6px;">
                        &times;
                    </button>
                </div>

                <form id="revision-form" method="POST" action="">
                    @csrf
                    <div style="padding:24px;">
                        <label for="revision_notes" style="display:block; font-size:13px; font-weight:700; color:var(--slate-800); margin-bottom:8px;">
                            Catatan Revisi / Instruksi Perbaikan <span style="color:#e11d48;">*</span>
                        </label>
                        <textarea 
                            id="revision_notes" 
                            name="revision_notes" 
                            rows="4" 
                            required 
                            minlength="5"
                            maxlength="3000"
                            placeholder="Contoh: Foto tampak depan buram, tolong unggah ulang. Koordinat GPS perlu diperbaiki karena tidak sesuai dengan lokasi desa..."
                            style="width:100%; border-radius:var(--radius-md); border:1px solid var(--slate-300); padding:12px; font-size:13px; font-family:inherit; resize:vertical; line-height:1.5; box-sizing:border-box;"
                        ></textarea>
                    </div>

                    <div style="padding:16px 24px; background:var(--slate-50); border-top:1px solid var(--slate-200); display:flex; justify-content:flex-end; gap:10px;">
                        <button type="button" onclick="closeRevisionModal()" class="btn">
                            Batal
                        </button>
                        <button type="submit" class="btn danger" style="background:#e11d48; border-color:#e11d48; color:#fff;">
                            Kirim Catatan Revisi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- MODAL KELOLA STATUS REALISASI RTLH --}}
    <div id="realization-modal" role="dialog" aria-modal="true" aria-labelledby="realization-modal-title" style="display:none; position:fixed; inset:0; z-index:2000; padding:20px; background:rgba(15, 23, 42, 0.75); align-items:center; justify-content:center; backdrop-filter:blur(4px);">
        <div style="background:#ffffff; border-radius:var(--radius-xl); max-width:580px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border:1px solid var(--slate-200); overflow:hidden; max-height:92vh; display:flex; flex-direction:column;">
            
            {{-- MODAL HEADER --}}
            <div style="padding:18px 24px; border-bottom:1px solid var(--slate-200); background:#f8fafc; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 id="realization-modal-title" style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                        Pembaruan Status Realisasi RTLH
                    </h3>
                    <p class="muted" style="font-size:12px; margin:2px 0 0 0; font-family:monospace;">
                        Kode RTLH: {{ $house->house_code }}
                    </p>
                </div>
                <button type="button" onclick="closeRealizationModal()" style="border:none; background:none; color:var(--slate-400); font-size:22px; line-height:1; cursor:pointer; width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:6px;">
                    &times;
                </button>
            </div>

            {{-- MODAL BODY --}}
            <form method="POST" action="{{ route('houses.realization.update', $house) }}" style="overflow-y:auto; flex:1;">
                @csrf
                <div style="padding:24px; display:flex; flex-direction:column; gap:18px;">
                    
                    {{-- PILIHAN STATUS REALISASI --}}
                    <div>
                        <label style="display:block; font-size:13px; font-weight:700; color:var(--slate-800); margin-bottom:8px;">
                            Pilih Status Realisasi <span style="color:#e11d48;">*</span>
                        </label>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                            <label id="modal-card-realized" onclick="setModalRealization('realized')" style="border:2px solid {{ $house->realization_status === 'realized' ? '#10b981' : 'var(--slate-200)' }}; background:{{ $house->realization_status === 'realized' ? '#f0fdf4' : '#fff' }}; border-radius:var(--radius-lg); padding:12px; cursor:pointer; display:flex; align-items:center; gap:10px; transition:all 0.2s;">
                                <input type="radio" name="realization_status" value="realized" id="modal_status_realized" @checked($house->realization_status === 'realized') style="accent-color:#16a34a;">
                                <div>
                                    <div style="font-size:13px; font-weight:800; color:#15803d;">
                                        Sudah Terealisasi
                                    </div>
                                    <div class="muted" style="font-size:11px; margin-top:2px;">Bantuan selesai ditangani</div>
                                </div>
                            </label>

                            <label id="modal-card-not-realized" onclick="setModalRealization('not_realized')" style="border:2px solid {{ $house->realization_status !== 'realized' ? '#f59e0b' : 'var(--slate-200)' }}; background:{{ $house->realization_status !== 'realized' ? '#fffbeb' : '#fff' }}; border-radius:var(--radius-lg); padding:12px; cursor:pointer; display:flex; align-items:center; gap:10px; transition:all 0.2s;">
                                <input type="radio" name="realization_status" value="not_realized" id="modal_status_not_realized" @checked($house->realization_status !== 'realized') style="accent-color:#d97706;">
                                <div>
                                    <div style="font-size:13px; font-weight:800; color:#b45309;">
                                        Belum Terealisasi
                                    </div>
                                    <div class="muted" style="font-size:11px; margin-top:2px;">Dalam antrian / usulan</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- SUMBER ANGGARAN --}}
                    <div>
                        <label for="modal_funding_source" style="display:block; font-size:13px; font-weight:700; color:var(--slate-800); margin-bottom:6px;">
                            Sumber Anggaran Penanganan
                        </label>
                        <input
                            type="text"
                            id="modal_funding_source"
                            name="funding_source"
                            value="{{ old('funding_source', $house->funding_source) }}"
                            placeholder="Contoh: APBD Kabupaten Cirebon, DAK, BSPS, Baznas, CSR..."
                            list="funding_sources_datalist"
                            style="width:100%; border-radius:var(--radius-md); border:1px solid var(--slate-300); padding:10px 12px; font-size:13px; box-sizing:border-box;"
                        >
                        <datalist id="funding_sources_datalist">
                            <option value="APBD Kabupaten Cirebon">
                            <option value="APBD Provinsi Jawa Barat">
                            <option value="APBN (BSPS / KemenPUPR)">
                            <option value="Dana Alokasi Khusus (DAK)">
                            <option value="BAZNAS">
                            <option value="Corporate Social Responsibility (CSR)">
                            <option value="Swadaya Masyarakat">
                        </datalist>
                    </div>

                    {{-- NOMINAL ANGGARAN & TANGGAL REALISASI --}}
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div>
                            <label for="modal_budget_amount" style="display:block; font-size:13px; font-weight:700; color:var(--slate-800); margin-bottom:6px;">
                                Nilai Anggaran (Rp)
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                id="modal_budget_amount"
                                name="budget_amount"
                                value="{{ old('budget_amount', $house->budget_amount) }}"
                                placeholder="Misal: 20000000"
                                style="width:100%; border-radius:var(--radius-md); border:1px solid var(--slate-300); padding:10px 12px; font-size:13px; box-sizing:border-box;"
                            >
                        </div>

                        <div>
                            <label for="modal_realized_at" style="display:block; font-size:13px; font-weight:700; color:var(--slate-800); margin-bottom:6px;">
                                Tanggal Realisasi
                            </label>
                            <input
                                type="date"
                                id="modal_realized_at"
                                name="realized_at"
                                value="{{ old('realized_at', $house->realized_at ? $house->realized_at->format('Y-m-d') : date('Y-m-d')) }}"
                                style="width:100%; border-radius:var(--radius-md); border:1px solid var(--slate-300); padding:10px 12px; font-size:13px; box-sizing:border-box;"
                            >
                        </div>
                    </div>

                    {{-- CATATAN PELAKSANAAN / KETERANGAN --}}
                    <div>
                        <label for="modal_realization_notes" style="display:block; font-size:13px; font-weight:700; color:var(--slate-800); margin-bottom:6px;">
                            Catatan Pelaksanaan Realisasi
                        </label>
                        <textarea
                            id="modal_realization_notes"
                            name="realization_notes"
                            rows="3"
                            placeholder="Keterangan tambahan hasil monev atau serah terima bantuan..."
                            style="width:100%; border-radius:var(--radius-md); border:1px solid var(--slate-300); padding:10px 12px; font-size:13px; font-family:inherit; resize:vertical; box-sizing:border-box;"
                        >{{ old('realization_notes', $house->realization_notes) }}</textarea>
                    </div>

                </div>

                {{-- MODAL FOOTER --}}
                <div style="padding:16px 24px; background:var(--slate-50); border-top:1px solid var(--slate-200); display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closeRealizationModal()" class="btn">
                        Batal
                    </button>
                    <button type="submit" class="btn primary" style="background:#059669; border-color:#059669; color:#fff;">
                        Simpan Status Realisasi
                    </button>
                </div>
            </form>

        </div>
    </div>

        @push('scripts')
        <script>
            function openPhotoPreview(source, caption) {
                const modal = document.getElementById('photo-preview-modal');
                const image = document.getElementById('photo-preview-image');
                const captionEl = document.getElementById('photo-preview-caption');

                image.src = source;
                image.alt = caption || 'Foto Rumah';
                captionEl.textContent = caption || 'Foto Rumah';

                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            function closePhotoPreview() {
                const modal = document.getElementById('photo-preview-modal');
                const image = document.getElementById('photo-preview-image');

                modal.style.display = 'none';
                image.src = '';
                document.body.style.overflow = '';
            }

            function openRevisionModal(actionUrl, houseCode, existingNotes) {
                const modal = document.getElementById('revision-modal');
                const form = document.getElementById('revision-form');
                const codeEl = document.getElementById('revision-house-code');
                const notesInput = document.getElementById('revision_notes');

                if (!modal) return;
                form.action = actionUrl;
                codeEl.textContent = 'Kode RTLH: ' + houseCode;
                notesInput.value = existingNotes || '';
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
                setTimeout(() => notesInput.focus(), 50);
            }

            function closeRevisionModal() {
                const modal = document.getElementById('revision-modal');
                if (!modal) return;
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }

            function openRealizationModal() {
                const modal = document.getElementById('realization-modal');
                if (!modal) return;
                modal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }

            function closeRealizationModal() {
                const modal = document.getElementById('realization-modal');
                if (!modal) return;
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }

            function setModalRealization(status) {
                const radioRealized = document.getElementById('modal_status_realized');
                const radioNotRealized = document.getElementById('modal_status_not_realized');
                const cardRealized = document.getElementById('modal-card-realized');
                const cardNotRealized = document.getElementById('modal-card-not-realized');

                if (status === 'realized') {
                    if (radioRealized) radioRealized.checked = true;
                    if (cardRealized) {
                        cardRealized.style.borderColor = '#10b981';
                        cardRealized.style.background = '#f0fdf4';
                    }
                    if (cardNotRealized) {
                        cardNotRealized.style.borderColor = 'var(--slate-200)';
                        cardNotRealized.style.background = '#fff';
                    }
                } else {
                    if (radioNotRealized) radioNotRealized.checked = true;
                    if (cardNotRealized) {
                        cardNotRealized.style.borderColor = '#f59e0b';
                        cardNotRealized.style.background = '#fffbeb';
                    }
                    if (cardRealized) {
                        cardRealized.style.borderColor = 'var(--slate-200)';
                        cardRealized.style.background = '#fff';
                    }
                }
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closePhotoPreview();
                    closeRevisionModal();
                    closeRealizationModal();
                }
            });
        </script>

        @if($house->latitude !== null && $house->longitude !== null)
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const mapEl = document.getElementById('mini-map');
                if (!mapEl || typeof L === 'undefined') return;

                const lat = {{ (float) $house->latitude }};
                const lng = {{ (float) $house->longitude }};

                const miniMap = L.map('mini-map', {
                    zoomControl: true,
                    scrollWheelZoom: false
                }).setView([lat, lng], 16);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap | Kota Cirebon'
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

                const marker = L.marker([lat, lng], { icon: customIcon }).addTo(miniMap);
                marker.bindPopup(`
                    <div style="font-family:sans-serif; font-size:12px; line-height:1.4;">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:4px;">
                            <strong style="color:#0f172a; font-family:monospace;">{{ $house->house_code }}</strong>
                            <span style="font-size:10px; font-weight:800; padding:2px 6px; border-radius:999px; ${isRealized ? 'background:#dcfce7; color:#15803d;' : 'background:#fef3c7; color:#b45309;'}">
                                ${isRealized ? 'Sudah Terealisasi' : 'Belum Terealisasi'}
                            </span>
                        </div>
                        <span style="color:#475569;">{{ addslashes($house->head_of_household_name ? 'CPBM: ' . $house->head_of_household_name : ($house->address ?: ($house->region?->name ?? 'Kabupaten Cirebon'))) }}</span>
                    </div>
                `).openPopup();

                setTimeout(function () {
                    miniMap.invalidateSize();
                }, 300);
            });
        </script>
        @endif
        @endpush
</x-app-layout>
