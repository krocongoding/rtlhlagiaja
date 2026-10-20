<x-layouts.app title="Tambah Surveyor Baru">

    <div class="pagehead">
        <div>
            <div class="kicker">
                <i class="fa-solid fa-user-plus"></i> MANAJEMEN PETUGAS LAPANGAN
            </div>
            <h1>Tambah Surveyor Baru</h1>
            <p class="muted">
                Daftarkan akun petugas baru yang bertugas mengumpulkan & menginput data rumah tidak layak huni di lapangan.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('admin.surveyors.index') }}" class="btn">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar
            </a>
        </div>
    </div>

    <div class="card" style="max-width: 680px; margin: 0 auto; padding: 32px;">
        <h3 style="font-size: 18px; font-weight: 800; color: var(--slate-900); margin-bottom: 20px; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-id-card text-blue-500" style="color:#2563eb;"></i> Form Pendaftaran Surveyor
        </h3>

        <form method="POST" action="{{ route('admin.surveyors.store') }}">
            @csrf

            {{-- NAMA --}}
            <div style="margin-bottom: 20px;">
                <label for="name" style="display:block; font-weight:700; margin-bottom:6px; color:var(--slate-800);">
                    Nama Lengkap Surveyor <span style="color:#ef4444;">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Contoh: Budi Santoso, S.T."
                    required
                    style="width:100%;"
                >
                @error('name')
                    <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- EMAIL --}}
            <div style="margin-bottom: 20px;">
                <label for="email" style="display:block; font-weight:700; margin-bottom:6px; color:var(--slate-800);">
                    Alamat Email (Username Login) <span style="color:#ef4444;">*</span>
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="Contoh: budi.surveyor@cirebon-rtlh.test"
                    required
                    style="width:100%;"
                >
                @error('email')
                    <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- KATA SANDI --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom: 20px;">
                <div>
                    <label for="password" style="display:block; font-weight:700; margin-bottom:6px; color:var(--slate-800);">
                        Kata Sandi <span style="color:#ef4444;">*</span>
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Minimal 8 karakter"
                        required
                        style="width:100%;"
                    >
                    @error('password')
                        <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" style="display:block; font-weight:700; margin-bottom:6px; color:var(--slate-800);">
                        Konfirmasi Kata Sandi <span style="color:#ef4444;">*</span>
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        placeholder="Ulangi kata sandi"
                        required
                        style="width:100%;"
                    >
                </div>
            </div>

            {{-- STATUS AKTIF --}}
            <div style="margin-bottom: 18px; padding: 16px; background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 8px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:700; color:var(--slate-800); margin:0;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }} style="width:18px; height:18px;">
                    <span>Aktifkan Akun Petugas Sekarang</span>
                </label>
                <p class="muted" style="margin-top:4px; margin-bottom:0; font-size:12px; padding-left:28px;">
                    Jika diaktifkan, petugas dapat langsung login dan menginput data survei RTLH di portal.
                </p>
            </div>

            {{-- HAK AKSES ADMINISTRATOR (OPSIONAL) --}}
            <div style="margin-bottom: 24px; padding: 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;">
                <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer; font-weight:700; color:#92400e; margin:0;">
                    <input type="checkbox" name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }} style="width:18px; height:18px; margin-top:2px;">
                    <div>
                        <span>Berikan Hak Akses Administrator Sistem (Setara Admin)</span>
                        <p style="font-weight:400; font-size:12px; color:#b45309; margin:4px 0 0;">
                            Centang opsi ini jika petugas ini juga bertindak sebagai Administrator (dapat memverifikasi/mempublikasikan data RTLH dan mengelola akun).
                        </p>
                    </div>
                </label>
            </div>

            {{-- BUTTONS --}}
            <div class="actions" style="justify-content: flex-end; gap:12px; border-top: 1px solid var(--slate-200); padding-top:20px;">
                <a href="{{ route('admin.surveyors.index') }}" class="btn">
                    Batal
                </a>
                <button type="submit" class="btn primary">
                    <i class="fa-solid fa-save"></i> Simpan & Daftarkan Surveyor
                </button>
            </div>
        </form>
    </div>

</x-layouts.app>
