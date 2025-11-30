<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditConference extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function credits()
    {
        return $this->hasMany(Credit::class, 'credit_conference_id');
    }
    public function category()
    {
        return $this->belongsTo(CreditCategory::class, 'credit_category_id');
    }
}
