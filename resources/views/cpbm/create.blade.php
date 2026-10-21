<x-layouts.app title="Tambah CPBM Baru | Teras Rutilahu Cirebon">

    <div class="pagehead">
        <div>
            <div class="kicker">PENDAFTARAN WARGA</div>
            <h1>Tambah Calon Penerima Bantuan (CPBM) Baru</h1>
            <p class="muted">
                Daftarkan data warga calon penerima bantuan perbaikan rumah sebelum dilakukan penilaian kondisi fisik.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('cpbm.index') }}" class="btn">
                Kembali ke Daftar
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert danger" style="margin-bottom:20px; border-left:4px solid #b91c1c;">
            <div>
                <strong>Terdapat kesalahan pengisian:</strong>
                <ul style="margin:6px 0 0 16px; font-size:13px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="card" style="max-width:760px; margin:0 auto; padding:24px 28px;">
        <form method="POST" action="{{ route('cpbm.store') }}">
            @csrf

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:var(--slate-700); margin-bottom:6px;">
                        NIK (16 Digit) <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" name="nik" value="{{ old('nik') }}" required maxlength="16" placeholder="32740..." style="width:100%; font-family:monospace; font-size:13px; padding:10px 14px; border:1px solid var(--slate-300); border-radius:8px;">
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:var(--slate-700); margin-bottom:6px;">
                        Nama Kepala Keluarga <span style="color:#ef4444;">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Nama lengkap..." style="width:100%; font-size:13px; padding:10px 14px; border:1px solid var(--slate-300); border-radius:8px;">
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:13px; font-weight:700; color:var(--slate-700); margin-bottom:6px;">
                    Alamat Lengkap (Nama Jalan / Blok) <span style="color:#ef4444;">*</span>
                </label>
                <input type="text" name="address" value="{{ old('address') }}" required placeholder="Contoh: Jl. Diponegoro Gang Melati Blok Cempaka" style="width:100%; font-size:13px; padding:10px 14px; border:1px solid var(--slate-300); border-radius:8px;">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:var(--slate-700); margin-bottom:6px;">RT</label>
                    <input type="text" name="rt" value="{{ old('rt') }}" placeholder="001" maxlength="5" style="width:100%; font-size:13px; padding:10px 14px; border:1px solid var(--slate-300); border-radius:8px;">
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:var(--slate-700); margin-bottom:6px;">RW</label>
                    <input type="text" name="rw" value="{{ old('rw') }}" placeholder="002" maxlength="5" style="width:100%; font-size:13px; padding:10px 14px; border:1px solid var(--slate-300); border-radius:8px;">
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:var(--slate-700); margin-bottom:6px;">No. Telepon / WA</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="08..." style="width:100%; font-size:13px; padding:10px 14px; border:1px solid var(--slate-300); border-radius:8px;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:var(--slate-700); margin-bottom:6px;">
                        Kecamatan <span style="color:#ef4444;">*</span>
                    </label>
                    <select name="district_id" id="createDistrictSelect" onchange="updateVillages(this.value)" required style="width:100%; font-size:13px; padding:10px 14px; border:1px solid var(--slate-300); border-radius:8px; background:#fff;">
                        <option value="">Pilih Kecamatan</option>
                        @foreach($districts as $d)
                            <option value="{{ $d->id }}" {{ old('district_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:13px; font-weight:700; color:var(--slate-700); margin-bottom:6px;">
                        Kelurahan <span style="color:#ef4444;">*</span>
                    </label>
                    <select name="region_id" id="createVillageSelect" required style="width:100%; font-size:13px; padding:10px 14px; border:1px solid var(--slate-300); border-radius:8px; background:#fff;">
                        <option value="">Pilih Kecamatan Dahulu</option>
                        @foreach($villages as $v)
                            <option value="{{ $v->id }}" data-parent="{{ $v->parent_id }}" {{ old('region_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom:24px;">
                <label style="display:block; font-size:13px; font-weight:700; color:var(--slate-700); margin-bottom:6px;">
                    Catatan Tambahan (Opsional)
                </label>
                <textarea name="notes" rows="2" placeholder="Keterangan kondisi khusus penerima bantuan..." style="width:100%; font-size:13px; padding:10px 14px; border:1px solid var(--slate-300); border-radius:8px;">{{ old('notes') }}</textarea>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:12px; border-top:1px solid var(--slate-200); padding-top:20px;">
                <a href="{{ route('cpbm.index') }}" class="btn">Batal</a>
                <button type="submit" class="btn primary">Simpan Data CPBM</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function updateVillages(districtId) {
            const villageSelect = document.getElementById('createVillageSelect');
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

        // Run on page load if old value exists
        const initDistrict = document.getElementById('createDistrictSelect').value;
        if (initDistrict) {
            updateVillages(initDistrict);
        }
    </script>
    @endpush

</x-layouts.app>
