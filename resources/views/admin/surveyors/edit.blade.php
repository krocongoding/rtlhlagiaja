<x-layouts.app title="Edit Surveyor - {{ $surveyor->name }}">

    <div class="pagehead">
        <div>
            <div class="kicker">
                <i class="fa-solid fa-user-pen"></i> MANAJEMEN PETUGAS LAPANGAN
            </div>
            <h1>Edit Data Surveyor</h1>
            <p class="muted">
                Perbarui informasi akun, status keaktifan, atau kata sandi untuk <strong>{{ $surveyor->name }}</strong> (ID: #{{ $surveyor->id }}).
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('admin.surveyors.show', $surveyor) }}" class="btn">
                <i class="fa-solid fa-arrow-left"></i> Log History & Detail
            </a>
            <a href="{{ route('admin.surveyors.index') }}" class="btn">
                <i class="fa-solid fa-list"></i> Daftar Surveyor
            </a>
        </div>
    </div>

    <div class="card" style="max-width: 680px; margin: 0 auto; padding: 32px;">
        <h3 style="font-size: 18px; font-weight: 800; color: var(--slate-900); margin-bottom: 20px; display:flex; align-items:center; gap:10px;">
            <i class="fa-solid fa-user-gear text-blue-500" style="color:#2563eb;"></i> Form Edit Akun Surveyor
        </h3>

        <form method="POST" action="{{ route('admin.surveyors.update', $surveyor) }}">
            @csrf
            @method('PUT')

            {{-- NAMA --}}
            <div style="margin-bottom: 20px;">
                <label for="name" style="display:block; font-weight:700; margin-bottom:6px; color:var(--slate-800);">
                    Nama Lengkap Surveyor <span style="color:#ef4444;">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $surveyor->name) }}"
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
                    value="{{ old('email', $surveyor->email) }}"
                    required
                    style="width:100%;"
                >
                @error('email')
                    <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                @enderror
            </div>

            {{-- GANTI KATA SANDI (OPSIONAL) --}}
            <div style="padding: 16px; background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 8px; margin-bottom: 20px;">
                <h4 style="font-size:14px; font-weight:700; color:var(--slate-800); margin:0 0 8px 0;">
                    <i class="fa-solid fa-key" style="color:#f59e0b;"></i> Ubah Kata Sandi (Kosongkan jika tidak ingin mengubah)
                </h4>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div>
                        <label for="password" style="display:block; font-weight:600; font-size:13px; margin-bottom:4px; color:var(--slate-700);">
                            Kata Sandi Baru
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Minimal 8 karakter"
                            style="width:100%;"
                        >
                        @error('password')
                            <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" style="display:block; font-weight:600; font-size:13px; margin-bottom:4px; color:var(--slate-700);">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Ulangi kata sandi baru"
                            style="width:100%;"
                        >
                    </div>
                </div>
            </div>

            {{-- STATUS AKTIF --}}
            <div style="margin-bottom: 18px; padding: 16px; background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: 8px;">
                <label style="display:flex; align-items:center; gap:10px; cursor:pointer; font-weight:700; color:var(--slate-800); margin:0;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $surveyor->is_active) ? 'checked' : '' }} style="width:18px; height:18px;">
                    <span>Akun Surveyor Aktif</span>
                </label>
            </div>

            {{-- HAK AKSES ADMINISTRATOR (SETARA ADMIN) --}}
            <input type="hidden" name="role_settings_submitted" value="1">
            <div style="margin-bottom: 24px; padding: 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;">
                <label style="display:flex; align-items:flex-start; gap:10px; cursor:pointer; font-weight:700; color:#92400e; margin:0;">
                    <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $surveyor->hasRole('admin')) ? 'checked' : '' }} {{ $surveyor->id === auth()->id() ? 'disabled' : '' }} style="width:18px; height:18px; margin-top:2px;">
                    <div>
                        <span>Berikan Hak Akses Administrator Sistem (Setara Admin)</span>
                        <p style="font-weight:400; font-size:12px; color:#b45309; margin:4px 0 0;">
                            Pengguna ini akan memiliki wewenang penuh atas Command Center Admin, review & verifikasi data survei, publikasi, master data, dan dokumen kependudukan.
                        </p>
                        @if($surveyor->id === auth()->id())
                            <small style="color:#b91c1c; font-weight:600; display:block; margin-top:4px;">
                                * Anda sedang mengedit akun Anda sendiri. Hak administrator tidak dapat dicabut dari akun yang sedang aktif.
                            </small>
                            <input type="hidden" name="is_admin" value="1">
                        @endif
                    </div>
                </label>
            </div>

            {{-- BUTTONS --}}
            <div class="actions" style="justify-content: flex-end; gap:12px; border-top: 1px solid var(--slate-200); padding-top:20px;">
                <a href="{{ route('admin.surveyors.show', $surveyor) }}" class="btn">
                    Batal
                </a>
                <button type="submit" class="btn primary">
                    <i class="fa-solid fa-save"></i> Perbarui Data Surveyor
                </button>
            </div>
        </form>
    </div>

</x-layouts.app>
