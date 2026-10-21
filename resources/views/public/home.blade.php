<x-layouts.public title="Beranda">

    {{-- HERO SECTION --}}
    <section class="hero home-hero" id="heroSection">
        <div class="hero-glow"></div>

        <div>
            <div class="kicker">
                <span class="kicker-dot"></span>
                PORTAL RESMI TERAS RUTILAHU KABUPATEN CIREBON
            </div>

            <h1>
                Basis Data & WebGIS <br>
                <span class="gradient-text">Teras Rutilahu Cirebon</span>
            </h1>

            <p class="lead">
                Platform terpadu pemetaan geospasial, inventarisasi kondisi fisik, dan transparansi data Rumah Tidak Layak Huni (RTLH) untuk percepatan bantuan perumahan di Kabupaten Cirebon.
            </p>

            <div class="actions">
                <a class="btn primary large" href="{{ route('public.map') }}">
                    <i class="fa-solid fa-map-location-dot"></i>
                    Buka Peta Interaktif
                </a>

                <a class="btn large" href="{{ route('public.datasets') }}">
                    <i class="fa-solid fa-file-csv"></i>
                    Katalog & Download
                </a>

                <a class="btn large" href="{{ route('public.statistics') }}">
                    <i class="fa-solid fa-chart-line"></i>
                    Statistik
                </a>
            </div>

            {{-- HERO INTERACTIVE QUICK SEARCH --}}
            <div style="margin-top:20px; max-width:520px;">
                <form action="{{ route('public.map') }}" method="GET" style="display:flex; gap:8px; background:rgba(255,255,255,0.08); padding:5px 8px; border-radius:14px; border:1px solid rgba(255,255,255,0.18); backdrop-filter:blur(10px);">
                </form>
            </div>
        </div>

        {{-- HERO METRIC CARD --}}
        <div>
            <div class="hero-stats-card">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:16px;">
                    <div class="stat-label" style="color:#94a3b8;">DATA TERPUBLIKASI</div>
                    <span class="badge published">
                        <i class="fa-solid fa-circle-check"></i> Terverifikasi
                    </span>
                </div>

                <div class="stat" style="color:#ffffff; font-size:48px;">
                    {{ number_format($total) }}
                </div>

                <p style="color:#cbd5e1; font-size:14px; margin-bottom:20px; line-height:1.6;">
                    Unit rumah tidak layak huni telah terdata lengkap dengan kondisi struktur, sanitasi, utilitas, dan titik koordinat GPS.
                </p>

                <div style="border-top:1px solid rgba(255,255,255,0.1); padding-top:16px; display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:12px; color:#94a3b8;">
                        <i class="fa-solid fa-clock-rotate-left"></i> Realtime Sync
                    </span>
                    <a href="{{ route('public.map') }}" style="color:#10b981; text-decoration:none; font-size:13px; font-weight:700; display:inline-flex; align-items:center; gap:6px;">
                        Eksplorasi di Peta <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- HIGHLIGHT FITUR UTAMA --}}
    <div style="margin: 20px 0 10px;">
        <h2 style="font-size: 24px; font-weight: 800; color: var(--slate-900);">
            Layanan & Fitur Unggulan
        </h2>
        <p class="muted">Akses informasi geospasial dan data publik yang akurat, terstruktur, dan terbuka.</p>
    </div>

    <section class="grid3">
        <div class="card card-hoverable">
            <div class="stat-widget-icon emerald">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 8px; color: var(--slate-900);">
                Peta Tematik WebGIS
            </h3>
            <p class="muted" style="line-height: 1.6; margin-bottom: 16px;">
                Eksplorasi persebaran spasial rumah RTLH per kecamatan dan desa dengan filter interaktif dan detail koordinat.
            </p>
            <a href="{{ route('public.map') }}" class="btn small primary" style="width: fit-content;">
                Buka WebGIS <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="card card-hoverable">
            <div class="stat-widget-icon blue">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 8px; color: var(--slate-900);">
                Dashboard Statistik
            </h3>
            <p class="muted" style="line-height: 1.6; margin-bottom: 16px;">
                Visualisasi grafik tren tahunan, rekapitulasi jumlah unit per kecamatan, dan analisa komponen kerusakan rumah.
            </p>
            <a href="{{ route('public.statistics') }}" class="btn small" style="width: fit-content;">
                Lihat Analitik <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="card card-hoverable">
            <div class="stat-widget-icon purple">
                <i class="fa-solid fa-cloud-arrow-down"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 8px; color: var(--slate-900);">
                Open Data & API
            </h3>
            <p class="muted" style="line-height: 1.6; margin-bottom: 16px;">
                Unduh dataset lengkap dalam format CSV terstruktur atau integrasikan dengan REST API GeoJSON untuk riset & analitik.
            </p>
            <a href="{{ route('public.datasets') }}" class="btn small" style="width: fit-content;">
                Unduh Data <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </section>

    {{-- ALUR KERJA PENANGANAN RTLH --}}
    <section class="card" style="margin-top: 40px; padding: 36px;">
        <div style="text-align: center; max-width: 680px; margin: 0 auto 36px;">
            <div class="kicker" style="margin-bottom: 10px;">
                <i class="fa-solid fa-diagram-project"></i> TATA KELOLA DATA
            </div>
            <h2 style="font-size: 26px; font-weight: 800; color: var(--slate-900);">
                Alur Pendataan & Publikasi RTLH
            </h2>
            <p class="muted">Setiap data melalui proses verifikasi berjenjang guna menjamin validitas dan ketepatan sasaran penerima manfaat.</p>
        </div>

        <div class="grid4">
            <div style="text-align: center; position: relative;">
                <div style="width: 56px; height: 56px; border-radius: 16px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 16px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);">
                    <i class="fa-solid fa-street-view"></i>
                </div>
                <div style="font-size: 12px; font-weight: 800; color: #0284c7; margin-bottom: 4px;">TAHAP 1</div>
                <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 6px; color: var(--slate-900);">Survei Lapangan</h4>
                <p class="muted" style="font-size: 13px;">Surveyor mendatangi lokasi, mengambil titik koordinat GPS, foto, dan input kondisi fisik.</p>
            </div>

            <div style="text-align: center; position: relative;">
                <div style="width: 56px; height: 56px; border-radius: 16px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 16px; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.2);">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div style="font-size: 12px; font-weight: 800; color: #d97706; margin-bottom: 4px;">TAHAP 2</div>
                <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 6px; color: var(--slate-900);">Verifikasi Admin</h4>
                <p class="muted" style="font-size: 13px;">Pemeriksaan kelengkapan dokumen administratif, indikator kerusakan, dan validasi data.</p>
            </div>

            <div style="text-align: center; position: relative;">
                <div style="width: 56px; height: 56px; border-radius: 16px; background: var(--primary-100); color: var(--primary-700); display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 16px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);">
                    <i class="fa-solid fa-globe"></i>
                </div>
                <div style="font-size: 12px; font-weight: 800; color: var(--primary-700); margin-bottom: 4px;">TAHAP 3</div>
                <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 6px; color: var(--slate-900);">Publikasi Spasial</h4>
                <p class="muted" style="font-size: 13px;">Data yang lolos verifikasi diterbitkan ke peta publik dan katalog open data Kabupaten Cirebon.</p>
            </div>

            <div style="text-align: center; position: relative;">
                <div style="width: 56px; height: 56px; border-radius: 16px; background: #ede9fe; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 16px; box-shadow: 0 4px 12px rgba(124, 58, 237, 0.2);">
                    <i class="fa-solid fa-hand-holding-hand"></i>
                </div>
                <div style="font-size: 12px; font-weight: 800; color: #7c3aed; margin-bottom: 4px;">TAHAP 4</div>
                <h4 style="font-size: 16px; font-weight: 700; margin-bottom: 6px; color: var(--slate-900);">Program Bantuan</h4>
                <p class="muted" style="font-size: 13px;">Rujukan utama bagi instansi pemerintah, CSR, dan lembaga sosial untuk penyaluran bantuan.</p>
            </div>
        </div>
    </section>

    {{-- CITIZEN FAQ SECTION --}}
    <section class="card" style="margin-top: 40px; padding: 36px;">
        <div style="text-align: center; max-width: 680px; margin: 0 auto 32px;">
            <div class="kicker" style="margin-bottom: 8px;">
                <i class="fa-solid fa-circle-question"></i> INFORMASI & BANTUAN
            </div>
            <h2 style="font-size: 26px; font-weight: 800; color: var(--slate-900);">
                Pertanyaan yang Sering Diajukan (FAQ)
            </h2>
            <p class="muted">Panduan dan transparansi mengenai program pendataan Rumah Tidak Layak Huni.</p>
        </div>

        <div style="display:flex; flex-direction:column; gap:12px; max-width:840px; margin:0 auto;">
            <details style="background:var(--slate-50); border:1px solid var(--slate-200); border-radius:var(--radius-lg); padding:16px 20px; cursor:pointer;">
                <summary style="font-weight:700; color:var(--slate-900); font-size:15px; outline:none;">
                    Apa saja kriteria rumah yang dikategorikan sebagai RTLH?
                </summary>
                <p class="muted" style="margin-top:12px; font-size:13.5px; line-height:1.7;">
                    Sesuai standar teknis Kementerian PUPR dan Dinas Perumahan, kriteria RTLH mencakup tiga aspek pokok: <strong>Keselamatan Bangunan</strong> (struktur pondasi, tiang kolom, balok pengikat, dan rangka atap rusak/lapuk), <strong>Kesehatan & Sanitasi</strong> (pencahayaan/ventilasi minim, belum memiliki jamban keluarga sehat atau tangki septik), serta <strong>Kecukupan Ruang</strong> (luas lantai kurang dari standar 9 m² per jiwa).
                </p>
            </details>

            <details style="background:var(--slate-50); border:1px solid var(--slate-200); border-radius:var(--radius-lg); padding:16px 20px; cursor:pointer;">
                <summary style="font-weight:700; color:var(--slate-900); font-size:15px; outline:none;">
                    Bagaimana alur penyaluran program bantuan bedah rumah?
                </summary>
                <p class="muted" style="margin-top:12px; font-size:13.5px; line-height:1.7;">
                    Tahapan diawali dengan survei verifikasi faktual oleh petugas lapangan bersertifikat, pemeriksaan administratif dokumen hak milik/kependudukan oleh Dinas, penetapan Surat Keputusan (SK) Calon Penerima Bantuan Menetap (CPBM), hingga penyaluran stimulan bahan bangunan secara transparan.
                </p>
            </details>

            <details style="background:var(--slate-50); border:1px solid var(--slate-200); border-radius:var(--radius-lg); padding:16px 20px; cursor:pointer;">
                <summary style="font-weight:700; color:var(--slate-900); font-size:15px; outline:none;">
                    Mengapa identitas pemilik rumah disamarkan di portal publik?
                </summary>
                <p class="muted" style="margin-top:12px; font-size:13.5px; line-height:1.7;">
                    Untuk mematuhi Undang-Undang Perlindungan Data Pribadi (UU PDP), identitas kependudukan seperti NIK 16 digit dan nomor kontak disamarkan, serta koordinat peta publik difuzzing secara deterministik guna mencegah penyalahgunaan data warga oleh pihak yang tidak bertanggung jawab.
                </p>
            </details>

            <details style="background:var(--slate-50); border:1px solid var(--slate-200); border-radius:var(--radius-lg); padding:16px 20px; cursor:pointer;">
                <summary style="font-weight:700; color:var(--slate-900); font-size:15px; outline:none;">
                    Bagaimana jika ada warga prasejahtera yang rumahnya belum terdata?
                </summary>
                <p class="muted" style="margin-top:12px; font-size:13.5px; line-height:1.7;">
                    Warga dapat melaporkan usulan melalui pihak RT/RW atau perangkat Desa/Kelurahan setempat agar dicatatkan dalam usulan basis data CPBM (Calon Penerima Bantuan Manfaat) pada siklus pendataan lapangan berikutnya.
                </p>
            </details>
        </div>
    </section>

    {{-- CALL TO ACTION --}}
    <section style="margin-top: 40px; background: linear-gradient(135deg, #0f172a 0%, #064e3b 100%); border-radius: var(--radius-xl); padding: 48px 36px; color: #ffffff; text-align: center; position: relative; overflow: hidden;">
        <h2 style="font-size: 28px; font-weight: 800; margin-bottom: 12px; color: #ffffff;">
            Mendukung Keterbukaan Data & Ketepatan Sasaran
        </h2>
        <p style="color: #cbd5e1; max-width: 600px; margin: 0 auto 24px; font-size: 16px; line-height: 1.6;">
            Seluruh data publik dilindungi privasinya dengan tetap menyajikan indikator geospasial esensial untuk kebutuhan perencanaan dan pembangunan daerah.
        </p>
        <div class="actions" style="justify-content: center;">
            <a class="btn primary large" href="{{ route('public.map') }}">
                <i class="fa-solid fa-location-arrow"></i> Mulai Jelajahi Peta
            </a>
            <a class="btn large" href="{{ route('public.datasets') }}" style="background: rgba(255,255,255,0.1); color:#ffffff; border-color: rgba(255,255,255,0.2);">
                <i class="fa-solid fa-database"></i> Lihat Dataset Lengkap
            </a>
        </div>
    </section>

</x-layouts.public>
