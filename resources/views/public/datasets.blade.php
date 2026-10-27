<x-layouts.public title="Katalog Open Data RTLH">

    <div class="pagehead">
        <div>
            <div class="kicker">
                <i class="fa-solid fa-database"></i> KATALOG OPEN DATA
            </div>
            <h1>Data RTLH Kota Cirebon</h1>
            <p class="muted">
                Dataset rumah tidak layak huni terverifikasi yang dapat digunakan untuk perencanaan, riset, dan analisis spasial.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('public.download.rtlh', request()->query()) }}" class="btn primary">
                <i class="fa-solid fa-file-csv"></i> Unduh CSV Terfilter
            </a>
            <a href="{{ route('api.public.rtlh') }}" target="_blank" class="btn">
                <i class="fa-solid fa-code"></i> GeoJSON API
            </a>
            <a href="{{ route('public.map') }}" class="btn">
                <i class="fa-solid fa-map-location-dot"></i> Peta GIS
            </a>
        </div>
    </div>

    {{-- FILTER CARD --}}
    <div class="card" style="margin-bottom: 24px;">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:14px;">
            <i class="fa-solid fa-filter text-emerald-600" style="color:#059669;"></i>
            <h3 style="font-size:16px; font-weight:700; color:var(--slate-900); margin:0;">Filter & Pencarian Data</h3>
        </div>

        <form method="GET" action="{{ route('public.datasets') }}">
            <div class="toolbar">
                <div style="flex:1; min-width:240px; position:relative;">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari berdasarkan kode RTLH..."
                        style="padding-left:36px;"
                    >
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--slate-400);"></i>
                </div>

                <div style="min-width:200px;">
                    <select name="region_id">
                        <option value="">Semua Wilayah</option>
                        @foreach($regions as $region)
                            <option
                                value="{{ $region->id }}"
                                @selected((string) request('region_id') === (string) $region->id)
                            >
                                {{ $region->name }} ({{ $region->type ?? 'Kec' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="min-width:140px;">
                    <select name="year">
                        <option value="">Semua Tahun</option>
                        @foreach($years as $year)
                            <option
                                value="{{ $year }}"
                                @selected((string) request('year') === (string) $year)
                            >
                                Tahun {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>

                <a href="{{ route('public.datasets') }}" class="btn">
                    <i class="fa-solid fa-rotate-left"></i> Reset
                </a>
            </div>
        </form>
    </div>

    {{-- TABLE DATA CARD --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-900); margin-bottom: 2px;">
                    <i class="fa-solid fa-table-cells text-emerald-600" style="color:#059669;"></i> Daftar Unit RTLH Publik
                </h3>
                <p class="muted" style="font-size: 13px;">
                    Menampilkan <strong>{{ $houses->firstItem() ?? 0 }} – {{ $houses->lastItem() ?? 0 }}</strong> dari total <strong>{{ number_format($houses->total()) }}</strong> unit terpublikasi.
                </p>
            </div>

            <div style="display:flex; align-items:center; gap:8px;">
                <span class="badge published">
                    <i class="fa-solid fa-circle-check"></i> Status Terverifikasi
                </span>
            </div>
        </div>

        <div class="tablewrap" style="border:none; border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 160px;">Kode RTLH</th>
                        <th>Wilayah Administratif</th>
                        <th>Luas Bangunan</th>
                        <th>Tahun Survei</th>
                        <th>Status Publik</th>
                        <th style="text-align: right; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($houses as $house)
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div class="brand-logo" style="width:28px; height:28px; font-size:12px; border-radius:6px; flex-shrink:0;">
                                        <img src="{{ asset('images/logo.png') }}" alt="Logo RTLH">
                                    </div>
                                    <strong style="color:var(--slate-900); font-family:monospace; font-size:14px;">
                                        {{ $house->house_code }}
                                    </strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge" style="background:var(--slate-100); color:var(--slate-700);">
                                    <i class="fa-solid fa-location-dot"></i> {{ $house->region?->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                @if($house->area_m2 !== null)
                                    <strong>{{ number_format((float) $house->area_m2, 1, ',', '.') }}</strong> <span class="muted">m²</span>
                                @else
                                    <span class="muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge" style="background:#e0f2fe; color:#0369a1;">
                                    <i class="fa-regular fa-calendar"></i> {{ $house->survey_year ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge published">
                                    <i class="fa-solid fa-check"></i> {{ ucfirst($house->status) }}
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('public.rtlh.detail', $house) }}" class="btn small primary">
                                    <i class="fa-solid fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px 24px;">
                                <div style="width:56px; height:56px; border-radius:50%; background:var(--slate-100); color:var(--slate-400); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 14px;">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </div>
                                <h4 style="font-size:16px; font-weight:700; color:var(--slate-800); margin-bottom:4px;">
                                    Data tidak ditemukan
                                </h4>
                                <p class="muted" style="font-size:13px; max-width:400px; margin:0 auto 16px;">
                                    Tidak ada unit RTLH yang sesuai dengan kata kunci pencarian atau filter yang dipilih.
                                </p>
                                <a href="{{ route('public.datasets') }}" class="btn small">
                                    <i class="fa-solid fa-rotate-left"></i> Reset Filter
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($houses->hasPages())
            <div style="padding: 16px 24px; border-top: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                <div class="muted" style="font-size:13px;">
                    Halaman <strong>{{ $houses->currentPage() }}</strong> dari <strong>{{ $houses->lastPage() }}</strong>
                </div>

                <div class="actions" style="margin:0;">
                    @if($houses->onFirstPage())
                        <span class="btn small" style="opacity:0.5; cursor:not-allowed;">
                            <i class="fa-solid fa-chevron-left"></i> Sebelumnya
                        </span>
                    @else
                        <a href="{{ $houses->previousPageUrl() }}" class="btn small">
                            <i class="fa-solid fa-chevron-left"></i> Sebelumnya
                        </a>
                    @endif

                    @if($houses->hasMorePages())
                        <a href="{{ $houses->nextPageUrl() }}" class="btn small primary">
                            Berikutnya <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="btn small" style="opacity:0.5; cursor:not-allowed;">
                            Berikutnya <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- DATA DICTIONARY SECTION --}}
    <div class="card" style="margin-top: 24px;">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:16px;">
            <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-900); margin:0;">
                Kamus Data (Data Dictionary)
            </h3>
        </div>

        <div class="grid2">
            <div style="border:1px solid var(--slate-200); border-radius:var(--radius-md); padding:16px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                    <code style="font-weight:700; color:var(--primary-700); background:var(--primary-50); padding:2px 8px; border-radius:4px;">house_code</code>
                    <span class="badge" style="background:var(--slate-100); font-size:11px;">String</span>
                </div>
                <p class="muted" style="font-size:13px;">Kode unik identifikasi rumah yang dipublikasikan secara resmi.</p>
            </div>

            <div style="border:1px solid var(--slate-200); border-radius:var(--radius-md); padding:16px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                    <code style="font-weight:700; color:var(--primary-700); background:var(--primary-50); padding:2px 8px; border-radius:4px;">region</code>
                    <span class="badge" style="background:var(--slate-100); font-size:11px;">String</span>
                </div>
                <p class="muted" style="font-size:13px;">Nama wilayah administratif tingkat kecamatan / kelurahan di Kota Cirebon.</p>
            </div>

            <div style="border:1px solid var(--slate-200); border-radius:var(--radius-md); padding:16px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                    <code style="font-weight:700; color:var(--primary-700); background:var(--primary-50); padding:2px 8px; border-radius:4px;">area_m2</code>
                    <span class="badge" style="background:var(--slate-100); font-size:11px;">Decimal</span>
                </div>
                <p class="muted" style="font-size:13px;">Luas total lantai bangunan rumah dalam satuan meter persegi ($m^2$).</p>
            </div>

            <div style="border:1px solid var(--slate-200); border-radius:var(--radius-md); padding:16px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                    <code style="font-weight:700; color:var(--primary-700); background:var(--primary-50); padding:2px 8px; border-radius:4px;">survey_year</code>
                    <span class="badge" style="background:var(--slate-100); font-size:11px;">Integer</span>
                </div>
                <p class="muted" style="font-size:13px;">Tahun saat pelaksanaan survei verifikasi faktual di lapangan.</p>
            </div>
        </div>
    </div>

</x-layouts.public>