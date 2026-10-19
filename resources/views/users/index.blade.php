@extends('layouts.spark')

@section('page-title', 'Manajemen Pengguna')
@section('page-subtitle', 'Kelola akun dan hak akses pengguna sistem')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Daftar Pengguna</h5>
            <a href="{{ route('users.create') }}" class="btn-custom btn-custom-primary">
                <i class="bi bi-person-plus me-1"></i> Tambah Pengguna
            </a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                {{ $user->getRoleNames()->map(fn ($role) => $role === 'super-admin' ? 'Super Admin' : 'Admin')->join(', ') ?: 'Belum ada role' }}
                            </td>
                            <td>
                                <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('users.toggle-active', $user) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $user->is_active ? 'danger' : 'success' }}">
                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $users->links() }}</div>
    </div>
@endsection
