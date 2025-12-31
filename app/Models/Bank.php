<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'banks';
    protected $fillable = [
        'bank_code',
        'branch_code',
        'name_kana',
        'name',
        'type',
        'bank_category'
    ];

    public $timestamps = false;

    // 支店（type=2）のリレーション
    public function branches()
    {
        return $this->hasMany(Bank::class, 'bank_code', 'bank_code')
                    ->where('type', 2);
    }

    // カテゴリ
    public function category()
    {
        return $this->belongsTo(BankCategory::class, 'bank_category', 'id');
    }
}
