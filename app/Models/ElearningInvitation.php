<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ElearningInvitation extends Model
{
    protected $table = 'elearning_invitations';

    protected $fillable = [
        'member_id',
        'token',
        'sent_at',
        'completed_at',
    ];

    protected $casts = [
        'sent_at'      => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * member用のトークンを新規発行して保存する
     */
    public static function issueFor(Member $member): self
    {
        return self::create([
            'member_id' => $member->id,
            'token'     => Str::random(40),
            'sent_at'   => now(),
        ]);
    }
}