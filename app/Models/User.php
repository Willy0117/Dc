<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable, HasRoles;
    
    protected $guard_name = 'web';

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'tenant_id',
        'organization_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $appends = [
        'profile_photo_url',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
    // Tenant リレーション
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    // Organization リレーション
/*
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
*/
    /**
     * ログインユーザーの tenant_id でフィルターしたロールを取得
     */
    public function tenantRoles()
    {
        if ($this->isSuperAdmin()) {
            return $this->roles();
        }
        return $this->roles()->where('roles.tenant_id', $this->tenant_id); 
    }

    /**
     * ログインユーザーの tenant_id でフィルターした権限を取得
     */
    public function tenantPermissions()
    {
        if ($this->isSuperAdmin()) {
            return $this->getAllPermissions();
        }

        return $this->getAllPermissions()
            ->where('tenant_id', $this->tenant_id);
    }

    public function isSuperAdmin()
    {
        return $this->hasRole('super_admin');
    }
}

