<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(20)->withQueryString();
        return view('admin.pengguna.index', compact('users'));
    }

    public function create()
    {
        return view('admin.pengguna.form', ['user' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:superadmin,admin,verifikator,petugas',
            'nip'      => 'nullable|string|max:30',
            'no_hp'    => 'nullable|string|max:20',
            'instansi' => 'nullable|string|max:150',
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'email.unique'       => 'Email sudah terdaftar.',
        ]);

        User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role'      => $validated['role'],
            'nip'       => $validated['nip'] ?? null,
            'no_hp'     => $validated['no_hp'] ?? null,
            'instansi'  => $validated['instansi'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('admin.pengguna.index')
                         ->with('success', "Pengguna {$validated['name']} berhasil ditambahkan.");
    }

    public function edit(User $pengguna)
    {
        // Cegah edit superadmin lain kecuali diri sendiri
        if ($pengguna->isSuperAdmin() && auth()->id() !== $pengguna->id) {
            return back()->with('error', 'Tidak dapat mengedit akun superadmin lain.');
        }
        return view('admin.pengguna.form', ['user' => $pengguna]);
    }

    public function update(Request $request, User $pengguna)
    {
        if ($pengguna->isSuperAdmin() && auth()->id() !== $pengguna->id) {
            return back()->with('error', 'Tidak dapat mengedit akun superadmin lain.');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:150',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($pengguna->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|in:superadmin,admin,verifikator,petugas',
            'nip'      => 'nullable|string|max:30',
            'no_hp'    => 'nullable|string|max:20',
            'instansi' => 'nullable|string|max:150',
            'is_active'=> 'boolean',
        ]);

        $pengguna->name      = $validated['name'];
        $pengguna->email     = $validated['email'];
        $pengguna->role      = $validated['role'];
        $pengguna->nip       = $validated['nip'] ?? $pengguna->nip;
        $pengguna->no_hp     = $validated['no_hp'] ?? $pengguna->no_hp;
        $pengguna->instansi  = $validated['instansi'] ?? $pengguna->instansi;
        $pengguna->is_active = $request->boolean('is_active', true);

        if (!empty($validated['password'])) {
            $pengguna->password = Hash::make($validated['password']);
        }

        $pengguna->save();

        return redirect()->route('admin.pengguna.index')
                         ->with('success', "Data pengguna {$pengguna->name} berhasil diperbarui.");
    }

    public function toggleAktif(User $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menonaktifkan akun sendiri.');
        }
        $pengguna->update(['is_active' => !$pengguna->is_active]);
        $status = $pengguna->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Pengguna {$pengguna->name} berhasil {$status}.");
    }

    public function destroy(User $pengguna)
    {
        if ($pengguna->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        if ($pengguna->isSuperAdmin()) {
            return back()->with('error', 'Akun superadmin tidak dapat dihapus.');
        }
        $nama = $pengguna->name;
        $pengguna->delete();
        return redirect()->route('admin.pengguna.index')
                         ->with('success', "Pengguna {$nama} berhasil dihapus.");
    }
}
