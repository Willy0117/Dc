<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberTierHistory extends Model
{
    protected $table = 'member_tier_histories';

    protected $fillable = [
        'member_id',
        'period_start',
        'period_end',
        'case_count',
        'tier',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'case_count'   => 'integer',
        'tier'         => 'integer',
    ];

    // ──────────────────────────────────────────
    // リレーション
    // ──────────────────────────────────────────

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
