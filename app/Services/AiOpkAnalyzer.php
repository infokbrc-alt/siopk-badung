<?php

namespace App\Services;

use App\Models\OpkLaporan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AiOpkAnalyzer
 * 
 * Menggunakan Claude API untuk:
 * 1. Scoring urgensi pemeliharaan OPK (0–10)
 * 2. Deteksi duplikat dengan laporan lain
 * 3. Rekomendasi tindakan ke Dinas
 * 4. Auto-klasifikasi jenis OPK dari deskripsi
 * 5. Generate ringkasan eksekutif mingguan
 */
class AiOpkAnalyzer
{
    private string $apiUrl   = 'https://api.anthropic.com/v1/messages';
    private string $model    = 'claude-sonnet-4-20250514';
    private int    $maxTokens = 1024;

    public function __construct(
        private readonly string $apiKey
    ) {}

    // ─────────────────────────────────────────────
    //  1. ANALISIS UTAMA — dipanggil setelah laporan masuk
    // ─────────────────────────────────────────────
    public function analisisLaporan(OpkLaporan $laporan): array
    {
        // Kumpulkan konteks laporan lain yang sudah disetujui (maks 5 terbaru)
        $laporanLain = OpkLaporan::where('status_verifikasi', 'disetujui')
            ->where('id', '!=', $laporan->id)
            ->latest()
            ->limit(5)
            ->get(['id', 'nama_opk', 'kondisi', 'nama_desa_adat', 'kategori_id'])
            ->map(fn($l) => "- [{$l->id}] {$l->nama_opk} ({$l->nama_desa_adat}, kondisi: {$l->kondisi})")
            ->implode("\n");

        $prompt = <<<PROMPT
Kamu adalah sistem AI untuk Dinas Kebudayaan Kabupaten Badung, Bali.
Tugasmu adalah menganalisis laporan Objek Pemajuan Kebudayaan (OPK) dari masyarakat.

## DATA LAPORAN BARU:
- Nama OPK     : {$laporan->nama_opk}
- Jenis OPK    : {$laporan->kategori?->nama}
- Kondisi      : {$laporan->kondisi}
- Kecamatan    : {$laporan->kecamatan?->nama}
- Desa Adat    : {$laporan->nama_desa_adat}
- Frekuensi    : {$laporan->frekuensi_pelaksanaan}
- Kepemilikan  : {$laporan->status_kepemilikan}
- Deskripsi    : {$laporan->deskripsi_umum}
- Praktisi     : {$laporan->praktisi_nama} (usia: {$laporan->praktisi_usia})

## OPK YANG SUDAH TERDAFTAR (pembanding duplikat):
{$laporanLain}

## INSTRUKSI:
Analisis laporan ini dan berikan output JSON berikut (tanpa penjelasan, tanpa markdown, hanya JSON murni):

{
  "urgency_score": <angka 0.0–10.0, makin tinggi makin mendesak>,
  "duplikat_score": <angka 0.0–100.0 persen kemiripan dengan laporan lain, 0 jika tidak ada>,
  "duplikat_id": <id laporan yang mirip atau null>,
  "rekomendasi": "<kalimat rekomendasi tindakan dalam Bahasa Indonesia, maks 150 kata>",
  "alasan_urgensi": "<penjelasan singkat mengapa skor urgensi tersebut, maks 80 kata>",
  "saran_verifikasi": "<saran untuk verifikator dinas, maks 80 kata>"
}

## PANDUAN SCORING URGENSI:
- 9–10 : Kritis + praktisi tunggal usia lanjut / hampir punah
- 7–9  : Kritis / rusak fisik / tidak ada regenerasi
- 5–7  : Waspada / praktisi berkurang signifikan
- 3–5  : Waspada ringan / perlu pemantauan
- 1–3  : Baik / terpelihara / komunitas aktif
- 0–1  : Sangat baik / sudah ada SK perlindungan
PROMPT;

        $result = $this->callApi($prompt);

        if (!$result['success']) {
            return $this->defaultAnalysis($laporan);
        }

        $data = $this->parseJson($result['content']);

        return [
            'urgency_score'    => min(10, max(0, (float)($data['urgency_score'] ?? 5.0))),
            'duplikat_score'   => min(100, max(0, (float)($data['duplikat_score'] ?? 0))),
            'duplikat_id'      => $data['duplikat_id'] ?? null,
            'rekomendasi'      => $data['rekomendasi'] ?? 'Perlu verifikasi lapangan.',
            'alasan_urgensi'   => $data['alasan_urgensi'] ?? '',
            'saran_verifikasi' => $data['saran_verifikasi'] ?? '',
        ];
    }

    // ─────────────────────────────────────────────
    //  2. CEK DUPLIKAT — bandingkan dua laporan
    // ─────────────────────────────────────────────
    public function cekDuplikat(OpkLaporan $baru, OpkLaporan $lama): float
    {
        $prompt = <<<PROMPT
Bandingkan dua laporan OPK berikut dan tentukan persentase kemiripannya (0–100).
Pertimbangkan nama, lokasi, jenis, dan deskripsi.

## Laporan A (Baru):
Nama: {$baru->nama_opk}
Jenis: {$baru->kategori?->nama}
Lokasi: {$baru->nama_desa_adat}, {$baru->kecamatan?->nama}
Deskripsi: {$baru->deskripsi_umum}

## Laporan B (Sudah ada):
Nama: {$lama->nama_opk}
Jenis: {$lama->kategori?->nama}
Lokasi: {$lama->nama_desa_adat}, {$lama->kecamatan?->nama}
Deskripsi: {$lama->deskripsi_umum}

Jawab HANYA dengan angka desimal 0–100 (contoh: 75.5), tanpa penjelasan.
PROMPT;

        $result = $this->callApi($prompt, 50);
        if (!$result['success']) return 0.0;

        return min(100, max(0, (float) trim($result['content'])));
    }

    // ─────────────────────────────────────────────
    //  3. RINGKASAN EKSEKUTIF MINGGUAN
    // ─────────────────────────────────────────────
    public function ringkasanEksekutif(array $stats): string
    {
        $prompt = <<<PROMPT
Kamu adalah asisten AI untuk Kepala Dinas Kebudayaan Kabupaten Badung.
Buat ringkasan eksekutif mingguan dalam Bahasa Indonesia yang profesional dan ringkas.

## DATA MINGGUAN:
- Total OPK terdaftar : {$stats['total_opk']}
- Laporan baru masuk  : {$stats['laporan_baru']}
- OPK status kritis   : {$stats['kritis']}
- OPK status waspada  : {$stats['waspada']}
- Laporan disetujui   : {$stats['disetujui']}
- Laporan ditolak     : {$stats['ditolak']}
- Menunggu verifikasi : {$stats['menunggu']}
- OPK prioritas tinggi (AI score ≥7): {$stats['prioritas_tinggi']}

## OPK KRITIS TERATAS:
{$stats['opk_kritis_list']}

Tulis ringkasan eksekutif dengan format:
1. Kondisi Umum (2 kalimat)
2. Temuan Penting (2–3 poin)
3. Rekomendasi Tindakan (2–3 poin)
4. Prioritas Minggu Ini (1 poin)

Maksimal 200 kata, nada formal dan faktual.
PROMPT;

        $result = $this->callApi($prompt, 600);
        return $result['success'] ? $result['content'] : 'Ringkasan tidak tersedia.';
    }

    // ─────────────────────────────────────────────
    //  4. AUTO-KLASIFIKASI OPK dari deskripsi
    // ─────────────────────────────────────────────
    public function klasifikasiOtomatis(string $namaOpk, string $deskripsi): ?int
    {
        $prompt = <<<PROMPT
Berdasarkan nama dan deskripsi OPK berikut, tentukan jenis OPK yang paling tepat.

Nama OPK  : {$namaOpk}
Deskripsi : {$deskripsi}

Pilih SALAH SATU nomor dari daftar berikut:
1 = Tradisi Lisan (tutur, cerita rakyat, pantun)
2 = Manuskrip (lontar, babad, naskah kuno)
3 = Adat Istiadat (hukum adat, tata kelola komunitas)
4 = Ritus (upacara keagamaan, ritual siklus hidup)
5 = Pengetahuan Tradisional (pengobatan, etnobotani)
6 = Teknologi Tradisional (subak, tenun, arsitektur)
7 = Seni (tari, musik, ukir, lukis, pertunjukan)
8 = Bahasa (bahasa daerah, dialek, aksara)
9 = Permainan Rakyat (permainan tradisional)
10 = Olahraga Tradisional (seni bela diri, lomba tradisional)

Jawab HANYA dengan angka 1–10, tanpa penjelasan.
PROMPT;

        $result = $this->callApi($prompt, 10);
        if (!$result['success']) return null;

        $num = (int) trim($result['content']);
        return ($num >= 1 && $num <= 10) ? $num : null;
    }

    // ─────────────────────────────────────────────
    //  5. CHAT ASISTEN untuk verifikator
    // ─────────────────────────────────────────────
    public function chatAsisten(string $pertanyaan, OpkLaporan $laporan): string
    {
        $prompt = <<<PROMPT
Kamu adalah asisten AI untuk verifikator Dinas Kebudayaan Kabupaten Badung.
Jawab pertanyaan verifikator tentang laporan OPK berikut.

## KONTEKS LAPORAN:
Nama     : {$laporan->nama_opk}
Jenis    : {$laporan->kategori?->nama}
Kondisi  : {$laporan->kondisi}
Lokasi   : {$laporan->nama_desa_adat}, {$laporan->kecamatan?->nama}
Deskripsi: {$laporan->deskripsi_umum}
AI Score : {$laporan->ai_urgency_score}/10

## PERTANYAAN VERIFIKATOR:
{$pertanyaan}

Jawab dalam Bahasa Indonesia, profesional, maksimal 150 kata.
PROMPT;

        $result = $this->callApi($prompt, 400);
        return $result['success'] ? $result['content'] : 'Maaf, asisten AI tidak tersedia saat ini.';
    }

    // ─────────────────────────────────────────────
    //  PRIVATE HELPERS
    // ─────────────────────────────────────────────
    private function callApi(string $prompt, int $maxTokens = null): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'x-api-key'         => $this->apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model'      => $this->model,
                    'max_tokens' => $maxTokens ?? $this->maxTokens,
                    'messages'   => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if ($response->successful()) {
                $data    = $response->json();
                $content = $data['content'][0]['text'] ?? '';
                return ['success' => true, 'content' => trim($content)];
            }

            Log::error('SIOPK AI API Error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return ['success' => false, 'content' => '', 'error' => $response->body()];

        } catch (\Exception $e) {
            Log::error('SIOPK AI Exception', ['message' => $e->getMessage()]);
            return ['success' => false, 'content' => '', 'error' => $e->getMessage()];
        }
    }

    private function parseJson(string $content): array
    {
        // Bersihkan markdown code block jika ada
        $clean = preg_replace('/```json|```/i', '', $content);
        $clean = trim($clean);

        $data = json_decode($clean, true);
        return is_array($data) ? $data : [];
    }

    private function defaultAnalysis(OpkLaporan $laporan): array
    {
        // Fallback jika API tidak tersedia
        $score = match($laporan->kondisi) {
            'kritis'  => 7.5,
            'waspada' => 4.5,
            default   => 2.0,
        };

        // Bonus score jika praktisi tua & tunggal
        if ($laporan->praktisi_usia && $laporan->praktisi_usia > 60) {
            $score = min(10, $score + 1.5);
        }

        return [
            'urgency_score'    => $score,
            'duplikat_score'   => 0.0,
            'duplikat_id'      => null,
            'rekomendasi'      => "OPK dengan kondisi {$laporan->kondisi} di {$laporan->nama_desa_adat}. Perlu verifikasi lapangan oleh tim Dinas Kebudayaan.",
            'alasan_urgensi'   => "Dihitung berdasarkan kondisi OPK ({$laporan->kondisi}) secara otomatis.",
            'saran_verifikasi' => 'Lakukan kunjungan lapangan untuk memvalidasi data.',
        ];
    }
}
