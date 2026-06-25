<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    const UPDATED_AT = null; // updated_atは使わない

    protected $fillable = [
        'source',
        'event_type',
        'payload',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'payload' => 'array',
    ];

    const SOURCE_CLOUDSIGN = 'cloudsign';
    const SOURCE_STRIPE    = 'stripe';
}
