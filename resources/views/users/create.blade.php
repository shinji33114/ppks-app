@extends('layouts.spark')

@section('page-title', 'Tambah Pengguna')
@section('page-subtitle', 'Buat akun baru dan tentukan hak aksesnya')

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label-custom">Nama</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="form-control-custom" required>
                    @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label-custom">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control-custom" required>
                    @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label-custom">Role</label>
                    <select id="role" name="role" class="form-control-custom" required>
                        <option value="">- Pilih role -</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                        @endforeach
                    </select>
                    @error('role')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="password" class="form-label-custom">Password</label>
                        <input id="password" type="password" name="password" class="form-control-custom" required>
                        @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label-custom">Konfirmasi Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control-custom" required>
                    </div>
                </div>
                <a href="{{ route('users.index') }}" class="btn btn-light">Batal</a>
                <button type="submit" class="btn-custom btn-custom-primary">Simpan Pengguna</button>
            </form>
        </div>
    </div>
@endsection
