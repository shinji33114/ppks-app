@extends('layouts.spark')

@section('page-title', 'Profil Saya')
@section('page-subtitle', 'Kelola informasi akun dan kata sandi Anda')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-light shadow-sm p-4 mb-4">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="card border-light shadow-sm p-4 mb-4">
            @include('profile.partials.update-password-form')
        </div>

        <div class="card border-light shadow-sm p-4">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection