<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = [
        'name',
        'guard_name',
        'tenant_id', 
    ];

    /**
     * Tenant リレーション
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Tenant スコープ（特定テナントのRoleのみ取得）
     */
    public function scopeOfTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}
