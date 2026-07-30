<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoSet extends Model
{
    protected $fillable = [
        'tenant_id', 'name', 'description', 'price_jpy', 'stripe_price_id', 'passing_score', 'active',
    ];

    /**
     * tenant_idは必須ではない運用のため、明示的に呼び出したときだけ絞り込むローカルスコープにしています。
     * 例: VideoSet::forTenant($tenantId)->get()
     */
    public function scopeForTenant($query, $tenantId)
    {
        if (is_null($tenantId)) {
            return $query;
        }

        return $query->where('tenant_id', $tenantId);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class)->orderBy('sort_order');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
