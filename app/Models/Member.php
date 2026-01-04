<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

class Member extends Model
{
    use HasRoles;
    use HasFactory;
    
    protected $table = 'members';

    protected $fillable = [
        'company_name',
        'company_furigana',
        'representative',
        'representative_furigana',
        'address_zip',
        'address',
        'email',
        'tel',
        'fax',
        'mobile',
        'staff',
        'agree',
        'affiliate',
        'agreed_at',
        'history_certificate_path',
        'history_certificate_thumbnail_path',
        'status',
    ];

    protected $casts = [
        'agree'     => 'boolean',
        'affiliate' => 'boolean',
        'agreed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function bankAccount()
    {
        return $this->hasOne(BankAccount::class);
    }
}
