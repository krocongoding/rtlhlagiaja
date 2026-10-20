<x-layouts.app title="Basis Data CPBM | RTLH Cirebon">

    <div class="pagehead">
        <div>
            <div class="kicker">
                KEPENDUDUKAN & CALON PENERIMA BANTUAN
            </div>
            <h1>Basis Data Calon Penerima Bantuan (CPBM)</h1>
            <p class="muted">
                Daftar warga calon penerima bantuan perbaikan RTLH. Lakukan pendaftaran CPBM di sini sebelum mengisi data survei fisik rumah.
            </p>
        </div>

        <div class="actions">
            <button type="button" class="btn primary" onclick="openCpbmModal()" style="display:inline-flex; align-items:center; gap:6px;">
                <span>+ Input CPBM Baru</span>
            </button>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="alert ok" style="margin-bottom: 20px;">
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="alert err" style="margin-bottom: 20px;">
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- KARTU STATISTIK RINGKAS --}}
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="card" style="padding: 16px 20px;">
            <div class="muted" style="font-size: 11px; font-weight: 800; text-transform: uppercase;">Total CPBM Terdata</div>
            <div style="font-size: 26px; font-weight: 900; color: var(--slate-900); margin-top: 4px;">{{ number_format($totalCpbm) }}</div>
            <div class="muted" style="font-size: 11px; margin-top: 2px;">Tercatat di Kota Cirebon</div>
        </div>

        <div class="card" style="padding: 16px 20px; border-left: 4px solid #10b981;">
            <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #047857;">Sudah Disurvei Kondisi Rumah</div>
            <div style="font-size: 26px; font-weight: 900; color: #10b981; margin-top: 4px;">{{ number_format($sudahSurveiCount) }}</div>
            <div class="muted" style="font-size: 11px; margin-top: 2px;">Telah memiliki data fisik rumah</div>
        </div>

        <div class="card" style="padding: 16px 20px; border-left: 4px solid #f59e0b;">
            <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #b45309;">Belum Disurvei (Menunggu)</div>
            <div style="font-size: 26px; font-weight: 900; color: #f59e0b; margin-top: 4px;">{{ number_format($belumSurveiCount) }}</div>
            <div class="muted" style="font-size: 11px; margin-top: 2px;">Siap diinput kondisi fisiknya</div>
        </div>
    </div>

    {{-- FILTER & PENCARIAN --}}
    <div class="card" style="padding: 18px 20px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('cpbm.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; align-items: flex-end;">
            <div>
                <label style="display:block; font-size:12px; font-weight:700; color:var(--slate-700); margin-bottom:4px;">Cari NIK / Nama / Alamat</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik NIK atau nama..." style="width:100%; font-size:13px; padding:8px 12px; border:1px solid var(--slate-300); border-radius:8px;">
            </div>

            <div>
                <label style="display:block; font-size:12px; font-weight:700; color:var(--slate-700); margin-bottom:4px;">Kecamatan</label>
                <select name="district_id" id="filterDistrictSelect" onchange="updateFilterVillages(this.value)" style="width:100%; font-size:13px; padding:8px 12px; border:1px solid var(--slate-300); border-radius:8px; background:#fff;">
                    <option value="">Semua Kecamatan</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}" {{ request('district_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block; font-size:12px; font-weight:700; color:var(--slate-700); margin-bottom:4px;">Kelurahan</label>
                <select name="region_id" id="filterVillageSelect" style="width:100%; font-size:13px; padding:8px 12px; border:1px solid var(--slate-300); border-radius:8px; background:#fff;">
                    <option value="">Semua Kelurahan</option>
                    @foreach($villages as $v)
                        <option value="{{ $v->id }}" data-parent="{{ $v->parent_id }}" {{ request('region_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block; font-size:12px; font-weight:700; color:var(--slate-700); margin-bottom:4px;">Status Survei Fisik</label>
                <select name="survey_status" style="width:100%; font-size:13px; padding:8px 12px; border:1px solid var(--slate-300); border-radius:8px; background:#fff;">
                    <option value="">Semua Status</option>
                    <option value="belum" {{ request('survey_status') == 'belum' ? 'selected' : '' }}>Belum Disurvei</option>
                    <option value="sudah" {{ request('survey_status') == 'sudah' ? 'selected' : '' }}>Sudah Disurvei</option>
                </select>
            </div>

            <div style="display:flex; gap:8px;">
                <button type="submit" class="btn primary" style="flex:1; padding:8px 16px; font-size:13px;">Terapkan</button>
                @if(request()->anyFilled(['search', 'district_id', 'region_id', 'survey_status']))
                    <a href="{{ route('cpbm.index') }}" class="btn" style="padding:8px 12px; font-size:13px;">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABEL BASIS DATA CPBM --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="overflow-x:auto;">
            <table class="table" style="width:100%; margin:0;">
                <thead>
                    <tr>
                        <th style="width:50px;">No</th>
                        <th>NIK</th>
                        <th>Nama Kepala Keluarga</th>
                        <th>Alamat / RT-RW</th>
                        <th>Kecamatan</th>
                        <th>Kelurahan</th>
                        <th style="text-align:center;">Status Survei</th>
                        <th style="text-align:right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cpbms as $index => $cpbm)
                        @php
                            $hasHouse = $cpbm->houses->isNotEmpty();
                            $targetRoute = auth()->user()->hasRole('surveyor') 
                                ? route('surveyor.rtlh.create', ['cpbm_id' => $cpbm->id]) 
                                : route('houses.create', ['cpbm_id' => $cpbm->id]);
                        @endphp
                        <tr>
                            <td>{{ $cpbms->firstItem() + $index }}</td>
                            <td style="font-family:monospace; font-weight:700;">{{ \App\Support\DataMasker::maskNik($cpbm->nik) }}</td>
                            <td style="font-weight:700; color:var(--slate-900);">{{ $cpbm->name }}</td>
                            <td>
                                {{ $cpbm->address }}
                                @if($cpbm->rt || $cpbm->rw)
                                    <span class="muted" style="font-size:11px;">(RT {{ $cpbm->rt ?? '-' }} / RW {{ $cpbm->rw ?? '-' }})</span>
                                @endif
                            </td>
                            <td>{{ $cpbm->district?->name ?? '-' }}</td>
                            <td>{{ $cpbm->region?->name ?? '-' }}</td>
                            <td style="text-align:center;">
                                @if($hasHouse)
                                    <span class="badge" style="background:#dcfce7; color:#166534; font-size:10px; font-weight:800; padding:3px 8px; border-radius:999px;">
                                        Sudah Disurvei ({{ $cpbm->houses->count() }})
                                    </span>
                                @else
                                    <span class="badge" style="background:#fef3c7; color:#92400e; font-size:10px; font-weight:800; padding:3px 8px; border-radius:999px;">
                                        Belum Disurvei
                                    </span>
                                @endif
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                @if(!$hasHouse)
                                    <a href="{{ $targetRoute }}" class="btn primary small" style="font-size:11px; padding:4px 10px; margin-right:4px;" title="Input Kondisi Fisik Rumah untuk warga ini">
                                        Input Kondisi Rumah &rarr;
                                    </a>
                                @else
                                    <a href="{{ route('houses.show', $cpbm->houses->first()) }}" class="btn small" style="font-size:11px; padding:4px 8px; margin-right:4px;">
                                        Lihat Rumah
                                    </a>
                                @endif

                                <a href="{{ route('cpbm.show', $cpbm) }}" class="btn small" style="font-size:11px; padding:4px 8px;">Detail</a>
                                <a href="{{ route('cpbm.edit', $cpbm) }}" class="btn small" style="font-size:11px; padding:4px 8px;">Edit</a>

                                <form method="POST" action="{{ route('cpbm.destroy', $cpbm) }}" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data CPBM ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn small danger" style="font-size:11px; padding:4px 8px;">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:36px; color:var(--slate-500);">
                                Belum ada data CPBM yang sesuai dengan kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cpbms->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--slate-200);">
                {{ $cpbms->links() }}
            </div>
        @endif
    </div>

    {{-- ============================================================
         MODAL CEPAT: INPUT CPBM BARU (PERSIS PROTOTYPE REV5)
         ============================================================ --}}
    <div id="cpbmModalOverlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding:16px;">
        <div style="background:#ffffff; border-radius:16px; width:100%; max-width:620px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); overflow:hidden;">
            <div style="background:#0b1329; color:#ffffff; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="margin:0; font-size:16px; font-weight:800;">Formulir Input CPBM Baru</h3>
                    <div style="font-size:11px; color:#94a3b8; margin-top:2px;">Pendaftaran Calon Penerima Bantuan Masyarakat</div>
                </div>
                <button type="button" onclick="closeCpbmModal()" style="background:none; border:none; color:#cbd5e1; font-size:20px; cursor:pointer; font-weight:700;">&times;</button>
            </div>

            <form method="POST" action="{{ route('cpbm.store') }}" style="padding:20px 24px;">
                @csrf

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:var(--slate-700); margin-bottom:4px;">
                            NIK (16 Digit) <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" name="nik" required maxlength="16" placeholder="32740..." style="width:100%; font-family:monospace; font-size:13px; padding:8px 12px; border:1px solid var(--slate-300); border-radius:8px;">
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:var(--slate-700); margin-bottom:4px;">
                            Nama Kepala Keluarga <span style="color:#ef4444;">*</span>
                        </label>
                        <input type="text" name="name" required placeholder="Nama lengkap..." style="width:100%; font-size:13px; padding:8px 12px; border:1px solid var(--slate-300); border-radius:8px;">
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block; font-size:12px; font-weight:700; color:var(--slate-700); margin-bottom:4px;">
                        Alamat (Nama Jalan / Blok) <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" name="address" required placeholder="Contoh: Jl. Pangeran Drajat Blok Melati" style="width:100%; font-size:13px; padding:8px 12px; border:1px solid var(--slate-300); border-radius:8px;">
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:var(--slate-700); margin-bottom:4px;">RT</label>
                        <input type="text" name="rt" placeholder="001" maxlength="5" style="width:100%; font-size:13px; padding:8px 12px; border:1px solid var(--slate-300); border-radius:8px;">
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:var(--slate-700); margin-bottom:4px;">RW</label>
                        <input type="text" name="rw" placeholder="002" maxlength="5" style="width:100%; font-size:13px; padding:8px 12px; border:1px solid var(--slate-300); border-radius:8px;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:20px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:var(--slate-700); margin-bottom:4px;">
                            Kecamatan <span style="color:#ef4444;">*</span>
                        </label>
                        <select name="district_id" id="modalDistrictSelect" onchange="updateModalVillages(this.value)" required style="width:100%; font-size:13px; padding:8px 12px; border:1px solid var(--slate-300); border-radius:8px; background:#fff;">
                            <option value="">Pilih Kecamatan</option>
                            @foreach($districts as $d)
                                <option value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display:block; font-size:12px; font-weight:700; color:var(--slate-700); margin-bottom:4px;">
                            Kelurahan <span style="color:#ef4444;">*</span>
                        </label>
                        <select name="region_id" id="modalVillageSelect" required style="width:100%; font-size:13px; padding:8px 12px; border:1px solid var(--slate-300); border-radius:8px; background:#fff;">
                            <option value="">Pilih Kecamatan Dahulu</option>
                            @foreach($villages as $v)
                                <option value="{{ $v->id }}" data-parent="{{ $v->parent_id }}">{{ $v->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; border-top:1px solid var(--slate-200); padding-top:16px;">
                    <button type="button" onclick="closeCpbmModal()" class="btn" style="padding:8px 16px;">Batal</button>
                    <button type="submit" class="btn primary" style="padding:8px 20px;">Simpan Data CPBM</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const modal = document.getElementById('cpbmModalOverlay');

        function openCpbmModal() {
            modal.style.display = 'flex';
        }

        function closeCpbmModal() {
            modal.style.display = 'none';
        }

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeCpbmModal();
            }
        });

        // Filter dropdown kecamatan & kelurahan
        function updateFilterVillages(districtId) {
            const villageSelect = document.getElementById('filterVillageSelect');
            const options = villageSelect.querySelectorAll('option[data-parent]');

            options.forEach(opt => {
                if (!districtId || opt.getAttribute('data-parent') === districtId) {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                    if (opt.selected) {
                        villageSelect.value = '';
                    }
                }
            });
        }

        // Modal dropdown kecamatan & kelurahan
        function updateModalVillages(districtId) {
            const villageSelect = document.getElementById('modalVillageSelect');
            const options = villageSelect.querySelectorAll('option[data-parent]');
            let hasVisible = false;

            options.forEach(opt => {
                if (opt.getAttribute('data-parent') === districtId) {
                    opt.style.display = '';
                    hasVisible = true;
                } else {
                    opt.style.display = 'none';
                }
            });

            if (!hasVisible) {
                villageSelect.value = '';
            }
        }
    </script>
    @endpush

</x-layouts.app>
