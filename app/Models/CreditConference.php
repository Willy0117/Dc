<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditConference extends Model
{
    use HasFactory;

    protected $fillable = ['credit_category_id', 'name', 'points'];

    public function category()
    {
        return $this->belongsTo(CreditCategory::class, 'credit_category_id');
    }

    public function roles()
    {
        return $this->hasMany(CreditRolePoint::class, 'credit_conference_id');
    }
}
