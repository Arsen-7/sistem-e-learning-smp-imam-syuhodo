<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccessController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('role'), fn ($query) => $query->where('role', $request->role))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.$request->q.'%';
                $query->where(fn ($item) => $item->where('name', 'like', $term)->orWhere('email', 'like', $term));
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.access.index', compact('users'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if(auth()->id() === $user->id && $request->role !== 'admin', 422, 'Akun admin yang sedang digunakan tidak boleh diturunkan rolenya.');

        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'guru', 'wali_kelas', 'siswa'])],
            'status_akun' => ['required', Rule::in(['Aktif', 'Nonaktif'])],
        ]);

        $user->update($validated);

        return back()->with('success', 'Hak akses pengguna berhasil diperbarui.');
    }
}
