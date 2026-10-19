<x-app-layout>
    <x-slot name="header">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
            <div>
                <h1 style="font-size:1.5rem; font-weight:800; color:var(--text-main); margin:0;">
                    Data Penghuni Rumah
                </h1>
                <p style="color:var(--text-muted); font-size:0.875rem; margin:4px 0 0 0;">
                    Kode Rumah: <span class="badge badge-info font-mono">{{ $house->house_code }}</span> | Penerima: <strong>{{ $house->applicant_name }}</strong>
                </p>
            </div>
            <a href="{{ route('houses.show', $house) }}" class="btn">
                Kembali ke Detail
            </a>
        </div>
    </x-slot>

    <div style="max-width:1000px; margin:0 auto;">
        {{-- ERRORS --}}
        @if ($errors->any())
            <div style="padding:16px 20px; background:#fef2f2; border:1px solid #fecaca; border-radius:var(--radius-md); margin-bottom:24px;">
                <div style="font-weight:700; color:#991b1b; display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                    Terjadi kesalahan input:
                </div>
                <ul style="margin:0; padding-left:20px; color:#b91c1c; font-size:0.875rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('houses.occupants.update', $house) }}"
              x-data="occupantForm()">
            @csrf
            @method('PUT')

            <div class="card" style="margin-bottom:24px;">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
                    <div>
                        <div class="card-title">
                            Daftar Anggota Penghuni
                        </div>
                        <div class="card-desc">Tambahkan dan lengkapi data seluruh anggota keluarga yang menghuni rumah ini.</div>
                    </div>
                    <button type="button" @click="add()" class="btn btn-primary btn-sm">
                        + Tambah Penghuni
                    </button>
                </div>

                <div class="card-body">
                    {{-- EMPTY STATE --}}
                    <template x-if="occupants.length === 0">
                        <div style="border:2px dashed var(--border-color); border-radius:var(--radius-md); padding:40px 20px; text-align:center; background:#fafafa;">
                            <h4 style="font-size:1rem; font-weight:700; color:var(--text-main); margin-bottom:4px;">Belum Ada Data Penghuni</h4>
                            <p style="color:var(--text-muted); font-size:0.875rem; margin-bottom:16px;">Tambahkan data anggota keluarga penghuni rumah untuk melengkapi berkas.</p>
                            <button type="button" @click="add()" class="btn btn-primary">
                                Tambahkan Penghuni Pertama
                            </button>
                        </div>
                    </template>

                    {{-- OCCUPANTS LIST --}}
                    <div style="display:flex; flex-direction:column; gap:16px;">
                        <template x-for="(occupant, index) in occupants" :key="occupant.key">
                            <div style="border:1px solid var(--border-color); border-radius:var(--radius-md); padding:20px; background:var(--bg-main); transition:all 0.2s ease;">
                                <input type="hidden" :name="`occupants[${index}][id]`" x-model="occupant.id">

                                {{-- CARD HEADER --}}
                                <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; margin-bottom:16px; border-bottom:1px solid var(--border-color);">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <span class="badge badge-info" style="font-size:0.8rem; font-weight:700;">
                                            #<span x-text="index + 1"></span>
                                        </span>
                                        <span style="font-weight:700; color:var(--text-main); font-size:0.95rem;">
                                            <span x-text="occupant.relationship ? occupant.relationship : 'Anggota Keluarga Baru'"></span>
                                        </span>
                                    </div>
                                    <button type="button" @click="remove(index)" class="btn btn-danger btn-sm" title="Hapus anggota ini">
                                        Hapus
                                    </button>
                                </div>

                                <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
                                    {{-- HUBUNGAN --}}
                                    <div class="form-group">
                                        <label class="form-label required">Hubungan Keluarga</label>
                                        <input type="text"
                                               :name="`occupants[${index}][relationship]`"
                                               x-model="occupant.relationship"
                                               placeholder="Contoh: Kepala Keluarga, Istri, Anak"
                                               required
                                               class="form-input">
                                    </div>

                                    {{-- JENIS KELAMIN --}}
                                    <div class="form-group">
                                        <label class="form-label required">Jenis Kelamin</label>
                                        <select :name="`occupants[${index}][gender]`"
                                                x-model="occupant.gender"
                                                required
                                                class="form-select">
                                            <option value="">-- Pilih Jenis Kelamin --</option>
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>

                                    {{-- TAHUN LAHIR --}}
                                    <div class="form-group">
                                        <label class="form-label">Tahun Lahir</label>
                                        <input type="number"
                                               :name="`occupants[${index}][birth_year]`"
                                               x-model="occupant.birth_year"
                                               min="1900"
                                               max="2100"
                                               placeholder="Contoh: 1985"
                                               class="form-input">
                                    </div>

                                    {{-- PEKERJAAN --}}
                                    <div class="form-group">
                                        <label class="form-label">Pekerjaan</label>
                                        <input type="text"
                                               :name="`occupants[${index}][occupation]`"
                                               x-model="occupant.occupation"
                                               placeholder="Contoh: Buruh Harian / Petani"
                                               class="form-input">
                                    </div>

                                    {{-- PENDIDIKAN --}}
                                    <div class="form-group">
                                        <label class="form-label">Pendidikan Terakhir</label>
                                        <input type="text"
                                               :name="`occupants[${index}][education]`"
                                               x-model="occupant.education"
                                               placeholder="Contoh: SD / SMP / SMA"
                                               class="form-input">
                                    </div>

                                    {{-- KONTAK UTAMA --}}
                                    <div class="form-group" style="display:flex; align-items:flex-end; padding-bottom:8px;">
                                        <label style="display:flex; align-items:center; gap:8px; cursor:pointer; margin:0; font-size:0.875rem; font-weight:600; color:var(--text-main);">
                                            <input type="checkbox"
                                                   :name="`occupants[${index}][is_primary_contact]`"
                                                   value="1"
                                                   x-model="occupant.is_primary_contact"
                                                   style="width:18px; height:18px; accent-color:var(--primary); cursor:pointer;">
                                            <span>Jadikan Kontak Utama</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="card-footer" style="display:flex; justify-content:flex-end; gap:12px; background:var(--bg-main);">
                    <a href="{{ route('houses.show', $house) }}" class="btn">
                        Batal
                    </a>
                    <button type="submit" class="btn primary">
                        Simpan Penghuni
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function occupantForm() {
            return {
                occupants: @js(
                    $house->occupants->map(function ($occupant) {
                        return [
                            'key' => $occupant->id,
                            'id' => $occupant->id,
                            'relationship' => $occupant->relationship,
                            'gender' => $occupant->gender,
                            'birth_year' => $occupant->birth_year,
                            'occupation' => $occupant->occupation,
                            'education' => $occupant->education,
                            'is_primary_contact' => $occupant->is_primary_contact,
                        ];
                    })->values()
                ),

                add() {
                    this.occupants.push({
                        key: Date.now() + Math.random(),
                        id: null,
                        relationship: '',
                        gender: '',
                        birth_year: '',
                        occupation: '',
                        education: '',
                        is_primary_contact: false,
                    });
                },

                remove(index) {
                    this.occupants.splice(index, 1);
                }
            }
        }
    </script>
</x-app-layout>