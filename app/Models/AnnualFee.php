<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnualFee extends Model
{
    protected $fillable = [
        'member_id',
        'fiscal_year',
        'annual_fee',
        'renewal_fee',
        'payment_amount',
        'payment_date',
        'status',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}