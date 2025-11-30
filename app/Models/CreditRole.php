<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditRole extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function credits()
    {
        return $this->hasMany(Credit::class, 'credit_role_id');
    }
}
