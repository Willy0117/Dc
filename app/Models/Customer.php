<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // ★★★ この行があるか確認、なければ追加 ★★★
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_name',
        'zip_code',
        'address',
        'phone_number',
        'representative_name',
        'contact_person_name',
    ];
    //
}
