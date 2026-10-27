<?php

use App\Models\Client;
use App\Models\User;
use Spatie\Permission\Models\Role;

it('renders working next and previous links on the client list', function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    foreach (range(1, 11) as $number) {
        Client::create([
            'tanggal_masuk' => '2026-09-20',
            'sumber_aduan' => 'Laporan warga',
            'tkp_kronologi' => 'Ditemukan di area publik.',
            'nama_lengkap' => "Klien Uji {$number}",
            'status' => Client::STATUS_DRAFT,
        ]);
    }

    $this->actingAs($admin)
        ->get(route('clients.index'))
        ->assertOk()
        ->assertSee('pagination', false)
        ->assertSee('rel="next"', false)
        ->assertSee('page=2', false)
        ->assertSee('Klien Uji 1')
        ->assertDontSee('Klien Uji 11');

    $this->actingAs($admin)
        ->get(route('clients.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('rel="prev"', false)
        ->assertSee('page=1', false)
        ->assertSee('Klien Uji 11')
        ->assertDontSee('Klien Uji 1</div>');
});

it('shows client totals for each progress stage and filters the list when a stage is selected', function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    foreach ([
        ['Klien Draft', Client::STATUS_DRAFT],
        ['Klien Assessment 1', Client::STATUS_ASSESSMENT],
        ['Klien Assessment 2', Client::STATUS_ASSESSMENT],
        ['Klien Assessment 3', Client::STATUS_ASSESSMENT],
        ['Klien Penanganan', Client::STATUS_PENANGANAN],
        ['Klien Selesai', Client::STATUS_SELESAI],
        ['Klien Dirujuk', Client::STATUS_DIRUJUK],
    ] as [$name, $status]) {
        Client::create([
            'tanggal_masuk' => '2026-09-20',
            'sumber_aduan' => 'Laporan warga',
            'tkp_kronologi' => 'Ditemukan di area publik.',
            'nama_lengkap' => $name,
            'status' => $status,
        ]);
    }

    $this->actingAs($admin)
        ->get(route('clients.index', ['stage' => 'assessment']))
        ->assertOk()
        ->assertSee('3 orang')
        ->assertSee('2 orang')
        ->assertSee('1 orang')
        ->assertSee(route('clients.index', ['stage' => 'final']))
        ->assertSee('Klien Assessment 1')
        ->assertSee('Klien Assessment 2')
        ->assertSee('Klien Assessment 3')
        ->assertDontSee('Klien Draft')
        ->assertDontSee('Klien Penanganan')
        ->assertDontSee('Klien Selesai')
        ->assertDontSee('Klien Dirujuk');
});

it('shows client search only on the client list above the table', function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    Client::create([
        'tanggal_masuk' => '2026-09-20',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Nama Klien Dicari',
        'status' => Client::STATUS_DRAFT,
    ]);
    Client::create([
        'tanggal_masuk' => '2026-09-20',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Klien Lain',
        'status' => Client::STATUS_DRAFT,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Cari nama penerima layanan');

    $this->actingAs($admin)
        ->get(route('clients.index'))
        ->assertOk()
        ->assertSee('Cari nama penerima layanan')
        ->assertSee('filter-search', false)
        ->assertSee('filter-tanggal-masuk', false)
        ->assertSeeInOrder(['id="filter-search"', '<table class="table-custom">'], false);

    $this->actingAs($admin)
        ->get(route('clients.index', ['search' => 'Nama Klien Dicari']))
        ->assertOk()
        ->assertSee('value="Nama Klien Dicari"', false)
        ->assertSee('Nama Klien Dicari')
        ->assertDontSee('Klien Lain');
});

it('offers a direct assessment action below the photo on eligible client details only', function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $draftClient = Client::create([
        'tanggal_masuk' => '2026-09-20',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Klien Siap Assessment',
        'status' => Client::STATUS_DRAFT,
        'foto_klien' => 'client-photos/klien-siap-assessment.jpg',
    ]);
    $archivedClient = Client::create([
        'tanggal_masuk' => '2026-09-20',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Klien Draft Arsip',
        'status' => Client::STATUS_DRAFT,
        'is_archived' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('clients.index'))
        ->assertOk()
        ->assertDontSee('Mulai Penilaian')
        ->assertDontSee(route('assessment.create', $draftClient), false);

    $this->actingAs($admin)
        ->get(route('clients.show', $draftClient))
        ->assertOk()
        ->assertSeeInOrder(['alt="Foto Penerima Layanan"', 'Mulai Penilaian', 'Klien Siap Assessment'])
        ->assertSee(route('assessment.create', $draftClient), false);

    $this->actingAs($admin)
        ->get(route('clients.show', $archivedClient))
        ->assertOk()
        ->assertDontSee('Mulai Penilaian')
        ->assertDontSee(route('assessment.create', $archivedClient), false);
});

it('filters clients by the selected entry date', function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    foreach ([
        ['Klien Tanggal Dipilih', '2026-09-20'],
        ['Klien Tanggal Lain', '2026-09-21'],
    ] as [$name, $date]) {
        Client::create([
            'tanggal_masuk' => $date,
            'sumber_aduan' => 'Laporan warga',
            'tkp_kronologi' => 'Ditemukan di area publik.',
            'nama_lengkap' => $name,
            'status' => Client::STATUS_DRAFT,
        ]);
    }

    $this->actingAs($admin)
        ->get(route('clients.index', ['tanggal_masuk' => '2026-09-20']))
        ->assertOk()
        ->assertSee('value="2026-09-20"', false)
        ->assertSee('Klien Tanggal Dipilih')
        ->assertDontSee('Klien Tanggal Lain');
});
