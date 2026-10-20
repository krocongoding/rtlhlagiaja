<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="kicker">
                BASIS DATA INTERNAL
            </div>
            <h1>Manajemen Data RTLH</h1>
            <p class="muted">
                Daftar lengkap rumah tidak layak huni Kabupaten Cirebon beserta status verifikasi dan survei.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('houses.create') }}" class="btn primary">
                Tambah Data Rumah
            </a>
        </div>
    </x-slot>

    {{-- METRIK REKAP REALISASI DESA & DAERAH --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="card" style="padding: 18px 20px;">
            <span class="muted" style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;">Total Basis Data RTLH</span>
            <div style="font-size:26px; font-weight:900; color:var(--slate-900); margin-top:4px;">{{ number_format($totalHouses ?? $houses->total()) }} <span style="font-size:13px; font-weight:600; color:var(--slate-500);">Unit</span></div>
        </div>

        <div class="card" style="padding: 18px 20px; border-left: 4px solid #10b981;">
            <span style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#15803d;">Sudah Terealisasi</span>
            <div style="font-size:26px; font-weight:900; color:#15803d; margin-top:4px;">{{ number_format($realizedCount ?? 0) }} <span style="font-size:13px; font-weight:600; color:#16a34a;">Unit</span></div>
        </div>

        <div class="card" style="padding: 18px 20px; border-left: 4px solid #f59e0b;">
            <span style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; color:#b45309;">Belum Terealisasi</span>
            <div style="font-size:26px; font-weight:900; color:#b45309; margin-top:4px;">{{ number_format($notRealizedCount ?? 0) }} <span style="font-size:13px; font-weight:600; color:#d97706;">Unit</span></div>
        </div>
    </div>

    {{-- SEARCH & FILTER CARD --}}
    <div class="card" style="margin-bottom: 24px;">
        <form method="GET" action="{{ route('houses.index') }}">
            <div class="toolbar" style="flex-wrap:wrap; gap:12px;">
                <div style="flex:2; min-width:260px;">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari NIK, Nama Kepala Keluarga, Kode RTLH, atau Alamat..."
                    >
                </div>

                @if(isset($villages) && $villages->isNotEmpty())
                    <div style="flex:1; min-width:180px;">
                        <select name="region_id" style="width:100%; height:42px; border-radius:var(--radius-md); border:1px solid var(--slate-300); padding:0 12px; font-size:13px; background:#fff;">
                            <option value="">-- Semua Kelurahan --</option>
                            @foreach($villages as $vil)
                                <option value="{{ $vil->id }}" @selected(request('region_id') == $vil->id)>
                                    Kelurahan {{ $vil->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div style="flex:1; min-width:180px;">
                    <select name="realization_status" style="width:100%; height:42px; border-radius:var(--radius-md); border:1px solid var(--slate-300); padding:0 12px; font-size:13px; background:#fff;">
                        <option value="">-- Semua Status Realisasi --</option>
                        <option value="realized" @selected(request('realization_status') === 'realized')>Sudah Terealisasi</option>
                        <option value="not_realized" @selected(request('realization_status') === 'not_realized')>Belum Terealisasi</option>
                    </select>
                </div>

                <div style="flex:1; min-width:170px;">
                    <select name="status" style="width:100%; height:42px; border-radius:var(--radius-md); border:1px solid var(--slate-300); padding:0 12px; font-size:13px; background:#fff;">
                        <option value="">-- Semua Status Alur --</option>
                        <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                        <option value="submitted" @selected(request('status') === 'submitted')>Menunggu Review</option>
                        <option value="revision" @selected(request('status') === 'revision')>Perlu Revisi</option>
                        <option value="verified" @selected(request('status') === 'verified')>Terverifikasi</option>
                        <option value="published" @selected(request('status') === 'published')>Published</option>
                    </select>
                </div>

                <button type="submit" class="btn primary">
                    Filter
                </button>

                @if(request()->hasAny(['search', 'region_id', 'realization_status', 'status']))
                    <a href="{{ route('houses.index') }}" class="btn">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABLE DATA CARD --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                    Daftar Unit Rumah RTLH
                </h3>
                <p class="muted" style="margin:2px 0 0 0; font-size:12px;">Monitoring data CPBM, kondisi rumah, dan realisasi anggaran per kelurahan.</p>
            </div>
            <span class="badge published">
                Total {{ number_format($houses->total()) }} Unit
            </span>
        </div>

        <div class="tablewrap" style="border:none; border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 45px;">No</th>
                        <th style="width: 150px;">Kode RTLH</th>
                        <th>Calon Penerima (CPBM)</th>
                        <th>Kelurahan</th>
                        <th>Alamat / Blok</th>
                        <th>Tahun</th>
                        <th>Status Realisasi</th>
                        <th style="text-align: right; width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($houses as $house)
                        <tr>
                            <td class="muted">{{ $houses->firstItem() + $loop->index }}</td>
                            <td>
                                <strong style="font-family:monospace; font-size:13px; color:var(--slate-900);">
                                    {{ $house->house_code }}
                                </strong>
                                @if($house->status === 'revision')
                                    <div style="margin-top:4px;">
                                        <span class="badge" style="background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5; font-size:10px; font-weight:700;" title="{{ $house->revision_notes ? 'Catatan: ' . $house->revision_notes : 'Perlu revisi' }}">
                                            Perlu Revisi
                                        </span>
                                    </div>
                                 @elseif($house->status === 'submitted')
                                     <div style="margin-top:4px;">
                                         <span class="badge submitted" style="font-size:10px;">
                                             Review
                                         </span>
                                     </div>
                                 @elseif($house->status === 'verified')
                                     <div style="margin-top:4px;">
                                         <span class="badge" style="background:#ede9fe; color:#6d28d9; font-size:10px;">
                                             Verified
                                         </span>
                                     </div>
                                 @elseif($house->status === 'published')
                                     <div style="margin-top:4px;">
                                         <span class="badge published" style="font-size:10px;">
                                             Published
                                         </span>
                                     </div>
                                 @elseif($house->status === 'draft')
                                     <div style="margin-top:4px;">
                                         <span class="badge" style="background:var(--slate-100); color:var(--slate-600); font-size:10px;">
                                             Draft
                                         </span>
                                     </div>
                                 @endif
                            </td>
                            <td>
                                @if($house->head_of_household_name || $house->nik)
                                    <div style="font-weight:700; color:var(--slate-900);">{{ $house->head_of_household_name ?: 'Nama belum diisi' }}</div>
                                    <div class="muted" style="font-size:11px; font-family:monospace;">NIK: {{ \App\Support\DataMasker::maskNik($house->nik) }}</div>
                                @else
                                    <span class="muted" style="font-size:12px;">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background:var(--slate-100); color:var(--slate-700);">
                                    {{ $house->region?->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                {{ $house->address ?: ($house->block ? 'Blok ' . $house->block : '-') }}
                                @if($house->rt || $house->rw)
                                    <span class="muted" style="font-size:11px; display:block;">RT {{ $house->rt ?? '-' }} / RW {{ $house->rw ?? '-' }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background:#e0f2fe; color:#0369a1;">
                                    {{ $house->survey_year ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @if($house->realization_status === 'realized')
                                    <span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #86efac; font-weight:700; font-size:12px;">
                                        Sudah Terealisasi
                                    </span>
                                @else
                                    <span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde047; font-weight:700; font-size:12px;">
                                        Belum Terealisasi
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('houses.show', $house) }}" class="btn small primary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:48px 24px;" class="muted">
                                <p style="font-size:14px; margin:0;">Belum ada data unit rumah RTLH yang tercatat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($houses->hasPages())
    <div class="pagination-wrap">
        <div class="pagination-info">
            Menampilkan
            <strong>{{ $houses->firstItem() }}</strong>
            -
            <strong>{{ $houses->lastItem() }}</strong>
            dari
            <strong>{{ $houses->total() }}</strong>
        </div>

        <nav class="pagination" aria-label="Pagination">

            {{-- Previous --}}
            @if ($houses->onFirstPage())
                <span class="page-btn disabled" aria-hidden="true">&lsaquo;</span>
            @else
                <a href="{{ $houses->previousPageUrl() }}" class="page-btn" rel="prev" aria-label="Sebelumnya">&lsaquo;</a>
            @endif

            {{-- Nomor halaman --}}
            @foreach ($houses->getUrlRange(
                max(1, $houses->currentPage() - 2),
                min($houses->lastPage(), $houses->currentPage() + 2)
            ) as $page => $url)

                @if ($page == $houses->currentPage())
                    <span class="page-number active">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $url }}" class="page-number">
                        {{ $page }}
                    </a>
                @endif

            @endforeach

            {{-- Next --}}
            @if ($houses->hasMorePages())
                <a href="{{ $houses->nextPageUrl() }}" class="page-btn" rel="next" aria-label="Berikutnya">&rsaquo;</a>
            @else
                <span class="page-btn disabled" aria-hidden="true">&rsaquo;</span>
            @endif

        </nav>
    </div>
@endif
    </div>
</x-app-layout>