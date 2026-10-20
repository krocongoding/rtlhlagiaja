<x-layouts.app title="Detail & Log Pekerjaan - {{ $surveyor->name }}">

    <div class="pagehead">
        <div>
            <div class="kicker">
                <i class="fa-solid fa-clock-rotate-left"></i> LOG HISTORY & DETAIL SURVEYOR
            </div>
            <h1>Log History & Detail Pekerjaan</h1>
            <p class="muted">
                Monitoring menyeluruh hasil survei RTLH dan riwayat aktivitas pekerjaan <strong>{{ $surveyor->name }}</strong> (ID Petugas: #{{ $surveyor->id }}).
            </p>
        </div>

        <div class="actions" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <a href="{{ route('admin.surveyors.index') }}" class="btn">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
            </a>
            <a href="{{ route('admin.surveyors.edit', $surveyor) }}" class="btn">
                <i class="fa-solid fa-pen"></i> Edit Profile Surveyor
            </a>

            @if($surveyor->id !== auth()->id())
                @if($surveyor->hasRole('admin'))
                    <form action="{{ route('admin.surveyors.toggle-admin', $surveyor) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin mencabut hak akses Administrator dari {{ $surveyor->name }}?')">
                        @csrf
                        <button type="submit" class="btn" style="background:#fef2f2; color:#b91c1c; border-color:#fecaca;" title="Cabut wewenang administrator">
                            <i class="fa-solid fa-user-xmark"></i> Cabut Hak Admin
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.surveyors.toggle-admin', $surveyor) }}" method="POST" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin memberikan hak akses ADMINISTRATOR kepada {{ $surveyor->name }}? Petugas akan memiliki kendali penuh atas seluruh sistem.')">
                        @csrf
                        <button type="submit" class="btn primary" style="background:#0284c7; border-color:#0284c7;" title="Promosikan menjadi administrator">
                            <i class="fa-solid fa-shield-halved"></i> Jadikan Administrator
                        </button>
                    </form>
                @endif
            @endif
        </div>
    </div>

    {{-- PROFILE CARD & STATS GRID --}}
    <div style="display:grid; grid-template-columns: 280px 1fr; gap:20px; margin-bottom: 28px;">
        {{-- USER PROFILE CARD --}}
        <div class="card" style="padding: 24px; text-align:center;">
            <div class="user-avatar" style="width:72px; height:72px; font-size:28px; margin:0 auto 16px;">
                {{ strtoupper(substr($surveyor->name, 0, 1)) }}
            </div>
            <h3 style="font-size: 18px; font-weight: 800; color: var(--slate-900); margin-bottom: 4px;">
                {{ $surveyor->name }}
            </h3>
            <div style="font-size:13px; font-weight:600; font-family:monospace; color:var(--slate-500); margin-bottom:12px;">
                ID PETUGAS: #{{ $surveyor->id }}
            </div>

            <div style="margin-bottom: 16px; display:flex; flex-direction:column; gap:6px; align-items:center;">
                @if($surveyor->is_active)
                    <span class="badge" style="background:#ecfdf5; color:#047857; font-weight:700; padding:6px 12px; font-size:12px;">
                        <i class="fa-solid fa-circle" style="font-size:8px;"></i> Akun Aktif
                    </span>
                @else
                    <span class="badge" style="background:#fef2f2; color:#b91c1c; font-weight:700; padding:6px 12px; font-size:12px;">
                        <i class="fa-solid fa-circle" style="font-size:8px;"></i> Akun Non-Aktif
                    </span>
                @endif

                @if($surveyor->hasRole('admin'))
                    <span class="badge" style="background:#fef3c7; color:#92400e; font-weight:800; padding:6px 12px; font-size:12px; border:1px solid #fde68a;">
                        <i class="fa-solid fa-shield-halved"></i> Akses Administrator
                    </span>
                @else
                    <span class="badge" style="background:#f1f5f9; color:#475569; font-weight:700; padding:4px 10px; font-size:11px;">
                        <i class="fa-solid fa-user"></i> Surveyor Lapangan
                    </span>
                @endif
            </div>

            <div style="border-top:1px solid var(--slate-200); padding-top:14px; text-align:left; font-size:13px;">
                <div style="margin-bottom:8px;" class="muted">
                    <i class="fa-solid fa-envelope"></i> <strong>Email:</strong> {{ $surveyor->email }}
                </div>
                <div class="muted">
                    <i class="fa-solid fa-calendar"></i> <strong>Terdaftar:</strong> {{ $surveyor->created_at ? $surveyor->created_at->format('d M Y, H:i') : '-' }}
                </div>
            </div>
        </div>

        {{-- METRICS GRID --}}
        <div>
            <div class="grid3">
                <div class="stat-widget">
                    <div class="stat-widget-icon blue">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                    <div class="stat-label">Total Data Input</div>
                    <div class="stat">{{ number_format($stats['total']) }}</div>
                    <div class="stat-desc">Semua rumah diinput surveyor ini</div>
                </div>

                <div class="stat-widget" style="{{ $stats['submitted'] > 0 ? 'border-color:#fde68a; background:#fffbeb;' : '' }}">
                    <div class="stat-widget-icon amber">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div class="stat-label" style="{{ $stats['submitted'] > 0 ? 'color:#b45309;' : '' }}">Menunggu Review</div>
                    <div class="stat" style="{{ $stats['submitted'] > 0 ? 'color:#b45309;' : '' }}">{{ number_format($stats['submitted']) }}</div>
                    <div class="stat-desc">Perlu verifikasi Admin</div>
                </div>

                <div class="stat-widget">
                    <div class="stat-widget-icon purple">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="stat-label">Verified</div>
                    <div class="stat" style="color:#7c3aed;">{{ number_format($stats['verified']) }}</div>
                    <div class="stat-desc">Data lolos verifikasi</div>
                </div>
            </div>

            <div class="grid3" style="margin-top:16px;">
                <div class="stat-widget">
                    <div class="stat-widget-icon emerald">
                        <i class="fa-solid fa-globe"></i>
                    </div>
                    <div class="stat-label">Published</div>
                    <div class="stat" style="color:#059669;">{{ number_format($stats['published']) }}</div>
                    <div class="stat-desc">Tayang di peta publik</div>
                </div>

                <div class="stat-widget">
                    <div class="stat-widget-icon rose">
                        <i class="fa-solid fa-rotate-left"></i>
                    </div>
                    <div class="stat-label">Minta Revisi</div>
                    <div class="stat" style="color:#e11d48;">{{ number_format($stats['revision']) }}</div>
                    <div class="stat-desc">Dikembalikan untuk diperbaiki</div>
                </div>

                <div class="stat-widget">
                    <div class="stat-widget-icon" style="background:var(--slate-100); color:var(--slate-600);">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                    <div class="stat-label">Draft</div>
                    <div class="stat">{{ number_format($stats['draft']) }}</div>
                    <div class="stat-desc">Belum di-submit surveyor</div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 1: DAFTAR DATA RUMAH YANG DIINPUT --}}
    <div class="card" style="padding:0; overflow:hidden; margin-bottom: 28px;">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-900); margin-bottom:2px;">
                     <img src="{{ asset('images/logo.png') }}" alt="RTLH Cirebon"></i> Riwayat Input Data RTLH
                </h3>
                <p class="muted" style="font-size:13px; margin:0;">Daftar unit rumah yang dimasukkan atau diperbarui oleh petugas ini.</p>
            </div>

            <span class="badge published">
                {{ $houses->total() }} Unit Rumah
            </span>
        </div>

        <div class="tablewrap" style="border:none; border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 140px;">Kode RTLH</th>
                        <th>Wilayah / Kecamatan</th>
                        <th>Alamat / Blok</th>
                        <th>Tahun</th>
                        <th>Status</th>
                        <th>Tanggal Input</th>
                        <th style="text-align: right; width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($houses as $house)
                        <tr>
                            <td>
                                <strong style="font-family:monospace; font-size:14px; color:var(--slate-900);">
                                    {{ $house->house_code }}
                                </strong>
                            </td>

                            <td>
                                <span class="badge" style="background:var(--slate-100); color:var(--slate-700);">
                                    <i class="fa-solid fa-location-dot"></i> {{ $house->region?->name ?? '-' }}
                                </span>
                            </td>

                            <td>
                                {{ $house->address ?: ($house->block ? 'Blok ' . $house->block : '-') }}
                            </td>

                            <td>
                                <span class="badge" style="background:#e0f2fe; color:#0369a1;">
                                    {{ $house->survey_year ?? '-' }}
                                </span>
                            </td>

                            <td>
                                @if($house->status === 'submitted')
                                    <span class="badge submitted"><i class="fa-solid fa-hourglass-half"></i> Menunggu Review</span>
                                @elseif($house->status === 'verified')
                                    <span class="badge" style="background:#ede9fe; color:#6d28d9;"><i class="fa-solid fa-circle-check"></i> Terverifikasi</span>
                                @elseif($house->status === 'published')
                                    <span class="badge published"><i class="fa-solid fa-globe"></i> Published</span>
                                @elseif($house->status === 'revision')
                                    <span class="badge" style="background:#ffe4e6; color:#be123c;"><i class="fa-solid fa-rotate-left"></i> Minta Revisi</span>
                                @else
                                    <span class="badge" style="background:var(--slate-100); color:var(--slate-600);"><i class="fa-solid fa-file-pen"></i> Draft</span>
                                @endif
                            </td>

                            <td class="muted" style="font-size:13px;">
                                {{ $house->created_at ? $house->created_at->format('d M Y H:i') : '-' }}
                            </td>

                            <td style="text-align: right;">
                                <a href="{{ route('houses.show', $house) }}" class="btn small" title="Lihat Detail Data">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 36px 24px;">
                                <span class="muted">Belum ada data rumah yang dimasukkan oleh surveyor ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($houses->hasPages())
            <div style="padding: 14px 24px; border-top: 1px solid var(--slate-200); background: var(--slate-50);">
                {{ $houses->links() }}
            </div>
        @endif
    </div>

    {{-- SECTION 2: AUDIT ACTIVITY LOG HISTORY --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-900); margin-bottom:2px;">
                    <i class="fa-solid fa-timeline text-amber-500" style="color:#f59e0b;"></i> Log Aktivitas & Riwayat Audit (Activity Log)
                </h3>
                <p class="muted" style="font-size:13px; margin:0;">Catatan rekam jejak aktivitas (Tambah, Edit, Submit) yang dilakukan oleh akun surveyor ini.</p>
            </div>

            <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700;">
                {{ $logs->total() }} Log Aktivitas
            </span>
        </div>

        <div class="tablewrap" style="border:none; border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 170px;">Waktu & Tanggal</th>
                        <th style="width: 130px;">Aksi</th>
                        <th>Tipe Objek</th>
                        <th>ID Objek</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td style="font-size:13px; font-weight:600; color:var(--slate-800);">
                                <i class="fa-regular fa-clock muted" style="margin-right:4px;"></i>
                                {{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i:s') : '-' }}
                            </td>

                            <td>
                                @if(in_array($log->action, ['CREATE', 'CREATE_SURVEYOR']))
                                    <span class="badge" style="background:#d1fae5; color:#065f46; font-weight:800;">
                                        <i class="fa-solid fa-plus-circle"></i> {{ $log->action }}
                                    </span>
                                @elseif(in_array($log->action, ['UPDATE', 'SUBMIT']))
                                    <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:800;">
                                        <i class="fa-solid fa-pen"></i> {{ $log->action }}
                                    </span>
                                @elseif(in_array($log->action, ['VERIFY', 'PUBLISH']))
                                    <span class="badge" style="background:#ede9fe; color:#6d28d9; font-weight:800;">
                                        <i class="fa-solid fa-check-double"></i> {{ $log->action }}
                                    </span>
                                @elseif(in_array($log->action, ['REVISION']))
                                    <span class="badge" style="background:#ffe4e6; color:#be123c; font-weight:800;">
                                        <i class="fa-solid fa-rotate-left"></i> {{ $log->action }}
                                    </span>
                                @else
                                    <span class="badge" style="background:var(--slate-100); color:var(--slate-700); font-weight:700;">
                                        {{ $log->action }}
                                    </span>
                                @endif
                            </td>

                            <td style="font-weight:600; color:var(--slate-800);">
                                {{ $log->auditable_type ?? '-' }}
                            </td>

                            <td>
                                @if($log->auditable_id)
                                    <strong style="font-family:monospace; font-size:13px; color:var(--slate-800);">
                                        #{{ $log->auditable_id }}
                                    </strong>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>

                            <td class="muted" style="font-family:monospace; font-size:12px;">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 36px 24px;">
                                <span class="muted">Belum ada catatan log aktivitas audit untuk surveyor ini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div style="padding: 14px 24px; border-top: 1px solid var(--slate-200); background: var(--slate-50);">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</x-layouts.app>
