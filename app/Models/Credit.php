<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    use HasFactory;

    protected $fillable = [
        'credit_category_id',
        'credit_conference_id',
        'credit_role_id',
        'credit',
    ];

    public function category()
    {
        return $this->belongsTo(CreditCategory::class, 'credit_category_id');
    }

    public function conference()
    {
        return $this->belongsTo(CreditConference::class, 'credit_conference_id');
    }

    public function role()
    {
        return $this->belongsTo(CreditRole::class, 'credit_role_id');
    }
}

