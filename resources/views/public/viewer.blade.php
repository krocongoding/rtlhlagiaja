<x-layouts.public title="Viewer & Eksplorasi Data">

    <div class="pagehead">
        <div>
            <div class="kicker">
                <i class="fa-solid fa-eye"></i> PANDUAN PENGGUNA
            </div>
            <h1>Workspace Viewer & Eksplorasi Publik</h1>
            <p class="muted">
                Akses terbuka bagi masyarakat, akademisi, dan instansi untuk menjelajahi data Rumah Tidak Layak Huni di Kota Cirebon.
            </p>
        </div>
    </div>

    <div class="grid3">
        <div class="card card-hoverable">
            <div class="stat-widget-icon emerald">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 8px;">Peta GIS Interaktif</h3>
            <p class="muted" style="font-size: 13px; line-height: 1.6; margin-bottom: 20px;">
                Visualisasikan sebaran lokasi unit rumah RTLH per kecamatan dan kelurahan di seluruh Kota Cirebon.
            </p>
            <a class="btn primary small" href="{{ route('public.map') }}">
                <i class="fa-solid fa-arrow-right"></i> Buka Peta
            </a>
        </div>

        <div class="card card-hoverable">
            <div class="stat-widget-icon blue">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 8px;">Statistik & Agregasi</h3>
            <p class="muted" style="font-size: 13px; line-height: 1.6; margin-bottom: 20px;">
                Analisa grafik tren tahunan, jumlah unit per kecamatan, serta rekapitulasi data per desa secara lengkap.
            </p>
            <a class="btn small" href="{{ route('public.statistics') }}">
                <i class="fa-solid fa-arrow-right"></i> Lihat Statistik
            </a>
        </div>

        <div class="card card-hoverable">
            <div class="stat-widget-icon purple">
                <i class="fa-solid fa-file-csv"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 800; margin-bottom: 8px;">Katalog Open Data</h3>
            <p class="muted" style="font-size: 13px; line-height: 1.6; margin-bottom: 20px;">
                Cari, filter, dan unduh dataset publik dalam format CSV atau integrasikan ke aplikasi melalui REST API.
            </p>
            <a class="btn small" href="{{ route('public.datasets') }}">
                <i class="fa-solid fa-arrow-right"></i> Akses Data
            </a>
        </div>
    </div>

    <div class="card" style="margin-top: 24px; padding: 28px;">
        <h3 style="font-size: 18px; font-weight: 800; color: var(--slate-900); margin-bottom: 12px;">
            <i class="fa-solid fa-circle-info text-sky-600" style="color:#0284c7;"></i> Mengenai Akun Petugas
        </h3>
        <p class="muted" style="line-height: 1.6; margin-bottom: 16px;">
            Bagi petugas dinas dan surveyor lapangan yang memerlukan hak akses input data survei, verifikasi, atau penerbitan data ke publik, silakan melakukan autentikasi login melalui tombol berikut:
        </p>
        <div class="actions">
            <a href="{{ route('login') }}" class="btn primary">
                <i class="fa-solid fa-right-to-bracket"></i> Masuk Akun Petugas
            </a>
            <a href="{{ route('public.home') }}" class="btn">
                <i class="fa-solid fa-house"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

</x-layouts.public>
