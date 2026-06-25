<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationContract extends Model
{
    protected $table = 'organization_contracts';

    protected $fillable = [
        'organization_id',
        'invoice_id',
        'corporate_fee',
        'personal_fee',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'corporate_fee' => 'integer',
        'personal_fee'  => 'integer',
        'started_at'    => 'date',
        'ended_at'      => 'date',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}