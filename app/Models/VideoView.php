<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoView extends Model
{
    protected $fillable = ['order_id', 'video_id', 'completed_at'];

    protected $casts = [
        'completed_at' => 'datetime',
    ];
}
