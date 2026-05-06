<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToSaas;

class Address extends Model
{
    use HasFactory, BelongsToSaas;

    protected $fillable = [
        'saas_id', 'addressable_type', 'addressable_id', 'type',
        'post_code', 'street', 'street_number', 'phone', 'fax',
    ];

    public function addressable()
    {
        return $this->morphTo(); // ポリモーフィックリレーションシップの定義
    }
}