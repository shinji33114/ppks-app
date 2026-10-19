<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        User::query()->eachById(function (User $user) use ($admin) {
            $user->syncRoles([$admin]);
        });

        Role::query()
            ->where('id', '!=', $admin->getKey())
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        throw new RuntimeException('Consolidated user roles cannot be restored to their previous assignments.');
    }
};
