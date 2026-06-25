<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIOPK Badung') — Sistem Informasi OPK Kabupaten Badung</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --tanah: #2C1A0E; --emas: #C8922A; --emas-muda: #E8B84B;
            --krem: #F7F1E8; --hijau: #2D5A27; --merah: #C0392B; --kuning: #D4A017;
            --emas-rgb: 200,146,42; --merah-rgb: 192,57,43; --kuning-rgb: 212,160,23; --hijau-rgb: 45,90,39;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--krem); color: var(--tanah); }

        .publik-nav {
            background: var(--tanah); padding: 0 1.5rem; height: 56px;
            display: flex; align-items: center; justify-content: space-between;
            border-bottom: 2px solid var(--emas); position: sticky; top: 0; z-index: 500;
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-brand:hover { text-decoration: none; }
        .nav-logo { width: 32px; height: 32px; border-radius: 50%; background: var(--emas); display: flex; align-items: center; justify-content: center; font-family: 'Cormorant Garamond', serif; font-weight: 700; color: var(--tanah); font-size: 0.9rem; }
        .nav-title { font-family: 'Cormorant Garamond', serif; font-size: 1rem; font-weight: 700; color: #f7f1e8; }
        .nav-title span { color: var(--emas-muda); }
        .nav-links { display: flex; align-items: center; gap: 1.25rem; }
        .nav-links a { color: rgba(247,241,232,0.65); text-decoration: none; font-size: 0.8rem; font-weight: 500; transition: color 0.2s; }
        .nav-links a:hover { color: var(--emas-muda); }
        .nav-links a.active { color: var(--emas-muda); }
        .nav-actions { display: flex; align-items: center; gap: 1rem; }
        .nav-login-link { color: rgba(247,241,232,0.45); text-decoration: none; font-size: 0.75rem; transition: color 0.2s; }
        .nav-login-link:hover { color: var(--emas-muda); }
        .btn-lapor { background: var(--emas); color: var(--tanah); border: none; padding: 6px 16px; border-radius: 3px; font-size: 0.8rem; font-weight: 600; text-decoration: none; cursor: pointer; white-space: nowrap; transition: background 0.2s; }
        .btn-lapor:hover { background: var(--emas-muda); color: var(--tanah); }

        @media (max-width: 768px) {
            .publik-nav { padding: 0 0.75rem; }
            .nav-links { gap: 0.75rem; }
            .nav-links a { font-size: 0.72rem; }
            .nav-actions { gap: 0.5rem; }
            .btn-lapor { padding: 5px 10px; font-size: 0.72rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

<nav class="publik-nav">
    <a href="{{ route('publik.dashboard') }}" class="nav-brand">
        <div class="nav-logo" style="background: transparent; overflow: hidden; padding: 0;">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
        </div>
        <div class="nav-title">SIOPK <span>Badung</span></div>
    </a>
    <div class="nav-links">
        <a href="{{ route('publik.dashboard') }}" class="{{ request()->routeIs('publik.dashboard') ? 'active' : '' }}">
            <i class="bi bi-map"></i> Peta OPK
        </a>
        <a href="{{ route('publik.lapor.index') }}" class="{{ request()->routeIs('publik.lapor.index') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i> Lapor OPK
        </a>
        <a href="{{ route('publik.lapor.status') }}" class="{{ request()->routeIs('publik.lapor.status') ? 'active' : '' }}">
            <i class="bi bi-search"></i> Cek Status
        </a>
    </div>
    <div class="nav-actions">
        @auth
            <a href="{{ route('admin.dashboard') }}" class="nav-login-link">
                <i class="bi bi-speedometer2"></i> Panel Admin
            </a>
        @else
            <a href="{{ route('login') }}" class="nav-login-link">
                <i class="bi bi-shield-lock"></i> Login Dinas
            </a>
        @endauth
        @if(!request()->routeIs('publik.lapor.*'))
            <a href="{{ route('publik.lapor.index') }}" class="btn-lapor">
                <i class="bi bi-plus-circle"></i> Lapor Sekarang
            </a>
        @endif
    </div>
</nav>

@yield('content')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
