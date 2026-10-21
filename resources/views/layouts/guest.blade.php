<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Teras Rutilahu Cirebon') }} — Autentikasi</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- App CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body style="background: radial-gradient(circle at top, #1e293b 0%, #0f172a 100%); min-height:100vh; display:flex; flex-direction:column;">

    <header style="padding: 24px 0; text-align:center;">
        <a href="{{ route('public.home') }}" style="display:inline-flex; align-items:center; gap:12px; text-decoration:none;">
            <div class="brand-logo" style="width:44px; height:44px; font-size:20px;">
                <img src="{{ asset('images/logo.png') }}" alt="Logo RTLH">
            </div>
            <div style="text-align:left;">
                <div style="font-size:18px; font-weight:800; color:#ffffff;">Teras Rutilahu</div>
                <div style="font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase;">Kabupaten Cirebon</div>
            </div>
        </a>
    </header>

    <main class="container" style="flex:1; display:flex; align-items:center; justify-content:center; padding: 20px;">
        <div class="auth-card">
            {{ $slot }}
        </div>
    </main>

    <footer style="text-align:center; padding: 24px 20px; font-size:13px; color:#64748b;">
        &copy; {{ date('Y') }} Pemerintah Kabupaten Cirebon • Dinas Perumahan, Kawasan Permukiman dan Pertanahan
    </footer>

</body>
</html>
