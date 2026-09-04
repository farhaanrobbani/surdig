<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->query('role')))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$request->query('q')}%")
                ->orWhere('email', 'like', "%{$request->query('q')}%")))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => [User::ROLE_STAFF => 'Staf', User::ROLE_OPERATOR => 'Operator', User::ROLE_KEPALA => 'Kepala', User::ROLE_SUPERADMIN => 'Superadmin'],
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => [User::ROLE_STAFF => 'Staf', User::ROLE_OPERATOR => 'Operator', User::ROLE_KEPALA => 'Kepala', User::ROLE_SUPERADMIN => 'Superadmin'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'],
            'is_active' => $request->boolean('is_active'),
            'email_verified_at' => now(),
            'nip' => $data['nip'] ?? null,
            'jabatan' => $data['jabatan'] ?? null,
            'pangkat' => $data['pangkat'] ?? null,
            'ruang_golongan' => $data['ruang_golongan'] ?? null,
            'grade_tukin' => $data['grade_tukin'] ?? 8,
            'jumlah_tukin_kotor' => $data['jumlah_tukin_kotor'] ?? 0,
            'jumlah_tukin_bersih' => $data['jumlah_tukin_bersih'] ?? 0,
            'gapok' => $data['gapok'] ?? 0,
            'jumlah_uang_makan_harian' => $data['jumlah_uang_makan_harian'] ?? 35150,
            'foto_profil_url' => $request->hasFile('foto_profil') ? $request->file('foto_profil')->store('users/photos', 'public') : null,
            'instansi' => $data['instansi'] ?? 'KUA Ampelgading',
        ]);

        return redirect()->route('users.index')
            ->with('success', 'Akun berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => [User::ROLE_STAFF => 'Staf', User::ROLE_OPERATOR => 'Operator', User::ROLE_KEPALA => 'Kepala', User::ROLE_SUPERADMIN => 'Superadmin'],
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Anda tidak dapat mengubah akun sendiri dari sini.']);
        }

        $data = $this->validateData($request, $user);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->is_active = $request->boolean('is_active');
        $user->nip = $data['nip'] ?? null;
        $user->jabatan = $data['jabatan'] ?? null;
        $user->pangkat = $data['pangkat'] ?? null;
        $user->ruang_golongan = $data['ruang_golongan'] ?? null;
        $user->grade_tukin = $data['grade_tukin'] ?? 8;
        $user->jumlah_tukin_kotor = $data['jumlah_tukin_kotor'] ?? 0;
        $user->jumlah_tukin_bersih = $data['jumlah_tukin_bersih'] ?? 0;
        $user->gapok = $data['gapok'] ?? 0;
        $user->jumlah_uang_makan_harian = $data['jumlah_uang_makan_harian'] ?? 35150;
        $user->instansi = $data['instansi'] ?? 'KUA Ampelgading';

        if ($request->hasFile('foto_profil')) {
            $this->deleteFoto($user);
            $user->foto_profil_url = $request->file('foto_profil')->store('users/photos', 'public');
        } elseif ($request->boolean('foto_hapus')) {
            $this->deleteFoto($user);
            $user->foto_profil_url = null;
        }

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['user' => 'Anda tidak dapat menghapus akun sendiri.']);
        }

        $this->deleteFoto($user);

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Akun berhasil dihapus.');
    }

    private function validateData(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::in([User::ROLE_STAFF, User::ROLE_OPERATOR, User::ROLE_KEPALA, User::ROLE_SUPERADMIN])],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['sometimes', 'boolean'],
            'nip' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'pangkat' => ['nullable', 'string', 'max:255'],
            'ruang_golongan' => ['nullable', 'string', 'max:50'],
            'grade_tukin' => ['nullable', 'integer', 'min:0', 'max:30'],
            'jumlah_tukin_kotor' => ['nullable', 'numeric', 'min:0'],
            'jumlah_tukin_bersih' => ['nullable', 'numeric', 'min:0'],
            'gapok' => ['nullable', 'numeric', 'min:0'],
            'jumlah_uang_makan_harian' => ['nullable', 'numeric', 'min:0'],
            'instansi' => ['nullable', 'string', 'max:255'],
            'foto_profil' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072'],
            'foto_hapus' => ['sometimes', 'in:1'],
        ]);
    }

    private function deleteFoto(User $user): void
    {
        if ($user->foto_profil_url && Storage::disk('public')->exists($user->foto_profil_url)) {
            Storage::disk('public')->delete($user->foto_profil_url);
        }
    }
}
