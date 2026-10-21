<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $permissions = [
            'trainers.view' => ['View trainers', 'trainers'],
            'trainers.manage' => ['Manage trainers and payroll', 'trainers'],
        ];

        foreach ($permissions as $key => [$name, $group]) {
            DB::table('permissions')->updateOrInsert(
                ['key' => $key],
                ['name' => $name, 'group' => $group, 'description' => 'Manage trainer profiles and payroll history.', 'created_at' => $now, 'updated_at' => $now],
            );
        }

        $permissionIds = DB::table('permissions')->whereIn('key', array_keys($permissions))->pluck('id', 'key');
        $roleIds = DB::table('roles')->whereIn('key', ['organization-owner', 'organization-admin', 'branch-manager', 'receptionist', 'trainer'])->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('permission_role')->updateOrInsert(['permission_id' => $permissionId, 'role_id' => $roleId]);
            }
        }
    }

    public function down(): void
    {
        $permissionIds = DB::table('permissions')->whereIn('key', ['trainers.view', 'trainers.manage'])->pluck('id');
        DB::table('permission_role')->whereIn('permission_id', $permissionIds)->delete();
        DB::table('permissions')->whereIn('id', $permissionIds)->delete();
    }
};
