<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $users = User::with('roles')->latest()->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create', ['roles' => ['admin', 'super-admin']]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'super-admin'])],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_active' => true,
        ]);
        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', 'Akun pengguna berhasil dibuat.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        if ($user->hasRole('super-admin') && $user->is_active
            && User::role('super-admin')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'Harus ada setidaknya satu akun pengelola utama yang aktif.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Status akun berhasil diperbarui.');
    }
}
