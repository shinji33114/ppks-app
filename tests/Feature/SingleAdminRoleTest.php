<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
});

it('allows admin and super admin to use workflow areas but restricts account management to super admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    foreach ([
        'clients.index',
        'clients.create',
        'assessment.index',
        'penanganan.dashboard',
        'exit-documents.mine',
    ] as $route) {
        $this->actingAs($admin)->get(route($route))->assertOk();
        $this->actingAs($superAdmin)->get(route($route))->assertOk();
    }

    $this->actingAs($admin)->get(route('users.index'))->assertForbidden();
    $this->actingAs($admin)->get(route('users.create'))->assertForbidden();
    $this->actingAs($superAdmin)->get(route('users.index'))->assertOk();
    $this->actingAs($superAdmin)->get(route('users.create'))->assertOk()
        ->assertSee('Pengelola utama');

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Ringkasan Layanan')
        ->assertDontSee('<h1 class="page-title">Ringkasan Layanan</h1>', false)
        ->assertSee('Total Penerima Manfaat')
        ->assertDontSee('Kelola Akun Pengguna')
        ->assertDontSee('Pengguna</a>');

    $this->actingAs($superAdmin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Kelola Akun Pengguna');

    $this->actingAs($admin)
        ->get(route('statistik.index'))
        ->assertRedirect(route('dashboard'));
});

it('lets only super admins create accounts with either role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Pengguna Ditolak',
        'email' => 'denied@example.com',
        'role' => 'super-admin',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertForbidden();

    foreach (['admin', 'super-admin'] as $role) {
        $this->actingAs($superAdmin)->post(route('users.store'), [
            'name' => 'Akun ' . $role,
            'email' => $role . '@example.com',
            'role' => $role,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('users.index'));

        expect(User::where('email', $role . '@example.com')->firstOrFail()->hasRole($role))->toBeTrue();
    }
});

it('migrates users assigned legacy roles to the admin role', function () {
    $petugasRole = Role::create(['name' => 'petugas-piket', 'guard_name' => 'web']);
    $kepalaRole = Role::create(['name' => 'kepala-rps', 'guard_name' => 'web']);

    $petugas = User::factory()->create();
    $petugas->assignRole($petugasRole);
    $kepala = User::factory()->create();
    $kepala->assignRole($kepalaRole);

    $migration = require base_path('database/migrations/2026_10_14_000001_consolidate_user_roles_to_admin.php');
    $migration->up();

    expect(Role::query()->pluck('name')->all())->toBe(['admin'])
        ->and($petugas->fresh()->getRoleNames()->all())->toBe(['admin'])
        ->and($kepala->fresh()->getRoleNames()->all())->toBe(['admin']);
});

it('promotes the lowest-id admin to super admin during the role migration', function () {
    $firstAdmin = User::factory()->create();
    $firstAdmin->assignRole('admin');
    $nextAdmin = User::factory()->create();
    $nextAdmin->assignRole('admin');

    $migration = require base_path('database/migrations/2026_10_14_000003_add_super_admin_role.php');
    $migration->up();

    expect($firstAdmin->fresh()->getRoleNames()->all())->toBe(['super-admin'])
        ->and($nextAdmin->fresh()->getRoleNames()->all())->toBe(['admin'])
        ->and(Role::where('name', 'super-admin')->exists())->toBeTrue();
});
