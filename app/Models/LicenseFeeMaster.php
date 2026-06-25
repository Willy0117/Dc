<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseFeeMaster extends Model
{
    protected $table = 'license_fee_masters';

    protected $fillable = [
        'corporate_fee',
        'personal_fee',
        'started_at',
    ];

    protected $casts = [
        'corporate_fee' => 'integer',
        'personal_fee'  => 'integer',
        'started_at'    => 'date',
    ];

    // 現在有効な料金（最新）
    public static function current(): ?self
    {
        return static::orderByDesc('started_at')->first();
    }
}
