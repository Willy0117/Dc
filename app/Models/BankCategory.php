<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankCategory extends Model
{
    protected $table = 'bank_categories';
    protected $fillable = ['bank_name'];

    public $timestamps = false;

    // 銀行とのリレーション
    public function banks()
    {
        return $this->hasMany(Bank::class, 'bank_category', 'id');
    }
}