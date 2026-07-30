<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class LicenseSitePermissionSeeder extends Seeder
{
    /**
     * 既存のRole/Permission体系に、このシステム用の権限を追加します。
     * 既にPermissionのguard名を分けている場合は $guard を合わせて変更してください。
     */
    public function run(): void
    {
        $guard = 'web';

        $permissions = [
            'video-sets.view',
            'video-sets.create',
            'video-sets.update',
            'video-sets.delete',
            'orders.view',
            'certificates.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        // 例：既存の「管理者」ロールがあれば、まとめて権限を付与
        // $admin = \Spatie\Permission\Models\Role::where('name', '管理者')->first();
        // $admin?->givePermissionTo($permissions);
    }
}
