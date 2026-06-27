# Security Audit Report — SIOPK Badung

**Status**: Draft  
**Tanggal Audit**: 2026-06-28  
**Auditor**: Code Review Otomatis  
**Versi Codebase**: `main`

---

## Executive Summary

Ditemukan **15 temuan** dengan 3 kritis (SEV-1) terkait kebocoran kredensial, 3 serius (SEV-2) terkait error handling, 3 moderat (SEV-3) terkait desain, dan 6 minor (SEV-4) terkait kualitas kode. Tidak ditemukan celah injeksi SQL atau XSS aktif, namun terdapat kelemahan pada sanitasi input, manajemen secret, dan ketahanan error.

---

## Severity Distribution

| Severity | Count |
|----------|-------|
| SEV-1 (Critical) | 3 |
| SEV-2 (High) | 3 |
| SEV-3 (Medium) | 3 |
| SEV-4 (Low) | 6 |

---

## Temuan Detail

### SEV-1: Critical

---

#### 1. API Key & Token Tersimpan di `.env` yang Tercatat di Git

**Lokasi**: `.env:51,71`  
**Kategori**: Security Misconfiguration (OWASP #5), Identification & Auth Failures (OWASP #7)

File `.env` berada di dalam git repository dan berisi kredensial produksi:

```
DEEPSEEK_API_KEY=sk-***REDACTED***
FONNTE_TOKEN=***REDACTED***
```

**Dampak**: Siapa pun dengan akses ke repository dapat menggunakan API key untuk memanggil DeepSeek API atas biaya pemilik, atau mengirim WhatsApp melalui akun Fonnte.

**Rekomendasi**:
1. Rotasi (regenerate) semua API key yang terekspos **segera**
2. Hapus `.env` dari git: `git rm --cached .env`
3. Pastikan `.gitignore` berisi `.env`
4. Gunakan `.env.example` dengan placeholder kosong sebagai referensi
5. Pertimbangkan secret manager (HashiCorp Vault, AWS Secrets Manager) untuk production

---

#### 2. Webhook Fonnte Tanpa Validasi Source / Signature

**Lokasi**: `app/Http/Controllers/FonnteWebhookController.php:10-39`  
**Kategori**: Broken Access Control (OWASP #1), Insecure Design (OWASP #4)

`deviceStatus()` dan `incomingMessage()` menerima POST dari siapa pun tanpa memverifikasi bahwa request berasal dari Fonnte:

```php
// app/Http/Controllers/FonnteWebhookController.php:10-13
public function deviceStatus(Request $request): \Illuminate\Http\JsonResponse
{
    $data = $request->all();
    Log::warning('Fonnte device status changed', $data);
    // Tidak ada validasi token/signature/hmac
```

**Dampak**: Attacker dapat mengirim payload palsu untuk:
- Memicu pengiriman WhatsApp notifikasi ke admin melalui `SendWhatsAppNotifJob`
- Mem-banjiri log dengan data palsu
- Potensi DoS via dispatch job berulang

**Rekomendasi**:
1. Verifikasi `X-Fonnte-Token` atau webhook signature sesuai dokumentasi Fonnte
2. Tolak seluruh request yang tidak membawa signature valid dengan HTTP 401

---

#### 3. Content Security Policy Hanya Report-Only

**Lokasi**: `app/Http/Middleware/SecurityHeaders.php:28-37`  
**Kategori**: Security Misconfiguration (OWASP #5)

Header yang diset adalah `Content-Security-Policy-Report-Only`, bukan `Content-Security-Policy`:

```php
// SecurityHeaders.php:28
$response->headers->set('Content-Security-Policy-Report-Only',
```

**Dampak**: Kebijakan CSP tidak ditegakkan — browser hanya melaporkan pelanggaran, tidak memblokir. XSS tetap dapat dieksekusi.

**Rekomendasi**:
1. Uji CSP dengan Report-Only di staging selama 1-2 minggu, pantau laporan
2. Ganti ke `Content-Security-Policy` enforce setelah tidak ada false positive
3. Tambahkan `media-src` dan `worker-src` directives
4. Hapus `X-XSS-Protection` header (deprecated, dapat menyebabkan masalah keamanan di browser modern)

---

### SEV-2: High

---

#### 4. Generic Exception Catch Tanpa Stack Trace Log

**Lokasi**: `app/Http/Controllers/Publik/LaporController.php:80-84`  
**Kategori**: Security Logging & Monitoring Failures (OWASP #9)

```php
// LaporController.php:80-83
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('Gagal menyimpan laporan OPK', ['error' => $e->getMessage()]);
    return back()->with('error', 'Terjadi kesalahan saat menyimpan laporan. Silakan coba lagi.')->withInput();
}
```

Hanya `getMessage()` yang dicatat, tanpa stack trace. Saat error terjadi di production, root cause sangat sulit dilacak.

**Rekomendasi**:
```php
Log::error('Gagal menyimpan laporan OPK', [
    'error'   => $e->getMessage(),
    'trace'   => $e->getTraceAsString(),
    'file'    => $e->getFile(),
    'line'    => $e->getLine(),
]);
```

---

#### 5. Exception di VerifikasiController Tidak Dicatat Sama Sekali

**Lokasi**: `app/Http/Controllers/Admin/VerifikasiController.php:52-54,68-70`  
**Kategori**: Security Logging & Monitoring Failures (OWASP #9)

```php
// VerifikasiController.php:52-54
} catch (\Exception $e) {
    return back()->with('error', 'Gagal memverifikasi laporan. Silakan coba lagi.');
}
```

Exception **tidak dicatat** — jika verifikasi gagal karena bug atau constraint violation, tidak ada jejak sama sekali.

**Rekomendasi**: Tambahkan `Log::error()` dengan konteks `laporan_id`, `user_id`, dan `$e->getMessage()` + trace.

---

#### 6. Potensi Data Loss pada Update OPK Tanpa Transaction

**Lokasi**: `app/Http/Controllers/Admin/OpkController.php:60-91`  
**Kategori**: Software & Data Integrity Failures (OWASP #8)

`OpkController::update()` melakukan operasi bertahap tanpa `DB::transaction()`:

1. Update data laporan (line 72) — sukses
2. Hapus foto via `$this->mediaService->deleteFotos()` (line 75) — sukses, file terhapus
3. Upload foto baru (line 77-83) — **gagal** karena `\RuntimeException`
4. Rollback manual hanya mengembalikan input form, **foto yang sudah dihapus tidak dapat dikembalikan**

**Rekomendasi**:
```php
DB::transaction(function () use ($request, $laporan, $validated) {
    $laporan->update(...);
    $this->mediaService->deleteFotos(...);
    if ($request->hasFile('fotos')) {
        $this->mediaService->uploadFotos(...);
    }
});
```

Pastikan `deleteFotos()` hanya menghapus dari database, dan file dihapus via observer/deferred deletion.

---

### SEV-3: Medium

---

#### 7. Contract Interfaces Didefinisikan Tapi Tidak Pernah Digunakan

**Lokasi**: `app/Contracts/*`, seluruh controller

`AppServiceProvider::register()` melakukan binding:

```php
// AppServiceProvider.php:32-35
$this->app->bind(OpkStatsServiceInterface::class, OpkStatsService::class);
$this->app->bind(PetaDataServiceInterface::class, PetaDataService::class);
$this->app->bind(LaporanServiceInterface::class, LaporanService::class);
$this->app->bind(VerifikasiServiceInterface::class, VerifikasiService::class);
```

Namun seluruh controller meng-inject concrete class langsung:

```php
// Semua controller menggunakan:
public function __construct(private readonly LaporanService $laporanService) {}
// Bukan:
public function __construct(private readonly LaporanServiceInterface $laporanService) {}
```

**Dampak**: Dependency Inversion Principle (DIP) dilanggar. Contract menjadi dead code. Tidak ada kemudahan untuk mocking di tes.

**Rekomendasi**: Ganti seluruh controller untuk type-hint terhadap interface, atau hapus contracts jika tidak diperlukan.

---

#### 8. Magic String untuk Status — Enum Tidak Konsisten

**Lokasi**: Tersebar di `VerifikasiService.php`, `AnalisisOpkJob.php`, `SidebarComposer.php`

`StatusVerifikasi` enum sudah didefinisikan (`app/Enums/StatusVerifikasi.php`), tetapi:

| File | Status yang dipakai | Seharusnya |
|------|-------------------|------------|
| `VerifikasiService.php:33` | `'disetujui'` | `StatusVerifikasi::Disetujui->value` |
| `AnalisisOpkJob.php:44` | `'ai_review'` | `StatusVerifikasi::AiReview->value` |
| `AnalisisOpkJob.php:58,82` | `'review_dinas'` | `StatusVerifikasi::ReviewDinas->value` |
| `SidebarComposer.php:16` | `['menunggu', 'ai_review', 'review_dinas']` | `[StatusVerifikasi::Menunggu->value, ...]` |

**Dampak**: Typo pada string literal sulit dideteksi dan tidak ada autocomplete IDE. Consistency rendah.

**Rekomendasi**: `sed` seluruh string literal status dengan referensi enum.

---

#### 9. Duplikasi Kode: WilayahController vs Individual Controllers

**Lokasi**: `app/Http/Controllers/Admin/WilayahController.php` vs `KecamatanController.php`, `DesaDinasController.php`, `DesaAdatController.php`

Kedua set controller memiliki logika CRUD yang identik:

```
WilayahController:
  storeKecamatan()   ≈ KecamatanController::store()
  updateKecamatan()  ≈ KecamatanController::update()
  destroyKecamatan() ≈ KecamatanController::destroy()
  storeDesaDinas()   ≈ DesaDinasController::store()
  ...
```

Di `routes/web.php`, **keduanya didaftarkan** dengan route berbeda:

```php
// routes/web.php:118-128
// WilayahController routes (prefix wilayah/)
Route::post('/kecamatan',           [KecamatanController::class, 'store'])...
// KecamatanController routes (prefix wilayah/)
```

**Dampak**: Route potensial tumpang tindih. Setiap perubahan harus dilakukan di dua tempat. Bug risk meningkat.

**Rekomendasi**: Pilih salah satu pola — semua route via `WilayahController` (combined) atau hapus `WilayahController` dan gunakan individual controller saja.

---

### SEV-4: Low

---

#### 10. `Log::warning` untuk Event Bisnis Normal

**Lokasi**: `app/Listeners/SideEffectHandler.php:25`, `app/Jobs/SendWhatsAppNotifJob.php:29`

```php
// SideEffectHandler.php:25
Log::warning("[SIOPK] Laporan dibuat: {$event->laporan->kode_laporan}, WA: {$event->laporan->pelapor_whatsapp}");
```

Pembuatan laporan baru adalah event bisnis normal, bukan warning. Ini membuat level logging tidak bermakna — warning menjadi noise.

**Rekomendasi**: `Log::warning` → `Log::info`. Juga lepaskan `WA: ...` dari log (lihat temuan #12).

---

#### 11. PII (WhatsApp Number) Tercatat di Log

**Lokasi**: `app/Listeners/SideEffectHandler.php:25`

Nomor WhatsApp pelapor ditulis ke log dalam plain text.

**Dampak**: Pelanggaran privasi data. Log sering dirotasi ke storage yang kurang terkontrol. Bisa melanggar regulasi perlindungan data.

**Rekomendasi**: Log hanya `kode_laporan` dan `id`. Jika perlu identifikasi pelapor, gunakan ID numerik atau hash.

---

#### 12. `StoreLaporanRequest` — Tahun Maksimum Hardcoded ke `date('Y')`

**Lokasi**: `app/Http/Requests/StoreLaporanRequest.php:21`

```php
'tahun_diketahui' => 'nullable|integer|min:1|max:' . date('Y'),
```

Tahun maksimum selalu tahun berjalan — di 1 Januari 2027 aturan ini berubah sendiri. Edge case: laporan yang diajukan di Desember dengan tahun_diketahui yang sebenarnya tahun depan tidak bisa.

**Rekomendasi**: `max:` . (date('Y') + 1) atau batas atas tetap (misal 2100).

---

#### 13. Test Coverage Rendah

**Lokasi**: `tests/` — hanya 6 file test

| Directory | File Count |
|-----------|-----------|
| Unit | 2 |
| Feature | 3 |
| TestCase | 1 |

Untuk codebase 70+ file dengan 10 model, 7 service, 3 job, 3 event, dan 16 controller — coverage jauh di bawah standar 80%.

**Rekomendasi**: Prioritaskan tes untuk:
1. `VerifikasiService` (update status, transaction rollback)
2. `LaporanService` (create Laporan with all fields)
3. `AnalisisOpkJob` (success path, failure path, missing laporan)
4. `OpkMediaService` (foto limit enforcement)

---

#### 14. Inkonsistensi Method Signature pada LaporanService

**Lokasi**: `app/Services/LaporanService.php:49,68`

```php
public function uploadFotos(OpkLaporan $laporan, array $files, ?string $keteranganUtama = null): void
public function uploadDokumen(OpkLaporan $laporan, UploadedFile $dokumen): void
```

`uploadFotos` menerima `array $files` (array of file), sedangkan `uploadDokumen` menerima `UploadedFile $dokumen` (single file). Tidak simetris.

**Rekomendasi**: Buat keduanya menerima `array $files` untuk konsistensi dan kemudahan perluasan (multi-dokumen di masa depan).

---

#### 15. Cache Invalidation Ad-Hoc di Setiap Controller

**Lokasi**: Seluruh controller admin (`KecamatanController`, `DesaDinasController`, `DesaAdatController`, `KategoriController`, `PenggunaController`)

Setiap metode `store()`, `update()`, `destroy()` memanggil `Cache::forget()` secara manual:

```php
// KecamatanController.php:36,50,60
Cache::forget('kecamatan_list');
Cache::forget('kecamatan_with_desa');
```

**Dampak**: Gampang ada yang terlewat saat menambah fitur. Tidak ada single source of truth untuk cache invalidation.

**Rekomendasi**: Gunakan model events (observer) atau centralized cache invalidation service. Alternatif: Laravel cache tags (`Cache::tags(['wilayah'])->flush()`).

---

## Ringkasan OWASP Top 10 Coverage

| OWASP # | Kategori | Status | Temuan Terkait |
|---------|----------|--------|----------------|
| 1 | Broken Access Control | **Terdampak** | #2 (webhook tanpa signature) |
| 2 | Cryptographic Failures | OK | — |
| 3 | Injection | OK | Validasi form request + Eloquent parameterized queries |
| 4 | Insecure Design | **Terdampak** | #2 (webhook design) |
| 5 | Security Misconfiguration | **Terdampak** | #1 (exposed secrets), #3 (CSP report-only) |
| 6 | Vulnerable Components | OK | Perlu dependency scan rutin |
| 7 | Identification & Auth Failures | **Terdampak** | #1 (API key bocor) |
| 8 | Software & Data Integrity Failures | **Terdampak** | #6 (data loss tanpa transaction) |
| 9 | Security Logging & Monitoring | **Terdampak** | #4, #5 (logging tidak memadai), #12 (PII di log) |
| 10 | SSRF | OK | Tidak ada fetch URL dari user input |

---

## Hal yang Sudah Baik

- **Rate limiting** pada login (`throttle:5,1`), lapor (`throttle:3,1`), dan API publik (`throttle:30,1`)
- **Input validation** komprehensif melalui FormRequest dengan custom message Bahasa Indonesia
- **Eloquent parameterized queries** — tidak ada raw SQL dari user input (mencegah SQL injection)
- **XSS prevention** via `e()` helper di output API dan Blade templating
- **Role-based access control** via Policy + Middleware dengan granular permission
- **Soft delete + observer cleanup** — data tidak dihapus permanen tanpa pemulihan
- **Security headers** base (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`) sudah dipasang
- **Model::shouldBeStrict()** di non-production environment
- **CSV export streaming** via `cursor()` — memory-efficient
- **AI Provider strategy pattern** — mudah mengganti provider tanpa ubah kode consumer

---

## Action Items (Prioritas)

| # | Action | Severity | Estimasi |
|---|--------|----------|----------|
| 1 | Rotasi semua API key + hapus `.env` dari git | SEV-1 | 15 menit |
| 2 | Tambahkan webhook signature validation di Fonnte | SEV-1 | 30 menit |
| 3 | Ganti CSP Report-Only → enforce + hapus X-XSS-Protection | SEV-1 | 1 jam |
| 4 | Tambahkan `Log::error()` di semua catch block tanpa log | SEV-2 | 30 menit |
| 5 | Wrap `OpkController::update()` file ops dalam `DB::transaction()` | SEV-2 | 1 jam |
| 6 | Ganti semua string literal status dengan enum | SEV-3 | 2 jam |
| 7 | Controller pakai interface / hapus contracts jika tidak jadi | SEV-3 | 1 jam |
| 8 | Hapus duplikasi controller wilayah | SEV-3 | 2 jam |
| 9 | `Log::warning` → `Log::info` + hapus PII dari log | SEV-4 | 15 menit |
| 10 | Tulis tes minimal untuk service dan job kritis | SEV-4 | 4 jam |
| 11 | Implementasi cache tags atau centralized invalidation | SEV-4 | 3 jam |
| 12 | Reguler dependency scan (`composer audit`, Dependabot) | SEV-4 | Ongoing |

---

## Kesimpulan

Codebase SIOPK Badung memiliki arsitektur yang solid dengan validasi input yang baik, RBAC yang rapi, dan pola AI provider yang extensible. Tidak ditemukan celah SQL injection atau XSS aktif. Namun, **kebocoran kredensial** (SEV-1) harus segera ditangani, **error handling** perlu diperkuat, dan **konsistensi kode** (enum, interface usage, cache) perlu distandarisasi.

Nilai total: **68/100** (C+). Setelah SEV-1 dan SEV-2 ditangani, diharapkan naik ke **85/100** (B+).
