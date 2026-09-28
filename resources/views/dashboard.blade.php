@extends('layouts.spark')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Selamat datang di Sistem Pelayanan PPKS')

@section('content')
    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Selamat datang, {{ auth()->user()->name }}</h2>
                </div>
                <p>
                    Anda login sebagai:
                    @foreach (auth()->user()->getRoleNames() as $role)
                        <span class="badge bg-success">{{ $role }}</span>
                    @endforeach
                </p>
            </div>
        </div>
    </div>

@endsection