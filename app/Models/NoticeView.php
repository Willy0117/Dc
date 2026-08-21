<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoticeView extends Model
{
    protected $table = 'notice_views';

    protected $fillable = [
        'notice_id',
        'user_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function notice(): BelongsTo
    {
        return $this->belongsTo(Notice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
