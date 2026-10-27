<x-app-layout>
    <x-slot name="header">
        <div>
            <div class="kicker">
                <i class="fa-solid fa-map-pin"></i> DATA MASTER
            </div>
            <h1>Wilayah Administratif</h1>
            <p class="muted">
                Daftar kecamatan dan kelurahan di lingkungan Pemerintah Kota Cirebon.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('regions.create') }}" class="btn primary">
                <i class="fa-solid fa-plus"></i> Tambah Wilayah
            </a>
        </div>
    </x-slot>

    <div class="card" style="margin-bottom: 20px;">
        <form method="GET" action="{{ route('regions.index') }}">
            <div class="toolbar">
                <div style="flex:1; min-width:260px; position:relative;">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama desa..."
                        style="padding-left:36px;"
                    >
                    <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--slate-400);"></i>
                </div>
                <button type="submit" class="btn primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari Desa
                </button>
                @if(request('search'))
                    <a href="{{ route('regions.index') }}" class="btn">
                        <i class="fa-solid fa-rotate-left"></i> Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding: 18px 24px; border-bottom: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="font-size:16px; font-weight:800; color:var(--slate-900); margin:0;">
                    Monitoring Realisasi RTLH per Desa
                </h3>
                <p class="muted" style="margin:2px 0 0 0; font-size:12px;">Cakupan wilayah dibatasi khusus tingkat Desa/Kelurahan dengan rekap realisasi.</p>
            </div>
            <span class="badge published">
                Total {{ number_format($regions->total()) }} Desa
            </span>
        </div>

        <div class="tablewrap" style="border:none; border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th style="width: 140px;">Kode Desa</th>
                        <th>Nama Desa</th>
                        <th style="text-align: center;">Total RTLH</th>
                        <th style="text-align: center;">Sudah Terealisasi</th>
                        <th style="text-align: center;">Belum Terealisasi</th>
                        <th style="text-align: right; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($regions as $region)
                        <tr>
                            <td class="muted">{{ $regions->firstItem() + $loop->index }}</td>
                            <td>
                                <code style="background:var(--slate-100); padding:2px 6px; border-radius:4px; font-size:12px; font-weight:700;">{{ $region->code }}</code>
                            </td>
                            <td>
                                <strong style="color:var(--slate-900);">{{ $region->name }}</strong>
                                <span class="muted" style="font-size:11px; display:block;">Desa</span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background:#f1f5f9; color:#334155; font-weight:700;">
                                    {{ number_format($region->total_houses ?? 0) }} Unit
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background:#dcfce7; color:#15803d; border:1px solid #86efac; font-weight:700;">
                                    <i class="fa-solid fa-circle-check"></i> {{ number_format($region->realized_count ?? 0) }} Unit
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde047; font-weight:700;">
                                    <i class="fa-solid fa-clock"></i> {{ number_format($region->not_realized_count ?? 0) }} Unit
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('houses.index', ['region_id' => $region->id]) }}" class="btn small" style="background:#fff; border-color:var(--slate-300); color:var(--slate-700);">
                                    <i class="fa-solid fa-list-check"></i> Lihat RTLH
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:48px 24px;" class="muted">
                                Belum ada data wilayah desa.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($regions->hasPages())
    <div class="pagination-wrap">

        <div class="pagination-info">
            Halaman
            <strong>{{ $regions->currentPage() }}</strong>
            dari
            <strong>{{ $regions->lastPage() }}</strong>
        </div>

        <nav class="pagination" aria-label="Pagination">

            {{-- Previous --}}
            @if ($regions->onFirstPage())
                <span class="page-btn disabled">
                    <i class="fa-solid fa-chevron-left"></i>
                </span>
            @else
                <a href="{{ $regions->previousPageUrl() }}" class="page-btn">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
            @endif

            {{-- Page numbers --}}
            @foreach ($regions->getUrlRange(
                max(1, $regions->currentPage() - 2),
                min($regions->lastPage(), $regions->currentPage() + 2)
            ) as $page => $url)

                @if ($page == $regions->currentPage())
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
            @if ($regions->hasMorePages())
                <a href="{{ $regions->nextPageUrl() }}" class="page-btn">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            @else
                <span class="page-btn disabled">
                    <i class="fa-solid fa-chevron-right"></i>
                </span>
            @endif

        </nav>
    </div>
@endif
    </div>
</x-app-layout>