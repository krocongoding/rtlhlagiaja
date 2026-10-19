@props(['title' => 'Workspace Petugas | RTLH Cirebon'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Workspace Petugas RTLH Cirebon') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    @stack('head')

    {{-- Anti-FOUC: apply dark class and sidebar collapsed state before first paint --}}
    <script>
        if (localStorage.getItem('rtlh-dark-mode') === '1') {
            document.documentElement.classList.add('dark');
        }
    </script>
</head>
<body>

    <div class="gov-layout" id="govLayout">

        {{-- ============================================================
             SIDEBAR PEMERINTAHAN (KIRI)
             ============================================================ --}}
        <aside class="gov-sidebar" id="govSidebar">
            {{-- KOP INSTANSI & LOGO --}}
            <a href="{{ route('dashboard') }}" class="gov-sidebar-brand" title="Dashboard Workspace">
                <div class="gov-sidebar-logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo Kab Cirebon">
                </div>
                <div>
                    <div class="gov-brand-title">RTLH CIREBON</div>
                    <div class="gov-brand-sub">DPKPP Kab. Cirebon</div>
                </div>
            </a>

            {{-- IDENTITAS AKUN / USER INFO --}}
            @auth
                <div class="gov-sidebar-user">
                    <div class="gov-user-avatar">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="gov-user-meta">
                        <span class="gov-user-name" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</span>
                        @if(auth()->user()->hasRole('admin'))
                            <span class="role-pill role-admin">Admin Sistem</span>
                        @elseif(auth()->user()->hasRole('surveyor'))
                            <span class="role-pill role-surveyor">Surveyor Lapangan</span>
                        @else
                            <span class="role-pill">Petugas</span>
                        @endif
                    </div>
                </div>
            @endauth

            {{-- MENU NAVIGASI SESUAI ROLE --}}
            <nav class="gov-sidebar-nav">
                @auth
                    @if(auth()->user()->hasRole('admin'))
                        {{-- GRUP: MENU UTAMA --}}
                        <div>
                            <div class="gov-nav-section-label">Menu Utama</div>
                            <div class="gov-nav-menu">
                                <a href="{{ route('admin.dashboard') }}" class="gov-nav-item {{ request()->routeIs('admin.dashboard') || (request()->routeIs('dashboard') && auth()->user()->hasRole('admin')) ? 'active' : '' }}">
                                    <span>Dashboard Admin</span>
                                </a>
                            </div>
                        </div>

                        {{-- GRUP: DATA & PENGAWASAN --}}
                        <div>
                            <div class="gov-nav-section-label">Data & Pengawasan</div>
                            <div class="gov-nav-menu">
                                <a href="{{ route('houses.index') }}" class="gov-nav-item {{ request()->routeIs('houses.index') || (request()->routeIs('houses.*') && !request()->routeIs('houses.create')) ? 'active' : '' }}">
                                    <span>Basis Data RTLH</span>
                                </a>

                                <a href="{{ route('houses.create') }}" class="gov-nav-item {{ request()->routeIs('houses.create') ? 'active' : '' }}">
                                    <span>Registrasi RTLH Baru</span>
                                </a>

                                <a href="{{ route('admin.review.index') }}" class="gov-nav-item {{ request()->routeIs('admin.review.*') ? 'active' : '' }}">
                                    <span>Verifikasi & Review</span>
                                </a>

                                <a href="{{ route('admin.surveyors.index') }}" class="gov-nav-item {{ request()->routeIs('admin.surveyors.*') ? 'active' : '' }}">
                                    <span>Kelola Surveyor</span>
                                </a>

                                <a href="{{ route('regions.index') }}" class="gov-nav-item {{ request()->routeIs('regions.*') ? 'active' : '' }}">
                                    <span>Master Wilayah</span>
                                </a>
                            </div>
                        </div>

                    @elseif(auth()->user()->hasRole('surveyor'))
                        {{-- GRUP: MENU SURVEYOR --}}
                        <div>
                            <div class="gov-nav-section-label">Menu Utama</div>
                            <div class="gov-nav-menu">
                                <a href="{{ route('surveyor.dashboard') }}" class="gov-nav-item {{ request()->routeIs('surveyor.dashboard') || (request()->routeIs('dashboard') && auth()->user()->hasRole('surveyor')) ? 'active' : '' }}">
                                    <span>Dashboard Surveyor</span>
                                </a>
                            </div>
                        </div>

                        <div>
                            <div class="gov-nav-section-label">Pendataan Lapangan</div>
                            <div class="gov-nav-menu">
                                <a href="{{ route('houses.index') }}" class="gov-nav-item {{ request()->routeIs('houses.index') || (request()->routeIs('houses.*') && !request()->routeIs('surveyor.rtlh.create')) ? 'active' : '' }}">
                                    <span>Data RTLH Saya</span>
                                </a>

                                <a href="{{ route('surveyor.rtlh.create') }}" class="gov-nav-item {{ request()->routeIs('surveyor.rtlh.create') ? 'active' : '' }}">
                                    <span>Input Survei Baru</span>
                                </a>
                            </div>
                        </div>
                    @else
                        <div>
                            <div class="gov-nav-section-label">Navigasi</div>
                            <div class="gov-nav-menu">
                                <a href="{{ route('dashboard') }}" class="gov-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                    <span>Dashboard</span>
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- GRUP: PINTASAN PUBLIK --}}
                    <div>
                        <div class="gov-nav-section-label">Portal Publik Warga</div>
                        <div class="gov-nav-menu">
                            <a href="{{ route('public.home') }}" target="_blank" class="gov-nav-item" title="Buka Beranda Publik di Tab Baru">
                                <span>Web Publik &rarr;</span>
                            </a>
                            <a href="{{ route('public.map') }}" target="_blank" class="gov-nav-item" title="Buka Peta GIS Publik di Tab Baru">
                                <span>Peta GIS Publik &rarr;</span>
                            </a>
                        </div>
                    </div>
                @endauth
            </nav>

            {{-- FOOTER SIDEBAR --}}
            @auth
                <div class="gov-sidebar-footer">
                    <a href="{{ route('profile.edit') }}" class="gov-nav-item" style="padding:6px 10px; font-size:12px; border-radius:6px; color:#94a3b8;" title="Pengaturan Profil">
                        <span>Profil</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="gov-nav-item" style="background:none; border:none; cursor:pointer; padding:6px 10px; font-size:12px; color:#f87171; border-radius:6px;" title="Keluar">
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            @endauth
        </aside>

        {{-- Backdrop untuk mobile saat drawer terbuka --}}
        <div class="gov-sidebar-backdrop" onclick="toggleGovSidebar()"></div>

        {{-- ============================================================
             AREA KONTEN UTAMA (KANAN — OTOMATIS MELEBAR SAAT SIDEBAR COLLAPSED)
             ============================================================ --}}
        <div class="gov-main-wrapper">
            {{-- TOPBAR WORKSPACE --}}
            <header class="gov-topbar">
                <div class="gov-topbar-left">
                    {{-- TOMBOL TOGGLE DRAWER (LIPAT / BUKA AGAR MELEBAR) --}}
                    <button type="button" class="gov-toggle-btn" onclick="toggleGovSidebar()" id="sidebarToggleBtn" title="Lipat atau buka menu samping (konten melebar)">
                        <span id="sidebarToggleIcon">&#9776;</span>
                        <span id="sidebarToggleLabel">Menu</span>
                    </button>

                    <div class="gov-topbar-breadcrumb">
                        <span>Workspace Petugas</span>
                        <span>&rsaquo;</span>
                        <strong>{{ $title ?? 'Kabupaten Cirebon' }}</strong>
                    </div>
                </div>

                <div class="gov-topbar-right">
                    {{-- DARK MODE TOGGLE --}}
                    <button type="button" class="btn-theme-toggle" onclick="toggleDarkMode()" id="darkToggleBtn" title="Ganti tema" style="font-size:11px; font-weight:700;">
                        <span id="darkToggleText">Dark</span>
                    </button>

                    <a href="{{ route('public.home') }}" target="_blank" class="btn small" style="font-size:11.5px; font-weight:700;" title="Buka Portal Publik">
                        Web Publik
                    </a>
                </div>
            </header>

            {{-- MAIN CONTENT --}}
            <main class="gov-content fade-in">
                @isset($header)
                    <div class="pagehead" style="padding-top:0; padding-bottom:18px;">
                        {{ $header }}
                    </div>
                @endisset

                {{-- FLASH MESSAGES --}}
                @if(session('ok') || session('success'))
                    <div class="alert ok">
                        <div>{{ session('ok') ?? session('success') }}</div>
                    </div>
                @endif

                @if(session('error') || session('err'))
                    <div class="alert err">
                        <div>{{ session('error') ?? session('err') }}</div>
                    </div>
                @endif

                @if(session('status'))
                    <div class="alert info">
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                {{ $slot }}
            </main>

            {{-- FOOTER RESMI WORKSPACE --}}
            <footer class="gov-footer">
                <div>
                    &copy; {{ date('Y') }} <strong>Pemerintah Kabupaten Cirebon</strong> &bull; Dinas Perumahan, Kawasan Permukiman dan Pertanahan
                </div>
                <div>
                    Sistem Pengelolaan Rumah Tidak Layak Huni (RTLH)
                </div>
            </footer>
        </div>

    </div>

    <script>
        const DARK_KEY = 'rtlh-dark-mode';
        const SIDEBAR_KEY = 'gov-sidebar-collapsed';
        const html = document.documentElement;
        const layout = document.getElementById('govLayout');

        function applyDarkMode(dark) {
            if (dark) {
                html.classList.add('dark');
                const txt = document.getElementById('darkToggleText');
                if (txt) txt.textContent = 'Light';
            } else {
                html.classList.remove('dark');
                const txt = document.getElementById('darkToggleText');
                if (txt) txt.textContent = 'Dark';
            }
            localStorage.setItem(DARK_KEY, dark ? '1' : '0');
        }

        function toggleDarkMode() {
            applyDarkMode(!html.classList.contains('dark'));
        }

        applyDarkMode(localStorage.getItem(DARK_KEY) === '1');

        // ── Collapsible Sidebar Toggle (Melebar Otomatis, Tanpa Blur) ──
        function applySidebarState(collapsed) {
            if (window.innerWidth < 1024) {
                layout.classList.toggle('mobile-sidebar-open', !collapsed);
            } else {
                layout.classList.toggle('sidebar-collapsed', collapsed);
                localStorage.setItem(SIDEBAR_KEY, collapsed ? '1' : '0');
            }
        }

        function toggleGovSidebar() {
            if (window.innerWidth < 1024) {
                layout.classList.toggle('mobile-sidebar-open');
            } else {
                const isCollapsed = layout.classList.contains('sidebar-collapsed');
                applySidebarState(!isCollapsed);
            }
        }

        // Apply saved desktop state
        if (window.innerWidth >= 1024 && localStorage.getItem(SIDEBAR_KEY) === '1') {
            layout.classList.add('sidebar-collapsed');
        }
    </script>
    @stack('scripts')
</body>
</html>