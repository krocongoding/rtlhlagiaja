@props(['title' => 'Workspace Petugas | Teras Rutilahu Cirebon'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Workspace Petugas Teras Rutilahu Cirebon') }}</title>

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
                    <div class="gov-brand-title">TERAS RUTILAHU</div>
                    <div class="gov-brand-sub">DPRKP Kota Cirebon</div>
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
                                <a href="{{ route('cpbm.index') }}" class="gov-nav-item {{ request()->routeIs('cpbm.*') ? 'active' : '' }}">
                                    <span>Basis Data CPBM</span>
                                </a>

                                <a href="{{ route('houses.index') }}" class="gov-nav-item {{ (request()->routeIs('houses.index') || request()->routeIs('houses.*')) && !request()->routeIs('houses.create') && !request()->routeIs('houses.map') ? 'active' : '' }}">
                                    <span>Basis Data RTLH</span>
                                </a>

                                <a href="{{ route('houses.map') }}" class="gov-nav-item {{ request()->routeIs('houses.map') ? 'active' : '' }}">
                                    <span>Peta & Sebaran Realisasi</span>
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
                                <a href="{{ route('cpbm.index') }}" class="gov-nav-item {{ request()->routeIs('cpbm.*') ? 'active' : '' }}">
                                    <span>Basis Data CPBM</span>
                                </a>

                                <a href="{{ route('houses.index') }}" class="gov-nav-item {{ (request()->routeIs('houses.index') || request()->routeIs('houses.*')) && !request()->routeIs('surveyor.rtlh.create') && !request()->routeIs('houses.create') && !request()->routeIs('houses.map') ? 'active' : '' }}">
                                    <span>Data RTLH Saya</span>
                                </a>

                                <a href="{{ route('houses.map') }}" class="gov-nav-item {{ request()->routeIs('houses.map') ? 'active' : '' }}">
                                    <span>Peta Sebaran Lapangan</span>
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
                        <strong>{{ $title ?? 'Kota Cirebon' }}</strong>
                    </div>
                </div>

                <div class="gov-topbar-right">
                    {{-- LONCENG NOTIFIKASI REVISI --}}
                    @auth
                        <div class="gov-notif-dropdown-wrapper" style="position:relative;">
                            <button type="button" class="gov-notif-btn" id="notifDropdownBtn" onclick="toggleNotifDropdown(event)" title="Pemberitahuan Revisi Data" aria-label="Notifikasi Revisi">
                                <i class="fa-solid fa-bell"></i>
                                @if(($pendingRevisionCount ?? 0) > 0)
                                    <span class="gov-notif-badge">{{ $pendingRevisionCount > 99 ? '99+' : $pendingRevisionCount }}</span>
                                @endif
                            </button>

                            {{-- DROPDOWN MENU NOTIFIKASI --}}
                            <div class="gov-notif-menu" id="notifDropdownMenu" style="display:none;">
                                <div class="gov-notif-header">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <i class="fa-solid fa-bell" style="color:#d97706;"></i>
                                        <span style="font-weight:800; font-size:13px; color:var(--slate-900);">Catatan Revisi Admin</span>
                                    </div>
                                    @if(($pendingRevisionCount ?? 0) > 0)
                                        <span class="badge danger" style="font-size:10.5px; font-weight:800; padding:2px 8px; background:#fee2e2; color:#b91c1c; border:1px solid #fca5a5;">
                                            {{ $pendingRevisionCount }} Perlu Perbaikan
                                        </span>
                                    @else
                                        <span class="badge success" style="font-size:10.5px; font-weight:700; padding:2px 8px; background:#dcfce7; color:#15803d; border:1px solid #bbf7d0;">
                                            Semua Baik
                                        </span>
                                    @endif
                                </div>

                                <div class="gov-notif-list">
                                    @forelse(($revisionNotifications ?? []) as $revHouse)
                                        <div class="gov-notif-item">
                                            <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:3px;">
                                                <a href="{{ route('houses.show', $revHouse) }}" class="gov-notif-code">
                                                    {{ $revHouse->house_code }}
                                                </a>
                                                <span class="gov-notif-time">
                                                    {{ $revHouse->revised_at ? $revHouse->revised_at->diffForHumans() : 'Baru saja' }}
                                                </span>
                                            </div>

                                            <div class="gov-notif-recipient">
                                                <i class="fa-solid fa-user" style="font-size:10px; opacity:0.7;"></i>
                                                <strong>{{ $revHouse->cpbm?->name ?? $revHouse->head_of_household_name ?? 'Penerima Bantuan' }}</strong>
                                                <span>• Kel. {{ $revHouse->region?->name ?? '-' }}</span>
                                            </div>

                                            <div class="gov-notif-note-box">
                                                <i class="fa-solid fa-circle-exclamation" style="flex-shrink:0; margin-top:2px;"></i>
                                                <div style="flex:1;">
                                                    {{ Str::limit($revHouse->revision_notes, 110) }}
                                                </div>
                                            </div>

                                            <div style="display:flex; justify-content:flex-end; margin-top:8px;">
                                                <a href="{{ route('houses.edit', $revHouse) }}" class="btn small primary" style="font-size:11px; padding:3px 10px; font-weight:700;">
                                                    Perbaiki Data &rarr;
                                                </a>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="gov-notif-empty">
                                            <i class="fa-solid fa-circle-check" style="font-size:32px; color:#10b981; margin-bottom:8px; display:inline-block;"></i>
                                            <div style="font-weight:700; color:var(--slate-800); font-size:13.5px;">Tidak Ada Revisi</div>
                                            <div style="font-size:12px; color:var(--slate-500); margin-top:4px;">
                                                Semua survei Anda telah disetujui atau belum ada catatan revisi dari admin.
                                            </div>
                                        </div>
                                    @endforelse
                                </div>

                                @if(($pendingRevisionCount ?? 0) > 0)
                                    <div class="gov-notif-footer">
                                        <a href="{{ route('houses.index', ['status' => 'revision']) }}">
                                            Lihat Semua Data Butuh Revisi ({{ $pendingRevisionCount }}) &rarr;
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endauth

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

                {{-- FLOATING TOAST NOTIFICATION STACK --}}
                <div id="toast-container" style="position:fixed; top:24px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:10px; max-width:380px; width:calc(100% - 48px); pointer-events:none;">
                    @if(session('ok') || session('success'))
                        <div class="gov-toast toast-success" style="pointer-events:auto;">
                            <div class="gov-toast-icon"><i class="fa-solid fa-circle-check"></i></div>
                            <div class="gov-toast-content">
                                <strong>Operasi Berhasil</strong>
                                <div>{{ session('ok') ?? session('success') }}</div>
                            </div>
                            <button type="button" class="gov-toast-close" onclick="this.parentElement.remove()">&times;</button>
                        </div>
                    @endif

                    @if(session('error') || session('err'))
                        <div class="gov-toast toast-error" style="pointer-events:auto;">
                            <div class="gov-toast-icon"><i class="fa-solid fa-circle-xmark"></i></div>
                            <div class="gov-toast-content">
                                <strong>Pemberitahuan</strong>
                                <div>{{ session('error') ?? session('err') }}</div>
                            </div>
                            <button type="button" class="gov-toast-close" onclick="this.parentElement.remove()">&times;</button>
                        </div>
                    @endif

                    @if(session('status'))
                        <div class="gov-toast toast-info" style="pointer-events:auto;">
                            <div class="gov-toast-icon"><i class="fa-solid fa-circle-info"></i></div>
                            <div class="gov-toast-content">
                                <strong>Status Sistem</strong>
                                <div>{{ session('status') }}</div>
                            </div>
                            <button type="button" class="gov-toast-close" onclick="this.parentElement.remove()">&times;</button>
                        </div>
                    @endif
                </div>

                {{-- AUTO DISMISS TOASTS SCRIPT --}}
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const toasts = document.querySelectorAll('.gov-toast');
                        toasts.forEach(toast => {
                            setTimeout(() => {
                                toast.style.opacity = '0';
                                toast.style.transform = 'translateX(40px)';
                                setTimeout(() => toast.remove(), 400);
                            }, 4500);
                        });
                    });
                </script>

                {{ $slot }}
            </main>

            {{-- FOOTER RESMI WORKSPACE --}}
            <footer class="gov-footer">
                <div>
                    &copy; {{ date('Y') }} <strong>Pemerintah Kota Cirebon</strong> &bull; Dinas Perumahan Rakyat dan Kawasan Permukiman
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

        // ── Dropdown Notifikasi Revisi Admin ──
        function toggleNotifDropdown(e) {
            if (e) e.stopPropagation();
            const menu = document.getElementById('notifDropdownMenu');
            if (!menu) return;
            menu.style.display = (menu.style.display === 'none' || menu.style.display === '') ? 'block' : 'none';
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('notifDropdownMenu');
            const btn = document.getElementById('notifDropdownBtn');
            if (menu && menu.style.display === 'block') {
                if (!menu.contains(e.target) && (!btn || !btn.contains(e.target))) {
                    menu.style.display = 'none';
                }
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const menu = document.getElementById('notifDropdownMenu');
                if (menu && menu.style.display === 'block') {
                    menu.style.display = 'none';
                }
            }
        });

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
