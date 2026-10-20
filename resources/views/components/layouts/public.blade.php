@props([
    'title' => 'Portal RTLH Kabupaten Cirebon',
])

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal Data dan Sistem Informasi Geografis Rumah Tidak Layak Huni (RTLH) Kabupaten Cirebon">
    <meta name="theme-color" content="#0f172a">

    <title>{{ $title }} | Portal RTLH Kabupaten Cirebon</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome / Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- App Stylesheet -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('head')

    {{-- Anti-FOUC: apply dark class before first paint --}}
    <script>if(localStorage.getItem('rtlh-dark-mode')==='1'){document.documentElement.classList.add('dark');}</script>
</head>

<body>

    {{-- TOPBAR NAVIGATION — PORTAL PUBLIK --}}
    <header class="topbar">
        <div class="container nav">

            {{-- LEFT: LOGO & BRANDING --}}
            <a href="{{ route('public.home') }}" class="brand-wrapper">
                <div class="lapa-brand-logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo RTLH">
                </div>
                <div class="brand-info">
                    <span class="brand-title">
                        rtlh<span style="color:#38bdf8;">.</span>cirebon
                    </span>
                    <span class="brand-subtitle">
                        Portal Informasi Publik
                    </span>
                </div>
            </a>

            {{-- CENTER: PUBLIC NAVIGATION LINKS --}}
            <nav class="nav-center">
                <a href="{{ route('public.home') }}" class="lapa-nav-link {{ request()->routeIs('public.home') ? 'active' : '' }}">
                    <i class="fa-solid fa-house" style="font-size:12px; opacity:0.8;"></i>
                    <span>Beranda</span>
                </a>

                {{-- Dropdown: WebGIS --}}
                <div class="lapa-dropdown">
                    <a href="{{ route('public.map') }}" class="lapa-nav-link {{ request()->routeIs('public.map') ? 'active' : '' }}">
                        <i class="fa-solid fa-map-location-dot" style="font-size:12px; opacity:0.8;"></i>
                        <span>WebGIS</span>
                        <i class="fa-solid fa-chevron-down" style="font-size:10px; opacity:0.7;"></i>
                    </a>
                    <div class="lapa-dropdown-menu">
                        <a href="{{ route('public.map') }}" class="lapa-dropdown-item">
                            <i class="fa-solid fa-map" style="color:#38bdf8; width:16px;"></i>
                            <span>Peta GIS Tematik</span>
                        </a>
                        <a href="{{ route('public.map') }}#heatmap" class="lapa-dropdown-item">
                            <i class="fa-solid fa-fire-flame-curved" style="color:#f43f5e; width:16px;"></i>
                            <span>Heatmap Kerentanan</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('public.statistics') }}" class="lapa-nav-link {{ request()->routeIs('public.statistics') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-pie" style="font-size:12px; opacity:0.8;"></i>
                    <span>Statistik</span>
                </a>

                <a href="{{ route('public.datasets') }}" class="lapa-nav-link {{ request()->routeIs('public.datasets') ? 'active' : '' }}">
                    <i class="fa-solid fa-database" style="font-size:12px; opacity:0.8;"></i>
                    <span>Unduh CSV</span>
                </a>
            </nav>

            {{-- RIGHT: SEARCH PILL, CTA BUTTON, AND THEME TOGGLE --}}
            <div style="display:flex; align-items:center; gap:12px;">

                {{-- Search Pill --}}
                <a href="{{ route('public.datasets') }}" class="lapa-search-pill" title="Cari data RTLH">
                    <i class="fa-solid fa-magnifying-glass" style="font-size:12px;"></i>
                    <span class="search-text">Search...</span>
                    <span class="search-badge">⌘K</span>
                </a>

                {{-- CTA: MASUK PETUGAS ATAU KE WORKSPACE --}}
                @auth
                    <a href="{{ route('dashboard') }}" class="lapa-btn-workspace" title="Masuk ke Panel Pengelolaan Petugas">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span>Ke Workspace</span>
                        <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="lapa-btn-signin" title="Login untuk Petugas Surveyor dan Admin">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        <span>Masuk Petugas</span>
                    </a>
                @endauth

                {{-- DARK MODE TOGGLE --}}
                <button type="button" class="btn-theme-toggle" onclick="toggleDarkMode()" id="darkToggleBtn" title="Ganti tema">
                    <i class="fa-solid fa-moon" id="darkToggleIcon"></i>
                </button>

                {{-- MOBILE HAMBURGER TOGGLE --}}
                <button type="button" class="lapa-mobile-toggle" onclick="toggleMobileNav()" title="Buka Menu Navigasi">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

        </div>

        {{-- MOBILE COLLAPSIBLE DROPDOWN MENU FOR PUBLIC --}}
        <div id="lapaMobileMenu" class="lapa-mobile-menu">
            <a href="{{ route('public.home') }}" class="lapa-mobile-link {{ request()->routeIs('public.home') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i> Beranda Publik
            </a>
            <a href="{{ route('public.map') }}" class="lapa-mobile-link {{ request()->routeIs('public.map') ? 'active' : '' }}">
                <i class="fa-solid fa-map-location-dot"></i> Peta WebGIS Tematik
            </a>
            <a href="{{ route('public.map') }}#heatmap" class="lapa-mobile-link">
                <i class="fa-solid fa-fire-flame-curved"></i> Heatmap Kerentanan
            </a>
            <a href="{{ route('public.statistics') }}" class="lapa-mobile-link {{ request()->routeIs('public.statistics') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Statistik & Grafik
            </a>
            <a href="{{ route('public.datasets') }}" class="lapa-mobile-link {{ request()->routeIs('public.datasets') ? 'active' : '' }}">
                <i class="fa-solid fa-database"></i> Katalog Open Data
            </a>

            @auth
                <div style="padding-top:10px; border-top:1px solid rgba(255,255,255,0.08); margin-top:8px;">
                    <a href="{{ route('dashboard') }}" class="lapa-btn-workspace" style="width:100%; justify-content:center;">
                        <i class="fa-solid fa-gauge-high"></i> Buka Workspace Petugas
                    </a>
                </div>
            @else
                <div style="padding-top:10px; border-top:1px solid rgba(255,255,255,0.08); margin-top:8px;">
                    <a href="{{ route('login') }}" class="lapa-btn-signin" style="width:100%; justify-content:center;">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i> Masuk Petugas
                    </a>
                </div>
            @endauth
        </div>
    </header>

    {{-- MAIN CONTENT WRAPPER --}}
    <main class="container fade-in" style="flex:1; padding-top:12px; padding-bottom:48px;">
        {{-- FLASH MESSAGES --}}
        @if(session('ok') || session('success'))
            <div class="alert ok" style="margin-top:16px;">
                <i class="fa-solid fa-circle-check fa-lg"></i>
                <div>{{ session('ok') ?? session('success') }}</div>
            </div>
        @endif

        @if(session('error') || session('err'))
            <div class="alert err" style="margin-top:16px;">
                <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                <div>{{ session('error') ?? session('err') }}</div>
            </div>
        @endif

        @if(session('status'))
            <div class="alert info" style="margin-top:16px;">
                <i class="fa-solid fa-circle-info fa-lg"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif

        {{ $slot }}
    </main>

    {{-- FOOTER --}}
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                        <div class="brand-logo" style="width:36px;height:36px;font-size:16px;">
                            <img src="{{ asset('images/logo.png') }}" alt="Logo RTLH">
                        </div>
                        <h4 style="margin:0;">Portal RTLH Cirebon</h4>
                    </div>
                    <p>Sistem Informasi Geografis dan Basis Data Terpadu Penanganan Rumah Tidak Layak Huni (RTLH) Kabupaten Cirebon.</p>
                </div>

                <div class="footer-col">
                    <h5>Navigasi Publik</h5>
                    <ul>
                        <li><a href="{{ route('public.home') }}">Beranda</a></li>
                        <li><a href="{{ route('public.map') }}">Peta GIS Tematik</a></li>
                        <li><a href="{{ route('public.statistics') }}">Statistik & Tren</a></li>
                        <li><a href="{{ route('public.datasets') }}">Katalog Open Data</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h5>Layanan & Integrasi</h5>
                    <ul>
                        <li><a href="{{ route('api.public.rtlh') }}" target="_blank">RTLH GeoJSON API</a></li>
                        <li><a href="{{ route('api.public.statistics') }}" target="_blank">Statistik JSON API</a></li>
                        <li><a href="{{ route('public.download.rtlh') }}">Download CSV RTLH</a></li>
                        <li><a href="{{ route('public.viewer') }}">Panduan Pengguna</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h5>Akses Petugas</h5>
                    <ul>
                        @auth
                            <li><a href="{{ route('dashboard') }}">Dashboard Saya</a></li>
                            <li><a href="{{ route('profile.edit') }}">Pengaturan Akun</a></li>
                        @else
                            <li><a href="{{ route('login') }}">Login Petugas Lapangan</a></li>
                        @endauth
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div>&copy; {{ date('Y') }} Pemerintah Kabupaten Cirebon. Dinas Perumahan, Kawasan Permukiman dan Pertanahan.</div>
                <div>WebGIS & Data Platform v2.0</div>
            </div>
        </div>
    </footer>

    <script>
        // ── Dark Mode ─────────────────────────────────────────
        const DARK_KEY = 'rtlh-dark-mode';
        const html     = document.documentElement;
        const icon     = document.getElementById('darkToggleIcon');

        function applyDarkMode(dark) {
            if (dark) {
                html.classList.add('dark');
                if (icon) icon.className = 'fa-solid fa-sun';
            } else {
                html.classList.remove('dark');
                if (icon) icon.className = 'fa-solid fa-moon';
            }
            localStorage.setItem(DARK_KEY, dark ? '1' : '0');
        }

        function toggleDarkMode() {
            applyDarkMode(!html.classList.contains('dark'));
        }

        // Apply saved preference immediately (prevents flash)
        applyDarkMode(localStorage.getItem(DARK_KEY) === '1');

        // ── Mobile Dropdown Menu Toggle ───────────────────
        function toggleMobileNav() {
            const menu = document.getElementById('lapaMobileMenu');
            if (menu) {
                menu.classList.toggle('open');
            }
        }
    </script>

    @stack('scripts')
</body>

</html>
