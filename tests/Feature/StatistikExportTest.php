<?php

use App\Models\Client;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
});

function unduhExcel($test, User $user): string
{
    $response = $test->actingAs($user)->get(route('statistik.export'));
    $response->assertOk();

    $path = tempnam(sys_get_temp_dir(), 'stat') . '.xlsx';
    file_put_contents($path, $response->streamedContent());

    return $path;
}

it('lets an admin download the statistics as an xlsx file', function () {
    $head = User::factory()->create();
    $head->assignRole('admin');

    $this->actingAs($head)
        ->get(route('statistik.export'))
        ->assertOk()
        ->assertDownload('statistik-ppks-' . now()->format('Y-m-d') . '.xlsx');
});

it('lets every admin download the statistics', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get(route('statistik.export'))->assertOk();
});

it('shows a clear Excel export action on the statistics dashboard', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Ekspor laporan statistik')
        ->assertSee('Unduh data Excel')
        ->assertSee(route('statistik.export'), false)
        ->assertSee('format Excel (.xlsx)');
});

it('redirects guests to the login page', function () {
    $this->get(route('statistik.export'))->assertRedirect(route('login'));
});

it('writes the same numbers that the dashboard shows', function () {
    $head = User::factory()->create();
    $head->assignRole('admin');

    foreach (['Lansia Terlantar', 'Lansia Terlantar', 'Anak Jalanan'] as $i => $kategori) {
        Client::create([
            'tanggal_masuk' => now()->toDateString(),
            'sumber_aduan' => 'Laporan warga',
            'tkp_kronologi' => 'Ditemukan di area publik.',
            'nama_lengkap' => "Klien {$i}",
            'foto_klien' => 'client-photos/test.jpg',
            'status' => Client::STATUS_DRAFT,
            'kategori_ppks' => $kategori,
        ]);
    }

    $sheet = IOFactory::load(unduhExcel($this, $head))->getActiveSheet();
    $cells = collect($sheet->toArray())->mapWithKeys(fn ($r) => [$r[0] => $r[1]]);

    expect($cells['Total Penerima Manfaat'])->toEqual(3)
        ->and($cells['Lansia Terlantar'])->toEqual(2)
        ->and($cells['Anak Jalanan'])->toEqual(1)
        ->and($cells['Draft'])->toEqual(3);
});

it('does not let user-entered text become an Excel formula', function () {
    $head = User::factory()->create();
    $head->assignRole('admin');

    Client::create([
        'tanggal_masuk' => now()->toDateString(),
        'sumber_aduan' => '=1+1',
        'tkp_kronologi' => 'Uji.',
        'nama_lengkap' => 'Klien Uji',
        'foto_klien' => 'client-photos/test.jpg',
        'status' => Client::STATUS_DRAFT,
    ]);

    $sheet = IOFactory::load(unduhExcel($this, $head))->getActiveSheet();

    $ditemukan = false;
    foreach ($sheet->getRowIterator() as $row) {
        $cell = $sheet->getCell('A' . $row->getRowIndex());
        if ($cell->getValue() === '=1+1') {
            $ditemukan = true;
            expect($cell->getDataType())->toBe('s');
        }
    }

    expect($ditemukan)->toBeTrue();
});
