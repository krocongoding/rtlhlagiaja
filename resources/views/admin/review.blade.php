<x-layouts.app title="Review & Verifikasi RTLH">

    <div class="pagehead">
        <div>
            <div class="kicker">
                MODUL REVIEW & APPROVAL
            </div>
            <h1>Antrian Review & Publikasi RTLH</h1>
            <p class="muted">
                Verifikasi keabsahan data lapangan dari surveyor sebelum dipublikasikan ke portal geospasial publik.
            </p>
        </div>

        <div class="actions">
            <a href="{{ route('admin.dashboard') }}" class="btn">
                Dashboard Admin
            </a>
            <a href="{{ route('houses.index') }}" class="btn">
                Semua Data
            </a>
        </div>
    </div>

    {{-- FLASH MESSAGES & VALIDATION ERRORS --}}
    @if(session('ok') || session('success'))
        <div class="alert ok" style="margin-bottom: 20px;">
            <div>{{ session('ok') ?? session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert danger" style="margin-bottom: 20px;">
            <div>
                <strong>Gagal memproses aksi:</strong>
                <ul style="margin:4px 0 0 16px; padding:0;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- TABLE DATA REVIEW --}}
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="font-size: 17px; font-weight: 800; color: var(--slate-900); margin-bottom: 2px;">
                    Antrian Data Menunggu Keputusan
                </h3>
                <p class="muted" style="font-size: 13px;">Tinjau kelengkapan kondisi fisik, foto, titik koordinat, dan skor prioritas.</p>
            </div>

            <span class="badge" style="background:#fef3c7; color:#b45309; font-weight:700;">
                {{ $houses->total() }} Data Antrian
            </span>
        </div>

        <div class="tablewrap" style="border:none; border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th style="width: 150px;">Kode RTLH</th>
                        <th>Wilayah / Kecamatan</th>
                        <th>Tahun</th>
                        <th>Status Saat Ini</th>
                        <th>Petugas Surveyor</th>
                        <th style="text-align: right; width: 280px;">Aksi Keputusan</th>
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
                                    <strong style="font-family:monospace; font-size:14px; color:var(--slate-900);">
                                        {{ $house->house_code }}
                                    </strong>
                                </div>
                            </td>

                            <td>
                                <span class="badge" style="background:var(--slate-100); color:var(--slate-700);">
                                    {{ $house->region?->name ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <span class="badge" style="background:#e0f2fe; color:#0369a1;">
                                    {{ $house->survey_year ?? '-' }}
                                </span>
                            </td>

                            <td>
                                @if($house->status === 'submitted')
                                    <span class="badge submitted">
                                        Menunggu Review
                                    </span>
                                @elseif($house->status === 'verified')
                                    <span class="badge" style="background:#ede9fe; color:#6d28d9;">
                                        Terverifikasi (Siap Publish)
                                    </span>
                                @else
                                    <span class="badge">
                                        {{ ucfirst($house->status) }}
                                    </span>
                                @endif

                                @if($house->revision_notes)
                                    <div style="margin-top:4px;">
                                        <span class="badge" style="background:#fff1f2; color:#be123c; border:1px solid #fecaca; font-size:10px; cursor:pointer;" title="Catatan sebelumnya: {{ $house->revision_notes }}">
                                            Ada Catatan Revisi
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td>
                                <div style="font-size:13px; font-weight:700; color:var(--slate-800); display:flex; align-items:center; gap:8px;">
                                    <div class="user-avatar" style="width:26px; height:26px; font-size:11px; flex-shrink:0;">
                                        {{ strtoupper(substr($house->creator?->name ?? 'P', 0, 1)) }}
                                    </div>
                                    <div>
                                        @if($house->creator)
                                            <a href="{{ route('admin.surveyors.show', $house->creator) }}" style="color:var(--slate-900); text-decoration:none;" title="Lihat Log History Surveyor">
                                                {{ $house->creator->name }}
                                                <span class="badge" style="background:var(--slate-100); color:var(--slate-600); font-family:monospace; font-size:10px; margin-left:2px;">
                                                    #{{ $house->creator->id }}
                                                </span>
                                            </a>
                                        @else
                                            <span class="muted">Petugas #{{ $house->created_by ?? '-' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td style="text-align: right;">
                                <div class="actions" style="justify-content: flex-end; gap:6px;">
                                    {{-- DETAIL --}}
                                    <a href="{{ route('houses.show', $house) }}" class="btn small" title="Tinjau Detail">
                                        Detail
                                    </a>

                                    @if($house->status === 'submitted')
                                        {{-- VERIFY --}}
                                        <form method="POST" action="{{ route('admin.review.verify', $house) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn small primary" title="Verifikasi Data">
                                                Verifikasi
                                            </button>
                                        </form>

                                        {{-- REVISION WITH NOTE --}}
                                        <button type="button" class="btn small danger" title="Kembalikan ke Surveyor dengan Catatan Revisi" onclick="openRevisionModal('{{ route('admin.review.revision', $house) }}', '{{ $house->house_code }}', @js($house->revision_notes ?? ''))">
                                            Revisi
                                        </button>

                                    @elseif($house->status === 'verified')
                                        {{-- PUBLISH --}}
                                        <form method="POST" action="{{ route('admin.review.publish', $house) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn small" style="background:#059669; color:#fff; border-color:#059669;" title="Terbitkan ke Peta Publik">
                                                Publish
                                            </button>
                                        </form>

                                        {{-- REVISION WITH NOTE --}}
                                        <button type="button" class="btn small danger" title="Kembalikan ke Surveyor dengan Catatan Revisi" onclick="openRevisionModal('{{ route('admin.review.revision', $house) }}', '{{ $house->house_code }}', @js($house->revision_notes ?? ''))">
                                            Revisi
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 48px 24px;">
                                <h4 style="font-size:16px; font-weight:700; color:var(--slate-800); margin-bottom:4px;">
                                    Semua Data Bersih & Terverifikasi
                                </h4>
                                <p class="muted" style="font-size:13px; max-width:400px; margin:0 auto;">
                                    Tidak ada data baru yang sedang menunggu antrian review verifikasi saat ini.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($houses->hasPages())
            <div style="padding: 16px 24px; border-top: 1px solid var(--slate-200); background: var(--slate-50); display:flex; justify-content:space-between; align-items:center;">
                <div class="muted" style="font-size:13px;">
                    Halaman {{ $houses->currentPage() }} dari {{ $houses->lastPage() }}
                </div>
                <div>
                    {{ $houses->links() }}
                </div>
            </div>
        @endif
    </div>

    {{-- MODAL CATATAN REVISI --}}
    <div id="revision-modal" role="dialog" aria-modal="true" aria-labelledby="revision-modal-title" style="display:none; position:fixed; inset:0; z-index:2000; padding:20px; background:rgba(15, 23, 42, 0.75); align-items:center; justify-content:center; backdrop-filter:blur(4px);">
        <div style="background:#ffffff; border-radius:var(--radius-xl); max-width:540px; width:100%; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); border:1px solid var(--slate-200); overflow:hidden;">
            <div style="padding:20px 24px; border-bottom:1px solid var(--slate-200); background:#fff1f2; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 id="revision-modal-title" style="font-size:16px; font-weight:800; color:#9f1239; margin:0;">
                        Kembalikan untuk Revisi
                    </h3>
                    <p id="revision-house-code" class="muted" style="font-size:12px; margin:2px 0 0 0; font-family:monospace; color:#be123c;"></p>
                </div>
                <button type="button" onclick="closeRevisionModal()" style="border:none; background:none; color:var(--slate-400); font-size:22px; line-height:1; cursor:pointer; width:32px; height:32px; display:flex; align-items:center; justify-content:center; border-radius:6px;">
                    &times;
                </button>
            </div>

            <form id="revision-form" method="POST" action="">
                @csrf
                <div style="padding:24px;">
                    <label for="revision_notes" style="display:block; font-size:13px; font-weight:700; color:var(--slate-800); margin-bottom:8px;">
                        Catatan Revisi / Instruksi Perbaikan <span style="color:#e11d48;">*</span>
                    </label>
                    <textarea 
                        id="revision_notes" 
                        name="revision_notes" 
                        rows="4" 
                        required 
                        minlength="5" 
                        maxlength="3000"
                        placeholder="Contoh: Foto tampak depan buram, tolong unggah ulang. Koordinat GPS perlu diperbaiki karena tidak sesuai dengan lokasi desa..."
                        style="width:100%; border-radius:var(--radius-md); border:1px solid var(--slate-300); padding:12px; font-size:13px; font-family:inherit; resize:vertical; line-height:1.5; box-sizing:border-box;"
                    ></textarea>
                </div>

                <div style="padding:16px 24px; background:var(--slate-50); border-top:1px solid var(--slate-200); display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closeRevisionModal()" class="btn">
                        Batal
                    </button>
                    <button type="submit" class="btn danger" style="background:#e11d48; border-color:#e11d48; color:#fff;">
                        Kirim Catatan Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openRevisionModal(actionUrl, houseCode, existingNotes) {
            const modal = document.getElementById('revision-modal');
            const form = document.getElementById('revision-form');
            const codeEl = document.getElementById('revision-house-code');
            const notesInput = document.getElementById('revision_notes');

            form.action = actionUrl;
            codeEl.textContent = 'Kode RTLH: ' + houseCode;
            notesInput.value = existingNotes || '';
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            setTimeout(() => notesInput.focus(), 50);
        }

        function closeRevisionModal() {
            const modal = document.getElementById('revision-modal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRevisionModal();
            }
        });
    </script>
    @endpush

</x-layouts.app>