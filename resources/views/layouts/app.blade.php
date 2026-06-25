<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIOPK Badung') — Sistem Informasi OPK Kabupaten Badung</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"
          integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <!-- Leaflet -->
    <link href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" rel="stylesheet"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --tanah:       #2C1A0E;
            --emas:        #C8922A;
            --emas-muda:   #E8B84B;
            --krem:        #F7F1E8;
            --hijau:       #2D5A27;
            --merah:       #C0392B;
            --kuning:      #D4A017;
            --sidebar-w:   240px;
            --emas-rgb:    200,146,42;
            --merah-rgb:   192,57,43;
            --kuning-rgb:  212,160,23;
            --hijau-rgb:   45,90,39;
        }
        body { font-family: 'Inter', sans-serif; background: #f4f0e8; color: var(--tanah); }

        /* ---- Sidebar ---- */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--tanah);
            min-height: 100vh;
            position: fixed; top: 0; left: 0;
            z-index: 100; overflow-y: auto;
            border-right: 2px solid var(--emas);
        }
        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(var(--emas-rgb),0.25);
            display: flex; align-items: center; gap: 10px;
        }
        .sidebar-logo {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--emas);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Cormorant Garamond', serif;
            font-weight: 700; font-size: 1rem; color: var(--tanah);
        }
        .sidebar-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.05rem; font-weight: 700;
            color: #F7F1E8; line-height: 1.2;
        }
        .sidebar-title small { color: rgba(232,184,75,0.7); font-size: 0.65rem; display: block; }
        .sidebar-section {
            padding: 1rem 1.5rem 0.25rem;
            font-size: 0.62rem; font-weight: 700;
            color: rgba(var(--emas-rgb),0.5);
            text-transform: uppercase; letter-spacing: 0.15em;
        }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 1.5rem;
            color: rgba(247,241,232,0.6);
            text-decoration: none; font-size: 0.83rem;
            transition: all 0.15s;
            border-left: 3px solid transparent;
        }
        .sidebar-link:hover { color: #F7F1E8; background: rgba(var(--emas-rgb),0.08); }
        .sidebar-link.active {
            color: var(--emas-muda);
            border-left-color: var(--emas);
            background: rgba(var(--emas-rgb),0.1);
        }
        .sidebar-link .badge { margin-left: auto; font-size: 0.6rem; }

        /* ---- Topbar ---- */
        .topbar {
            margin-left: var(--sidebar-w);
            background: white;
            border-bottom: 1px solid #e5e0d8;
            padding: 0 1.5rem;
            height: 56px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 99;
        }
        .topbar-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.2rem; font-weight: 600;
        }

        /* ---- Main Content ---- */
        .main-wrapper { margin-left: var(--sidebar-w); padding: 1.75rem; }

        /* ---- Cards ---- */
        .card { border: 1px solid #d4c9b8; border-radius: 4px; }
        .card-header-custom {
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid #d4c9b8;
            display: flex; align-items: center; justify-content: space-between;
            background: #fdfaf5;
        }
        .card-header-custom .title { font-weight: 600; font-size: 0.88rem; }

        /* ---- KPI Cards ---- */
        .kpi-card { border-top: 3px solid var(--emas) !important; }
        .kpi-card.kritis { border-top-color: var(--merah) !important; }
        .kpi-card.waspada { border-top-color: var(--kuning) !important; }
        .kpi-card.hijau { border-top-color: var(--hijau) !important; }
        .kpi-value {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.4rem; font-weight: 700;
            line-height: 1; color: var(--tanah);
        }
        .kpi-label { font-size: 0.7rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.08em; }
        .kpi-sub   { font-size: 0.75rem; color: #9ca3af; margin-top: 4px; }

        /* ---- Status Badges ---- */
        .badge-kritis  { background-color: rgba(var(--merah-rgb),0.1); color: var(--merah); border: 1px solid rgba(var(--merah-rgb),0.2); }
        .badge-waspada { background-color: rgba(var(--kuning-rgb),0.1); color: var(--kuning); border: 1px solid rgba(var(--kuning-rgb),0.2); }
        .badge-baik    { background-color: rgba(var(--hijau-rgb),0.1);  color: var(--hijau);  border: 1px solid rgba(var(--hijau-rgb),0.2); }

        /* ---- Peta ---- */
        #peta { border-radius: 4px; }

        /* ---- AI Panel ---- */
        .ai-panel {
            background: linear-gradient(135deg, #1a0f06, var(--tanah));
            border-radius: 4px; color: #f7f1e8;
            border: 1px solid rgba(var(--emas-rgb),0.3);
        }
        .ai-blink { animation: blink 2s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }

        /* ---- Alert ---- */
        .alert { border-radius: 3px; border: none; }
        .alert-success { background: rgba(var(--hijau-rgb),0.1); color: var(--hijau); border-left: 4px solid var(--hijau); }
        .alert-danger  { background: rgba(var(--merah-rgb),0.1); color: var(--merah); border-left: 4px solid var(--merah); }
        .alert-warning { background: rgba(var(--kuning-rgb),0.1); color: #8a6010; border-left: 4px solid var(--kuning); }

        /* ---- Table ---- */
        .table th {
            font-size: 0.68rem; text-transform: uppercase;
            letter-spacing: 0.08em; color: #6b7280;
            font-weight: 600; background: #fdfaf5;
        }
        .table td { vertical-align: middle; font-size: 0.83rem; }

        /* ---- Button ---- */
        .btn-emas {
            background: var(--emas); color: var(--tanah);
            border: none; font-weight: 600;
        }
        .btn-emas:hover { background: var(--emas-muda); color: var(--tanah); }

        /* ---- Leaflet popup ---- */
        .leaflet-popup-content-wrapper { border-radius: 4px; }
        .leaflet-popup-content a { color: var(--tanah); }
        .leaflet-popup-content a:hover { color: var(--emas-muda); }
        .popup-kritis  { border-top: 3px solid var(--merah); }
        .popup-waspada { border-top: 3px solid var(--kuning); }
        .popup-baik    { border-top: 3px solid var(--hijau); }
    </style>
    @stack('styles')
</head>
<body>

{{-- Sidebar --}}
@auth
<div class="sidebar">
    <div class="sidebar-brand">
        <div class="sidebar-logo" style="background: transparent; overflow: hidden; padding: 0;">
			<img src="{{ asset('img/logo.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
		</div>
        <div class="sidebar-title">
            SIOPK
            <small>Kabupaten Badung</small>
        </div>
    </div>

    <div class="sidebar-section">Utama</div>
    <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="bi bi-grid-1x2"></i> Dashboard
    </a>
    <a href="{{ route('admin.opk.index') }}" class="sidebar-link {{ request()->routeIs('admin.opk.*') ? 'active' : '' }}">
        <i class="bi bi-collection"></i> Data OPK
    </a>
    <a href="{{ route('admin.verifikasi.index') }}" class="sidebar-link {{ request()->routeIs('admin.verifikasi.*') ? 'active' : '' }}">
        <i class="bi bi-check2-circle"></i> Verifikasi
        @if(($sidebarAntrian ?? 0) > 0)
            <span class="badge bg-danger badge">{{ $sidebarAntrian }}</span>
        @endif
    </a>

    <div class="sidebar-section">Peta & Analitik</div>
    <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
        <i class="bi bi-map"></i> Peta OPK
    </a>
    <a href="{{ route('admin.laporan.index') }}" class="sidebar-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
        <i class="bi bi-bar-chart-line"></i> Laporan
    </a>
    <a href="{{ route('admin.ai.ringkasan-halaman') }}" class="sidebar-link {{ request()->routeIs('admin.ai.*') ? 'active' : '' }}">
        <i class="bi bi-robot"></i> AI Ringkasan
        @if(($sidebarAiKritis ?? 0) > 0)
            <span class="badge bg-danger badge">{{ $sidebarAiKritis }}</span>
        @endif
    </a>

    <div class="sidebar-section">Sistem</div>
    @if(auth()->user()->isAdmin())
    <a href="{{ route('admin.pengguna.index') }}" class="sidebar-link {{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}">
        <i class="bi bi-people"></i> Pengguna
    </a>
    <a href="{{ route('admin.wilayah.index') }}" class="sidebar-link {{ request()->routeIs('admin.wilayah.*') ? 'active' : '' }}">
        <i class="bi bi-geo-alt"></i> Wilayah
    </a>
    <a href="{{ route('admin.kategori.index') }}" class="sidebar-link {{ request()->routeIs('admin.kategori.*') ? 'active' : '' }}">
        <i class="bi bi-tags"></i> Kategori OPK
    </a>
    <a href="{{ route('admin.opk.arsip') }}" class="sidebar-link {{ request()->routeIs('admin.opk.arsip') ? 'active' : '' }}">
        <i class="bi bi-archive"></i> Arsip OPK
    </a>
    @endif
    <a href="{{ route('publik.dashboard') }}" class="sidebar-link" target="_blank">
        <i class="bi bi-globe"></i> Portal Publik
    </a>

    <div class="mt-4" style="padding:1rem 1.5rem;border-top:1px solid rgba(var(--emas-rgb),0.15);">
        <div style="font-size:0.72rem;color:rgba(247,241,232,0.4);">Login sebagai</div>
        <div style="font-size:0.82rem;color:#e8b84b;font-weight:600;margin-top:2px;">
            {{ auth()->user()->name }}
        </div>
        <div style="font-size:0.68rem;color:rgba(247,241,232,0.35);text-transform:uppercase;letter-spacing:0.08em;">
            {{ auth()->user()->role }}
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit" style="background:none;border:none;color:rgba(var(--emas-rgb),0.5);font-size:0.75rem;cursor:pointer;padding:0;">
                <i class="bi bi-box-arrow-left"></i> Logout
            </button>
        </form>
    </div>
</div>
@endauth

{{-- Topbar --}}
@auth
<div class="topbar">
    <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
    <div class="d-flex align-items-center gap-3">
        <small class="text-muted">{{ now()->isoFormat('dddd, D MMMM Y') }}</small>
        <a href="{{ route('publik.lapor.index') }}" target="_blank"
           class="btn btn-sm btn-emas">
            <i class="bi bi-plus-circle"></i> Lapor OPK
        </a>
    </div>
</div>
@endauth

{{-- Main Content --}}
<div class="{{ auth()->check() ? 'main-wrapper' : '' }}">
    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-3">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @yield('content')
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>

@stack('scripts')
</body>
</html>
