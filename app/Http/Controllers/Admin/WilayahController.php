<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Kecamatan, DesaDinas, DesaAdat};
use Illuminate\Http\Request;

class WilayahController extends Controller
{
    public function index(Request $request)
    {
        $kecamatans = Kecamatan::withCount(['desaDinas', 'desaAdats'])
            ->orderBy('nama')->get();

        $selectedId   = $request->kecamatan_id ?? $kecamatans->first()?->id;
        $selectedKec  = $selectedId ? Kecamatan::with(['desaDinas', 'desaAdats'])->find($selectedId) : null;
        $desaDinas    = $selectedKec ? $selectedKec->desaDinas()->orderBy('nama')->get() : collect();
        $desaAdats    = $selectedKec ? $selectedKec->desaAdats()->orderBy('nama')->get() : collect();

        return view('admin.wilayah.index', compact(
            'kecamatans', 'selectedKec', 'selectedId',
            'desaDinas', 'desaAdats'
        ));
    }

    // ── Kecamatan ──
    public function storeKecamatan(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:kecamatans,kode',
        ], ['kode.unique' => 'Kode kecamatan sudah digunakan.']);

        $kec = Kecamatan::create($validated);
        return redirect()->route('admin.wilayah.index', ['kecamatan_id' => $kec->id])
            ->with('success', "Kecamatan {$kec->nama} berhasil ditambahkan.");
    }

    public function updateKecamatan(Request $request, Kecamatan $kecamatan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => "required|string|max:20|unique:kecamatans,kode,{$kecamatan->id}",
        ], ['kode.unique' => 'Kode kecamatan sudah digunakan.']);

        $kecamatan->update($validated);
        return back()->with('success', "Kecamatan {$kecamatan->nama} berhasil diperbarui.");
    }

    public function destroyKecamatan(Kecamatan $kecamatan)
    {
        $nama = $kecamatan->nama;
        try {
            $kecamatan->delete();
            return redirect()->route('admin.wilayah.index')
                ->with('success', "Kecamatan {$nama} dan semua desa/desa adat terkait berhasil dihapus.");
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', "Kecamatan {$nama} tidak dapat dihapus karena masih memiliki data OPK terkait. Pindahkan atau hapus data OPK terlebih dahulu.");
        }
    }

    // ── Desa Dinas ──
    public function storeDesaDinas(Request $request)
    {
        $validated = $request->validate([
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'nama'         => 'required|string|max:100',
        ]);

        DesaDinas::create($validated);
        return back()->with('success', "Desa Dinas {$validated['nama']} berhasil ditambahkan.");
    }

    public function updateDesaDinas(Request $request, DesaDinas $desaDina)
    {
        $validated = $request->validate(['nama' => 'required|string|max:100']);
        $desaDina->update($validated);
        return back()->with('success', "Desa Dinas {$desaDina->nama} berhasil diperbarui.");
    }

    public function destroyDesaDinas(DesaDinas $desaDina)
    {
        $desaDina->delete();
        return back()->with('success', "Desa Dinas {$desaDina->nama} berhasil dihapus.");
    }

    // ── Desa Adat ──
    public function storeDesaAdat(Request $request)
    {
        $validated = $request->validate([
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'nama'         => 'required|string|max:150',
        ]);

        DesaAdat::create($validated);
        return back()->with('success', "Desa Adat {$validated['nama']} berhasil ditambahkan.");
    }

    public function updateDesaAdat(Request $request, DesaAdat $desaAdat)
    {
        $validated = $request->validate(['nama' => 'required|string|max:150']);
        $desaAdat->update($validated);
        return back()->with('success', "Desa Adat {$desaAdat->nama} berhasil diperbarui.");
    }

    public function destroyDesaAdat(DesaAdat $desaAdat)
    {
        $desaAdat->delete();
        return back()->with('success', "Desa Adat {$desaAdat->nama} berhasil dihapus.");
    }
}
