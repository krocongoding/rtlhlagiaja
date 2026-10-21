<x-layouts.app title="Detail CPBM: {{ $cpbm->name }} | Teras Rutilahu Cirebon">

    <div class="pagehead">
        <div>
            <div class="kicker">DETAIL CALON PENERIMA BANTUAN</div>
            <h1>{{ $cpbm->name }}</h1>
            <p class="muted">
                NIK: <strong style="font-family:monospace;">{{ $cpbm->nik }}</strong> &bull; Terdaftar di {{ $cpbm->region?->name ?? '-' }}, Kec. {{ $cpbm->district?->name ?? '-' }}
            </p>
        </div>

        <div class="actions" style="display:flex; gap:10px;">
            @php
                $targetRoute = auth()->user()->hasRole('surveyor') 
                    ? route('surveyor.rtlh.create', ['cpbm_id' => $cpbm->id]) 
                    : route('houses.create', ['cpbm_id' => $cpbm->id]);
            @endphp
            <a href="{{ $targetRoute }}" class="btn primary">
                + Input Kondisi Rumah
            </a>
            <a href="{{ route('cpbm.edit', $cpbm) }}" class="btn">
                Edit Data
            </a>
            <a href="{{ route('cpbm.index') }}" class="btn">
                Kembali
            </a>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1.4fr; gap:24px; align-items:start;">
        {{-- KARTU IDENTITAS CPBM --}}
        <div class="card" style="padding:24px;">
            <div style="font-size:12px; font-weight:800; text-transform:uppercase; color:var(--primary-700); margin-bottom:14px; letter-spacing:0.5px;">
                Informasi Kependudukan & Wilayah
            </div>

            <table style="width:100%; font-size:13px; border-collapse:collapse;">
                <tbody>
                    <tr style="border-bottom:1px solid var(--slate-100);">
                        <td style="padding:10px 0; color:var(--slate-500); width:40%;">NIK</td>
                        <td style="padding:10px 0; font-family:monospace; font-weight:700;">{{ $cpbm->nik }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--slate-100);">
                        <td style="padding:10px 0; color:var(--slate-500);">Nama Kepala Keluarga</td>
                        <td style="padding:10px 0; font-weight:700;">{{ $cpbm->name }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--slate-100);">
                        <td style="padding:10px 0; color:var(--slate-500);">Alamat Jalan / Blok</td>
                        <td style="padding:10px 0;">{{ $cpbm->address }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--slate-100);">
                        <td style="padding:10px 0; color:var(--slate-500);">RT / RW</td>
                        <td style="padding:10px 0;">RT {{ $cpbm->rt ?? '-' }} / RW {{ $cpbm->rw ?? '-' }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--slate-100);">
                        <td style="padding:10px 0; color:var(--slate-500);">Kecamatan</td>
                        <td style="padding:10px 0; font-weight:600;">{{ $cpbm->district?->name ?? '-' }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--slate-100);">
                        <td style="padding:10px 0; color:var(--slate-500);">Kelurahan</td>
                        <td style="padding:10px 0; font-weight:600;">{{ $cpbm->region?->name ?? '-' }}</td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--slate-100);">
                        <td style="padding:10px 0; color:var(--slate-500);">Telepon / Kontak</td>
                        <td style="padding:10px 0;">{{ $cpbm->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:var(--slate-500);">Didaftarkan Oleh</td>
                        <td style="padding:10px 0;">{{ $cpbm->creator?->name ?? 'Sistem' }} ({{ $cpbm->created_at->format('d M Y, H:i') }})</td>
                    </tr>
                </tbody>
            </table>

            @if($cpbm->notes)
                <div style="margin-top:16px; padding:12px; background:var(--slate-50); border-radius:8px; font-size:12px; color:var(--slate-600);">
                    <strong>Catatan:</strong> {{ $cpbm->notes }}
                </div>
            @endif
        </div>

        {{-- RIWAYAT SURVEI KONDISI RUMAH TERKAIT --}}
        <div class="card" style="padding:24px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <div style="font-size:12px; font-weight:800; text-transform:uppercase; color:var(--slate-700); letter-spacing:0.5px;">
                    Data Rumah RTLH Terkait
                </div>
                <span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:11px; font-weight:700;">
                    {{ $cpbm->houses->count() }} Terdaftar
                </span>
            </div>

            @forelse($cpbm->houses as $house)
                <div style="border:1px solid var(--slate-200); border-radius:12px; padding:16px; margin-bottom:12px; background:#ffffff;">
                    <div style="display:flex; justify-content:space-between; align-items:start; gap:10px;">
                        <div>
                            <div style="font-size:14px; font-weight:800; color:var(--primary-700);">
                                {{ $house->house_code }}
                            </div>
                            <div class="muted" style="font-size:12px; margin-top:2px;">
                                Tahun Survei: {{ $house->survey_year }} &bull; Luas: {{ $house->area_m2 ? $house->area_m2 . ' m²' : '-' }}
                            </div>
                        </div>

                        <div>
                            @if($house->realization_status === 'realized')
                                <span class="badge" style="background:#dcfce7; color:#166534; font-size:10px; font-weight:800; padding:2px 8px; border-radius:6px;">
                                    Terealisasi
                                </span>
                            @else
                                <span class="badge" style="background:#fef3c7; color:#92400e; font-size:10px; font-weight:800; padding:2px 8px; border-radius:6px;">
                                    Belum Terealisasi
                                </span>
                            @endif
                        </div>
                    </div>

                    <div style="margin-top:12px; display:flex; justify-content:space-between; align-items:center; font-size:12px;">
                        <span class="muted">Status: <strong style="text-transform:uppercase;">{{ $house->status }}</strong></span>
                        <a href="{{ route('houses.show', $house) }}" class="btn small primary" style="font-size:11px; padding:4px 10px;">
                            Lihat Detail Rumah &rarr;
                        </a>
                    </div>
                </div>
            @empty
                <div style="text-align:center; padding:36px 16px; color:var(--slate-400); background:var(--slate-50); border-radius:12px; border:2px dashed var(--slate-200);">
                    <div style="font-size:32px; margin-bottom:8px;">🏠</div>
                    <div style="font-weight:700; color:var(--slate-700); font-size:14px;">Belum ada data kondisi rumah</div>
                    <p class="muted" style="font-size:12px; margin-top:4px; max-width:320px; margin-left:auto; margin-right:auto;">
                        Warga ini belum dilakukan survei fisik rumah. Klik tombol di bawah untuk mulai menginput kondisi fisik rumah.
                    </p>
                    <a href="{{ $targetRoute }}" class="btn primary small" style="margin-top:12px; display:inline-block;">
                        Input Kondisi Fisik Rumah Sekarang
                    </a>
                </div>
            @endforelse
        </div>
    </div>

</x-layouts.app>
