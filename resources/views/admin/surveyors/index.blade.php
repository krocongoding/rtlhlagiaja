<x-layouts.app title="Kelola & Monitoring Surveyor">

    <div class="pagehead">
        <div>
            <div class="kicker">
                <i class="fa-solid fa-users-gear"></i> MANAJEMEN PETUGAS LAPANGAN
            </div>
            <h1>Kelola & Monitoring Surveyor</h1>
            <p class="muted">
                Daftar lengkap petugas surveyor lapangan, status keaktifan akun, serta ringkasan riwayat data RTLH yang telah diinput.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('admin.dashboard') }}" class="btn">
                <i class="fa-solid fa-arrow-left"></i> Dashboard Admin
            </a>
            <a href="{{ route('admin.surveyors.bulk-create') }}" class="btn" style="background:#0284c7; color:#fff; border-color:#0284c7;">
                <i class="fa-solid fa-users-medical"></i> Tambah Banyak (Bulk)
            </a>
            <a href="{{ route('admin.surveyors.create') }}" class="btn primary">
                <i class="fa-solid fa-user-plus"></i> Tambah Surveyor Baru
            </a>
        </div>
    </div>

    {{-- STAT METRICS --}}
    <div class="grid3" style="margin-bottom: 24px;">
        <div class="stat-widget">
            <div class="stat-widget-icon blue">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-label">Total Surveyor</div>
            <div class="stat">{{ number_format($totalSurveyors) }}</div>
            <div class="stat-desc">Jumlah petugas terdaftar di sistem</div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon emerald">
                <i class="fa-solid fa-user-check"></i>
            </div>
            <div class="stat-label">Surveyor Aktif</div>
            <div class="stat" style="color:#059669;">{{ number_format($activeSurveyors) }}</div>
            <div class="stat-desc">Dapat melakukan input & update data</div>
        </div>

        <div class="stat-widget">
            <div class="stat-widget-icon amber">
                <i class="fa-solid fa-clipboard-list"></i>
            </div>
            <div class="stat-label">Total Data Lapangan</div>
            <div class="stat" style="color:#b45309;">
                {{ number_format($surveyors->sum('total_houses_count')) }}
            </div>
            <div class="stat-desc">Disurvei oleh seluruh petugas</div>
        </div>
    </div>

    {{-- FILTER & SEARCH CARD --}}
    <div class="card" style="margin-bottom: 24px;">
        <form method="GET" action="{{ route('admin.surveyors.index') }}">
            <div class="toolbar">
                <div style="flex:1; min-width:260px; position:relative;">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama atau email surveyor..."
                        style="padding-left:36px;"
                    >
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--slate-400);"></i>
                </div>

                <div style="width:180px;">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">-- Semua Status --</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Non-Aktif</option>
                    </select>
                </div>

                <button type="submit" class="btn primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>

                @if(request('search') || request('status') !== null)
                    <a href="{{ route('admin.surveyors.index') }}" class="btn">
                        <i class="fa-solid fa-rotate-left"></i> Reset Filter
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABLE SURVEYORS CARD --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center;">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--slate-900); margin:0;">
                <i class="fa-solid fa-id-badge text-blue-500" style="color:#2563eb;"></i> Daftar Petugas Surveyor
            </h3>
            <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700;">
                {{ $surveyors->total() }} Surveyor
            </span>
        </div>

        <div class="tablewrap" style="border:none; border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Nama Surveyor</th>
                        <th>Email / Akun</th>
                        <th>Status</th>
                        <th style="text-align:center;">Total Input</th>
                        <th style="text-align:center;">Pending Review</th>
                        <th style="text-align:center;">Verified</th>
                        <th style="text-align:center;">Published</th>
                        <th style="text-align:center;">Revisi</th>
                        <th style="text-align: right; width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($surveyors as $s)
                        <tr>
                            <td>
                                <strong style="font-family:monospace; font-size:13px; color:var(--slate-600);">
                                    #{{ $s->id }}
                                </strong>
                            </td>

                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div class="user-avatar" style="width:32px; height:32px; font-size:13px; flex-shrink:0;">
                                        {{ strtoupper(substr($s->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.surveyors.show', $s) }}" style="font-weight:700; color:var(--slate-900); text-decoration:none;">
                                            {{ $s->name }}
                                        </a>
                                        @if($s->hasRole('admin'))
                                            <span class="badge" style="background:#fef3c7; color:#92400e; font-size:10px; font-weight:800; padding:2px 6px; margin-left:4px; border:1px solid #fde68a;">
                                                <i class="fa-solid fa-shield-halved"></i> Admin
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="muted" style="font-size:13px;">
                                {{ $s->email }}
                            </td>

                            <td>
                                @if($s->is_active)
                                    <span class="badge" style="background:#ecfdf5; color:#047857; font-weight:700;">
                                        <i class="fa-solid fa-circle" style="font-size:8px;"></i> Aktif
                                    </span>
                                @else
                                    <span class="badge" style="background:#fef2f2; color:#b91c1c; font-weight:700;">
                                        <i class="fa-solid fa-circle" style="font-size:8px;"></i> Non-Aktif
                                    </span>
                                @endif
                            </td>

                            <td style="text-align:center;">
                                <span class="badge" style="background:var(--slate-100); color:var(--slate-800); font-weight:800;">
                                    {{ $s->total_houses_count }}
                                </span>
                            </td>

                            <td style="text-align:center;">
                                @if($s->submitted_houses_count > 0)
                                    <span class="badge" style="background:#fef3c7; color:#b45309; font-weight:800;">
                                        {{ $s->submitted_houses_count }}
                                    </span>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>

                            <td style="text-align:center;">
                                @if($s->verified_houses_count > 0)
                                    <span class="badge" style="background:#ede9fe; color:#6d28d9; font-weight:800;">
                                        {{ $s->verified_houses_count }}
                                    </span>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>

                            <td style="text-align:center;">
                                @if($s->published_houses_count > 0)
                                    <span class="badge" style="background:#d1fae5; color:#065f46; font-weight:800;">
                                        {{ $s->published_houses_count }}
                                    </span>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>

                            <td style="text-align:center;">
                                @if($s->revision_houses_count > 0)
                                    <span class="badge" style="background:#ffe4e6; color:#be123c; font-weight:800;">
                                        {{ $s->revision_houses_count }}
                                    </span>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>

                            <td style="text-align: right;">
                                <div class="actions" style="justify-content: flex-end; gap:6px;">
                                    <a href="{{ route('admin.surveyors.show', $s) }}" class="btn small" title="Log History & Detail Work">
                                        <i class="fa-solid fa-clock-rotate-left"></i> Log History
                                    </a>

                                    <a href="{{ route('admin.surveyors.edit', $s) }}" class="btn small" title="Edit Surveyor">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>

                                    <form method="POST" action="{{ route('admin.surveyors.toggle-status', $s) }}" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status aktif surveyor ini?')">
                                        @csrf
                                        <button type="submit" class="btn small {{ $s->is_active ? 'danger' : 'primary' }}" title="{{ $s->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                            <i class="fa-solid {{ $s->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 48px 24px;">
                                <div style="font-size:32px; color:var(--slate-400); margin-bottom:12px;">
                                    <i class="fa-solid fa-users-slash"></i>
                                </div>
                                <h4 style="font-size:16px; font-weight:700; color:var(--slate-800); margin-bottom:4px;">
                                    Belum Ada Surveyor Terdaftar
                                </h4>
                                <p class="muted" style="margin-bottom:16px;">Tambahkan surveyor baru untuk mulai mengatribusikan data survei RTLH.</p>
                                <a href="{{ route('admin.surveyors.create') }}" class="btn primary small">
                                    <i class="fa-solid fa-plus"></i> Tambah Surveyor
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($surveyors->hasPages())
            <div style="padding: 16px 24px; border-top: 1px solid var(--slate-200); background: var(--slate-50);">
                {{ $surveyors->links() }}
            </div>
        @endif
    </div>

</x-layouts.app>
