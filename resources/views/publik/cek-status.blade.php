<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Laporan — SIOPK Badung</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --tanah:#2C1A0E; --emas:#C8922A; --emas-muda:#E8B84B; --hijau:#2D5A27; --merah:#C0392B; --kuning:#D4A017; }
        body { font-family:'Inter',sans-serif; background:#f4f0e8; min-height:100vh; padding:2rem 1rem; }
        .container-status { max-width:580px; margin:0 auto; }
        .page-title { font-family:'Cormorant Garamond',serif; font-size:2rem; font-weight:700; color:var(--tanah); }
        .search-card { background:white; border:1px solid #d4c9b8; border-radius:4px; padding:1.5rem; margin-bottom:1.5rem; border-top:4px solid var(--emas); }
        .form-label { font-size:0.75rem; font-weight:600; color:var(--tanah); text-transform:uppercase; letter-spacing:0.06em; }
        .form-control { border:1px solid #d4c9b8; border-radius:3px; font-size:0.9rem; background:#fdfaf5; }
        .form-control:focus { border-color:var(--emas); box-shadow:0 0 0 3px rgba(200,146,42,0.12); }
        .btn-emas { background:var(--emas); color:var(--tanah); border:none; font-weight:600; padding:10px 24px; border-radius:3px; }
        .btn-emas:hover { background:var(--emas-muda); color:var(--tanah); }
        .result-card { background:white; border:1px solid #d4c9b8; border-radius:4px; overflow:hidden; }
        .result-header { padding:1.25rem 1.5rem; border-bottom:1px solid #d4c9b8; background:#fdfaf5; }
        .result-body { padding:1.5rem; }
        .info-row { display:flex; justify-content:space-between; align-items:flex-start; padding:10px 0; border-bottom:1px solid #f0ebe3; font-size:0.84rem; }
        .info-row:last-child { border-bottom:none; }
        .info-key { color:#9ca3af; flex-shrink:0; width:140px; }
        .info-val { font-weight:500; color:var(--tanah); text-align:right; }
        .timeline { padding:0; list-style:none; }
        .timeline-item { display:flex; gap:12px; padding-bottom:16px; position:relative; }
        .timeline-item:not(:last-child)::before { content:''; position:absolute; left:11px; top:24px; width:2px; height:calc(100% - 24px); background:#e5e0d8; }
        .timeline-dot { width:24px; height:24px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:0.65rem; font-weight:700; z-index:1; }
        .timeline-content { padding-top:2px; }
        .timeline-label { font-size:0.8rem; font-weight:600; color:var(--tanah); }
        .timeline-meta { font-size:0.72rem; color:#9ca3af; margin-top:2px; }
        .timeline-note { font-size:0.75rem; color:#6b7280; margin-top:4px; background:#f4f0e8; border-radius:3px; padding:6px 8px; }
        .status-chip { display:inline-flex; align-items:center; gap:5px; padding:4px 12px; border-radius:20px; font-size:0.75rem; font-weight:600; }
    </style>
</head>
<body>
<div class="container-status">
    <div class="mb-4">
        <a href="{{ route('publik.lapor.index') }}" style="font-size:0.78rem;color:var(--emas);text-decoration:none;">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Form Lapor
        </a>
        <h1 class="page-title mt-2">Cek Status Laporan</h1>
        <p style="color:#9ca3af;font-size:0.85rem;">Masukkan kode laporan untuk melihat perkembangan verifikasi</p>
    </div>

    <div class="search-card">
        <form method="GET" action="{{ route('publik.lapor.status') }}">
            <label class="form-label">Kode Laporan</label>
            <div class="d-flex gap-2">
                <input type="text" name="kode_laporan" class="form-control"
                       value="{{ $kode }}" placeholder="Contoh: SIOPK-2025-00001"
                       style="font-family:'Courier New',monospace;letter-spacing:0.05em;">
                <button type="submit" class="btn btn-emas">
                    <i class="bi bi-search me-1"></i>Cari
                </button>
            </div>
        </form>
    </div>

    @if($kode && !$laporan)
        <div style="background:rgba(192,57,43,0.08);border-left:3px solid var(--merah);padding:12px 16px;border-radius:0 3px 3px 0;font-size:0.84rem;color:var(--merah);">
            <i class="bi bi-exclamation-circle me-2"></i>Kode laporan <strong>{{ $kode }}</strong> tidak ditemukan. Pastikan kode sudah benar.
        </div>
    @endif

    @if($laporan)
    <div class="result-card">
        <div class="result-header">
            <div style="display:flex;justify-content:space-between;align-items:start;">
                <div>
                    <div style="font-size:0.68rem;color:#9ca3af;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:4px;">
                        {{ $laporan->kode_laporan }}
                    </div>
                    <div style="font-family:'Cormorant Garamond',serif;font-size:1.3rem;font-weight:700;color:var(--tanah);">
                        {{ $laporan->nama_opk }}
                    </div>
                    <div style="font-size:0.75rem;color:#9ca3af;margin-top:2px;">
                        {{ $laporan->kategori?->ikon }} {{ $laporan->kategori?->nama }} &nbsp;·&nbsp;
                        {{ $laporan->kecamatan?->nama }}
                    </div>
                </div>
                @php
                    $statusConfig = [
                        'menunggu'     => ['label'=>'Menunggu Verifikasi', 'bg'=>'rgba(212,160,23,0.1)', 'color'=>'#8a6010'],
                        'ai_review'    => ['label'=>'AI Sedang Review',    'bg'=>'rgba(41,128,185,0.1)', 'color'=>'#2980b9'],
                        'review_dinas' => ['label'=>'Ditinjau Dinas',      'bg'=>'rgba(200,146,42,0.1)', 'color'=>'#7a5c1e'],
                        'disetujui'    => ['label'=>'Disetujui ✓',         'bg'=>'rgba(45,90,39,0.1)',   'color'=>'var(--hijau)'],
                        'ditolak'      => ['label'=>'Ditolak',             'bg'=>'rgba(192,57,43,0.1)', 'color'=>'var(--merah)'],
                        'duplikat'     => ['label'=>'Duplikat',            'bg'=>'rgba(107,114,128,0.1)','color'=>'#6b7280'],
                    ];
                    $sc = $statusConfig[$laporan->status_verifikasi] ?? $statusConfig['menunggu'];
                @endphp
                <span class="status-chip" style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};">
                    {{ $sc['label'] }}
                </span>
            </div>
        </div>

        <div class="result-body">
            {{-- Info Laporan --}}
            <div class="mb-4">
                <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#9ca3af;margin-bottom:8px;">Detail Laporan</div>
                <div class="info-row">
                    <span class="info-key">Kecamatan</span>
                    <span class="info-val">{{ $laporan->kecamatan?->nama }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Desa Dinas</span>
                    <span class="info-val">{{ $laporan->desaDinas?->nama }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Desa Adat</span>
                    <span class="info-val">{{ $laporan->nama_desa_adat }}</span>
                </div>
                <div class="info-row">
                    <span class="info-key">Kondisi</span>
                    <span class="info-val">
                        @if($laporan->kondisi === 'kritis') 🔴 Kritis
                        @elseif($laporan->kondisi === 'waspada') ⚠️ Waspada
                        @else ✅ Baik @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-key">Tanggal Lapor</span>
                    <span class="info-val">{{ $laporan->created_at->isoFormat('D MMMM Y, HH:mm') }}</span>
                </div>
                @if($laporan->tanggal_verifikasi)
                <div class="info-row">
                    <span class="info-key">Tgl Verifikasi</span>
                    <span class="info-val">{{ $laporan->tanggal_verifikasi->isoFormat('D MMMM Y, HH:mm') }}</span>
                </div>
                @endif
                @if($laporan->catatan_verifikasi && in_array($laporan->status_verifikasi, ['disetujui','ditolak','duplikat']))
                <div class="info-row">
                    <span class="info-key">Catatan Dinas</span>
                    <span class="info-val" style="text-align:right;">{{ $laporan->catatan_verifikasi }}</span>
                </div>
                @endif
            </div>

            {{-- Timeline Status --}}
            <div style="font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#9ca3af;margin-bottom:12px;">Riwayat Status</div>
            <ul class="timeline">
                {{-- Titik awal selalu ada --}}
                <li class="timeline-item">
                    <div class="timeline-dot" style="background:var(--hijau);color:white;">✓</div>
                    <div class="timeline-content">
                        <div class="timeline-label">Laporan Dikirim</div>
                        <div class="timeline-meta">{{ $laporan->created_at->isoFormat('D MMM Y, HH:mm') }} · {{ $laporan->tipe_pelapor === 'masyarakat' ? 'Masyarakat Umum' : ($laporan->tipe_pelapor === 'tokoh_adat' ? 'Tokoh Adat' : 'Petugas Dinas') }}</div>
                    </div>
                </li>

                @foreach($laporan->riwayat as $rw)
                <li class="timeline-item">
                    <div class="timeline-dot" style="background:{{ in_array($rw->status_baru, ['disetujui']) ? 'var(--hijau)' : (in_array($rw->status_baru, ['ditolak','duplikat']) ? 'var(--merah)' : 'var(--emas)') }};color:white;">
                        {{ in_array($rw->status_baru, ['disetujui']) ? '✓' : (in_array($rw->status_baru, ['ditolak','duplikat']) ? '✕' : '→') }}
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-label">
                            {{ $statusConfig[$rw->status_baru]['label'] ?? ucfirst($rw->status_baru) }}
                        </div>
                        <div class="timeline-meta">{{ $rw->created_at->isoFormat('D MMM Y, HH:mm') }}
                            @if($rw->user) · {{ $rw->user->name }} @endif
                        </div>
                        @if($rw->catatan)
                        <div class="timeline-note">{{ $rw->catatan }}</div>
                        @endif
                    </div>
                </li>
                @endforeach

                {{-- Status saat ini jika belum ada riwayat --}}
                @if($laporan->riwayat->isEmpty() && $laporan->status_verifikasi === 'menunggu')
                <li class="timeline-item">
                    <div class="timeline-dot" style="background:#e5e0d8;color:#9ca3af;">⋯</div>
                    <div class="timeline-content">
                        <div class="timeline-label" style="color:#9ca3af;">Menunggu Verifikasi Dinas</div>
                        <div class="timeline-meta">Laporan dalam antrian tim verifikator</div>
                    </div>
                </li>
                @endif
            </ul>

            @if($laporan->status_verifikasi === 'disetujui')
            <div style="background:rgba(45,90,39,0.08);border-left:3px solid var(--hijau);padding:12px 14px;border-radius:0 3px 3px 0;font-size:0.8rem;color:var(--hijau);margin-top:1rem;">
                <strong>🎉 Laporan disetujui!</strong> OPK ini kini telah masuk ke dalam peta resmi Kabupaten Badung dan dapat dipantau oleh Dinas Kebudayaan. Terima kasih atas kontribusi Anda!
            </div>
            @elseif($laporan->status_verifikasi === 'ditolak')
            <div style="background:rgba(192,57,43,0.08);border-left:3px solid var(--merah);padding:12px 14px;border-radius:0 3px 3px 0;font-size:0.8rem;color:var(--merah);margin-top:1rem;">
                <strong>Laporan tidak dapat diproses.</strong> Silakan perbaiki data dan kirim laporan baru jika diperlukan.
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
