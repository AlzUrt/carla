<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => $guard,
        ]);

        Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => $guard,
        ]);

        $permissions = [
            'create_conversations',
            'update_conversations',
            'view_conversations',
            'delete_conversations',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guard,
            ]);
        }

        $adminRole->givePermissionTo($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'create_conversations',
            'update_conversations',
            'view_conversations',
            'delete_conversations',
        ];

        $adminRole = Role::query()
            ->where('name', 'admin')
            ->where('guard_name', $guard)
            ->first();

        if ($adminRole) {
            $adminRole->revokePermissionTo($permissions);
            $adminRole->delete();
        }

        Role::query()
            ->where('name', 'user')
            ->where('guard_name', $guard)
            ->delete();

        Permission::query()
            ->whereIn('name', $permissions)
            ->where('guard_name', $guard)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
