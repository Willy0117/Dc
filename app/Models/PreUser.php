<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreUser extends Model
{
    protected $fillable = [
        'email',
        'token',
        'agent',
        'expires_at',
        'verified_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isVerified(): bool
    {
        return ! is_null($this->verified_at);
    }

    public function verify(): void
    {
        $this->update([
            'verified_at' => now(),
        ]);
    }

    public function isAgent(): bool
    {
        return (bool) $this->agent;
    }
    
}
