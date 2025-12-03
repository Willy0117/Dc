<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function conferences()
    {
        return $this->hasMany(CreditConference::class, 'credit_category_id');
    }

    public function roles()
    {
        return $this->hasMany(CreditRolePoint::class, 'credit_category_id');
    }
}


