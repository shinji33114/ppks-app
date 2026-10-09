<?php

use App\Models\User;

it('memakai foto Alun-Alun sebagai latar halaman setelah login', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('class="bg-foto"', false)
        ->assertSee('images/dashboard-bg.jpg', false);

    expect(file_exists(public_path('images/dashboard-bg.jpg')))->toBeTrue();
});

it('menampilkan avatar kosong pada semua bagian profil dashboard', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('sidebar-profile-img profile-placeholder', false)
        ->assertSee('navbar-profile-img profile-placeholder', false)
        ->assertDontSee('spark-admin/assets/images/avatar.png');
});
