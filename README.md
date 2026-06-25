# SIOPK Badung
## Sistem Informasi OPK Kabupaten Badung
> Pemetaan Partisipatif 10 Objek Pemajuan Kebudayaan · UU No. 5 Tahun 2017

---

## Stack Teknologi
| Layer | Teknologi |
|---|---|
| Backend | Laravel 11 |
| Database | MySQL 8 (via XAMPP) |
| Frontend | Bootstrap 5 + Blade |
| Peta | Leaflet.js + OpenStreetMap |
| AI | Claude API (Anthropic) |
| Server Dev | XAMPP (Windows) |

---

## Instalasi di XAMPP

### Prasyarat
- XAMPP dengan PHP 8.2+ dan MySQL aktif
- Composer terinstall

### Langkah Instalasi
```cmd
cd C:\xampp\htdocs\siopk-badung
copy .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

### Buat Database (phpMyAdmin)
```sql
CREATE DATABASE siopk_badung CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Konfigurasi .env (wajib diisi)
```env
APP_URL=http://localhost/siopk-badung/public
DB_DATABASE=siopk_badung
DB_USERNAME=root
DB_PASSWORD=
CLAUDE_API_KEY=sk-ant-api03-xxxxx   ← isi dengan API key asli
APP_TIMEZONE=Asia/Makassar
```

---

## URL Akses

| URL | Keterangan |
|---|---|
| `/lapor` | Form Laporan Masyarakat (Publik) |
| `/lapor/status` | Cek Status Laporan |
| `/login` | Login Admin/Dinas |
| `/admin/dashboard` | Dashboard Eksekutif |
| `/admin/verifikasi` | Antrian Verifikasi |
| `/admin/opk` | Data OPK Resmi |
| `/admin/ai/ringkasan-halaman` | Ringkasan Eksekutif AI |

---

## Akun Default

| Role | Email | Password |
|---|---|---|
| Superadmin | superadmin@siopk-badung.id | SiOPK@2025! |
| Admin | admin@siopk-badung.id | Admin@2025 |
| Verifikator | verifikator@siopk-badung.id | Verif@2025 |
| Petugas | petugas@siopk-badung.id | Petugas@2025 |

---

## Fase 6 — Integrasi Claude AI

### Fitur AI yang Tersedia
| Fitur | Keterangan |
|---|---|
| Auto-analisis laporan | Setiap laporan baru otomatis dianalisis AI |
| Urgency Score (0–10) | Skor urgensi pemeliharaan berbasis kondisi + konteks |
| Deteksi duplikat | Perbandingan otomatis dengan laporan sebelumnya |
| Rekomendasi tindakan | Saran konkret untuk verifikator & kepala dinas |
| Chat asisten | Tanya-jawab tentang laporan spesifik |
| Ringkasan eksekutif | Laporan mingguan AI untuk kepala dinas |
| Auto-klasifikasi | Tebak jenis OPK dari deskripsi |

### Test Koneksi AI
```bash
php artisan siopk:test-ai
```

### Analisis Batch (laporan yang belum punya score)
```bash
php artisan siopk:analisis-semua

# Force analisis ulang semua
php artisan siopk:analisis-semua --force
```

### Scheduler (Windows Task Scheduler)
Tambahkan task setiap 1 menit:
```
php C:\xampp\htdocs\siopk-badung\artisan schedule:run
```

---

## Alur Sistem Lengkap

```
Masyarakat lapor via Form (5 langkah)
         ↓
Database (status: menunggu)
         ↓
AnalisisOpkJob → Claude AI
  ├── Urgency Score
  ├── Deteksi Duplikat
  └── Rekomendasi Tindakan
         ↓
status: review_dinas
         ↓
Verifikator review (dengan panduan AI)
   ├── Setujui → status: disetujui → masuk Peta
   └── Tolak   → status: ditolak  → notif pelapor
         ↓
Dashboard Eksekutif
  ├── Peta Leaflet real-time
  ├── AI Ringkasan Mingguan
  └── Prioritas Pemeliharaan
         ↓
Kepala Dinas / Bupati ambil keputusan
```

---

## Fase Pengembangan

| Fase | Status | Keterangan |
|---|---|---|
| 1 | ✅ Selesai | Setup, database, migrasi, seeder |
| 2 | ✅ Selesai | Auth & role-based access |
| 3 | ✅ Selesai | Form laporan publik 5 langkah |
| 4 | ✅ Selesai | Panel verifikasi Dinas |
| 5 | ✅ Selesai | Dashboard + WebGIS Leaflet |
| 6 | ✅ Selesai | Integrasi Claude AI |
| 7 | 🔜 Berikutnya | Notifikasi WhatsApp otomatis |

---

## Perintah Berguna

```bash
# Reset database + seed ulang
php artisan migrate:fresh --seed

# Clear semua cache
php artisan optimize:clear

# Test AI
php artisan siopk:test-ai

# Analisis laporan yang belum di-score
php artisan siopk:analisis-semua

# Jalankan scheduler manual
php artisan schedule:run
```
