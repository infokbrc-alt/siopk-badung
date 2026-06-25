@extends('layouts.app')
@section('title','Laporan & Statistik')
@section('page-title','Laporan & Statistik OPK')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-family:'Cormorant Garamond',serif;font-size:1.7rem;font-weight:700;margin:0;">Laporan & Statistik</h1>
        <p class="text-muted mb-0" style="font-size:0.82rem;">Rekap data OPK Kabupaten Badung</p>
    </div>
    <a href="{{ route('admin.laporan.export') }}" class="btn btn-emas btn-sm">
        <i class="bi bi-download me-1"></i>Export CSV
    </a>
</div>

{{-- KPI --}}
<div class="row g-3 mb-4">
    @foreach([
        ['Total OPK Resmi', $stats['total'], '', 'bi-collection', '#C8922A'],
        ['Kritis', $stats['kritis'], 'Perlu tindakan', 'bi-exclamation-triangle', '#C0392B'],
        ['Waspada', $stats['waspada'], 'Perlu pantau', 'bi-eye', '#D4A017'],
        ['Baik', $stats['baik'], 'Terlindungi', 'bi-shield-check', '#2D5A27'],
        ['Menunggu Verif.', $stats['menunggu'], 'Antrian', 'bi-clock', '#6b7280'],
        ['Bulan Ini', $stats['bulan_ini'], 'Laporan baru', 'bi-calendar3', '#2980b9'],
    ] as [$label, $val, $sub, $icon, $color])
    <div class="col-md-2">
        <div class="card h-100" style="border-top:3px solid {{ $color }};">
            <div class="card-body text-center py-3">
                <i class="bi {{ $icon }}" style="font-size:1.3rem;color:{{ $color }};"></i>
                <div style="font-family:'Cormorant Garamond',serif;font-size:2rem;font-weight:700;color:#2C1A0E;line-height:1;margin:4px 0;">{{ $val }}</div>
                <div style="font-size:0.68rem;color:#9ca3af;text-transform:uppercase;letter-spacing:0.06em;">{{ $label }}</div>
                @if($sub)<div style="font-size:0.65rem;color:#c4b8a8;margin-top:2px;">{{ $sub }}</div>@endif
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    {{-- Per Kategori --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header-custom"><span class="title">OPK per Jenis</span></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="padding-left:1rem;">Jenis OPK</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Kritis</th>
                            <th class="text-center">Waspada</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($perKategori as $kat)
                        <tr>
                            <td style="padding-left:1rem;font-size:0.83rem;">{{ $kat->ikon }} {{ $kat->nama }}</td>
                            <td class="text-center"><strong>{{ $kat->total }}</strong></td>
                            <td class="text-center" style="color:#C0392B;font-weight:600;">{{ $kat->kritis ?: '—' }}</td>
                            <td class="text-center" style="color:#D4A017;font-weight:600;">{{ $kat->waspada ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Per Kecamatan --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header-custom"><span class="title">OPK per Kecamatan</span></div>
            <div class="card-body">
                @foreach($perKecamatan as $kec)
                @php $pct = $stats['total'] > 0 ? round($kec->total / $stats['total'] * 100) : 0; @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.82rem;">
                        <span>{{ $kec->nama }}</span>
                        <div>
                            <strong>{{ $kec->total }}</strong>
                            @if($kec->kritis > 0)
                                <span style="color:#C0392B;font-size:0.72rem;margin-left:6px;">{{ $kec->kritis }} kritis</span>
                            @endif
                        </div>
                    </div>
                    <div style="height:6px;background:#e5e0d8;border-radius:3px;overflow:hidden;">
                        <div style="height:100%;width:{{ $pct }}%;background:{{ $kec->kritis > 0 ? '#C0392B' : '#C8922A' }};border-radius:3px;transition:width 0.5s;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Top Urgensi --}}
<div class="card">
    <div class="card-header-custom">
        <span class="title">Top 10 OPK Urgensi Tertinggi (AI Score)</span>
        <a href="{{ route('admin.opk.index') }}?kondisi=kritis" style="font-size:0.72rem;color:var(--emas);">Lihat Semua →</a>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="padding-left:1.25rem;width:40px;">#</th>
                    <th>Nama OPK</th>
                    <th>Jenis</th>
                    <th>Kecamatan</th>
                    <th>Kondisi</th>
                    <th>AI Score</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topUrgensi as $i => $opk)
                <tr>
                    <td style="padding-left:1.25rem;">
                        <div style="width:24px;height:24px;border-radius:50%;background:{{ $i < 3 ? '#C0392B' : ($i < 6 ? '#D4A017' : '#9ca3af') }};color:white;display:flex;align-items:center;justify-content:center;font-size:0.65rem;font-weight:700;">{{ $i+1 }}</div>
                    </td>
                    <td>
                        <a href="{{ route('admin.opk.show', $opk) }}" style="font-weight:600;font-size:0.85rem;color:var(--tanah);text-decoration:none;">{{ $opk->nama_opk }}</a>
                    </td>
                    <td><span style="background:rgba(200,146,42,0.1);color:#7a5c1e;padding:2px 8px;border-radius:2px;font-size:0.7rem;font-weight:500;">{{ $opk->kategori?->ikon }} {{ $opk->kategori?->nama }}</span></td>
                    <td style="font-size:0.82rem;">{{ $opk->kecamatan?->nama }}</td>
                    <td><span class="badge badge-{{ $opk->kondisi }} rounded-pill px-2" style="font-size:0.68rem;">{{ ucfirst($opk->kondisi) }}</span></td>
                    <td>
                        <span style="font-family:'Courier New',monospace;font-weight:700;color:{{ $opk->kondisi === 'kritis' ? '#C0392B' : '#D4A017' }}">
                            {{ number_format($opk->ai_urgency_score, 1) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.opk.show', $opk) }}" class="btn btn-sm btn-outline-secondary py-0 px-2">
                            <i class="bi bi-eye" style="font-size:0.75rem;"></i>
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
