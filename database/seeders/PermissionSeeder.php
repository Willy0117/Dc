<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        
        $resources = [
            'organization',
            'member',
            'invoice',
            'stripe',
            'tenant',
            'license-fee',
            'role',
            'permission',
            'admin',
            'user',
        ];

        $actions = [
            'view',
            'create',
            'update',
            'delete',
        ];

        foreach ([null, 1] as $tenantId) {
            foreach ($resources as $resource) {
                foreach ($actions as $action) {
                    Permission::firstOrCreate(
                        [
                            'name'       => "{$resource}.{$action}",
                            'guard_name' => 'admin',
                            'tenant_id'  => $tenantId,
                        ]
                    );
                }
            }
        }
    }
}