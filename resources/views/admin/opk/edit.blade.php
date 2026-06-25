@extends('layouts.app')
@section('title', 'Edit OPK')
@section('page-title', 'Edit Data OPK')

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.opk.show', $laporan) }}" style="font-size:0.8rem;color:var(--emas);text-decoration:none;">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Detail
    </a>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header-custom">
                <span class="title">Edit: {{ Str::limit($laporan->nama_opk, 50) }}</span>
                <span style="font-size:0.72rem;color:#9ca3af;">{{ $laporan->kode_laporan }}</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.opk.update', $laporan) }}" id="form-update-opk">
                    @csrf @method('PUT')

                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Nama OPK <span style="color:var(--merah)">*</span></label>
                        <input type="text" name="nama_opk" class="form-control @error('nama_opk') is-invalid @enderror"
                               value="{{ old('nama_opk', $laporan->nama_opk) }}">
                        @error('nama_opk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Kondisi <span style="color:var(--merah)">*</span></label>
                            <select name="kondisi" class="form-select @error('kondisi') is-invalid @enderror">
                                <option value="baik"    {{ old('kondisi',$laporan->kondisi) === 'baik'    ? 'selected' : '' }}>✅ Baik</option>
                                <option value="waspada" {{ old('kondisi',$laporan->kondisi) === 'waspada' ? 'selected' : '' }}>⚠️ Waspada</option>
                                <option value="kritis"  {{ old('kondisi',$laporan->kondisi) === 'kritis'  ? 'selected' : '' }}>🔴 Kritis</option>
                            </select>
                            @error('kondisi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Status Pelindungan <span style="color:var(--merah)">*</span></label>
                            <select name="status_pelindungan" class="form-select @error('status_pelindungan') is-invalid @enderror">
                                @foreach(['belum_terdaftar'=>'Belum Terdaftar','sudah_didata_dinas'=>'Sudah Didata Dinas','sk_kabupaten'=>'SK Kabupaten','sk_provinsi'=>'SK Provinsi','wbtb_nasional'=>'WBTB Nasional'] as $val => $label)
                                <option value="{{ $val }}" {{ old('status_pelindungan',$laporan->status_pelindungan) === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status_pelindungan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Deskripsi Umum <span style="color:var(--merah)">*</span></label>
                        <textarea name="deskripsi_umum" class="form-control @error('deskripsi_umum') is-invalid @enderror" rows="4">{{ old('deskripsi_umum', $laporan->deskripsi_umum) }}</textarea>
                        @error('deskripsi_umum')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Sejarah & Asal-Usul</label>
                        <textarea name="sejarah_asal_usul" class="form-control" rows="3">{{ old('sejarah_asal_usul', $laporan->sejarah_asal_usul) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Nilai & Makna Budaya</label>
                        <textarea name="nilai_makna_budaya" class="form-control" rows="3">{{ old('nilai_makna_budaya', $laporan->nilai_makna_budaya) }}</textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Latitude (GPS)</label>
                            <input type="text" name="latitude" class="form-control"
                                   value="{{ old('latitude', $laporan->latitude) }}" placeholder="-8.xxxxxx">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;">Longitude (GPS)</label>
                            <input type="text" name="longitude" class="form-control"
                                   value="{{ old('longitude', $laporan->longitude) }}" placeholder="115.xxxxxx">
                        </div>
                    </div>

                </form>

                <div class="d-flex gap-3 mt-3">
                    <button type="submit" form="form-update-opk" class="btn btn-emas px-4">
                        <i class="bi bi-check2 me-2"></i>Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.opk.show', $laporan) }}" class="btn btn-outline-secondary px-4">Batal</a>

                    @if(auth()->user()->isSuperAdmin())
                    <form method="POST" action="{{ route('admin.opk.destroy', $laporan) }}" class="ms-auto"
                          onsubmit="return confirm('Arsipkan OPK ini? Data tidak dihapus permanen.')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-archive me-1"></i>Arsipkan
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header-custom"><span class="title">Info Laporan</span></div>
            <div class="card-body p-0">
                <div style="padding:0 1rem;">
                    @foreach([
                        ['Kode',       $laporan->kode_laporan],
                        ['Jenis OPK',  $laporan->kategori?->ikon.' '.$laporan->kategori?->nama],
                        ['Kecamatan',  $laporan->kecamatan?->nama],
                        ['Desa Adat',  $laporan->nama_desa_adat],
                        ['Pelapor',    $laporan->pelapor_nama],
                        ['Tgl Lapor',  $laporan->created_at->isoFormat('D MMM Y')],
                    ] as [$k,$v])
                    <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0ebe3;font-size:0.8rem;">
                        <span style="color:#9ca3af;width:90px;flex-shrink:0;">{{ $k }}</span>
                        <span style="font-weight:500;text-align:right;">{{ $v }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
