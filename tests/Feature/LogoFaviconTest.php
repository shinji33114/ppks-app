<?php

use App\Models\User;

it('memakai lambang Kabupaten Tuban sebagai logo dan favicon', function () {
    foreach (['favicon.ico', 'images/logo-tuban.png', 'images/favicon-tuban.png', 'images/favicon-32.png',
              'images/favicon-192.png', 'images/apple-touch-icon.png'] as $berkas) {
        expect(file_exists(public_path($berkas)) && filesize(public_path($berkas)) > 0)
            ->toBeTrue("{$berkas} tidak ada atau kosong");
    }

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('favicon.ico', false)
        ->assertSee('images/logo-tuban.png', false);

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('favicon.ico', false)
        ->assertSee('apple-touch-icon.png', false)
        ->assertSee('images/favicon-tuban.png', false);
});
