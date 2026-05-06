<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'tel',
        'status_id',
        'name',
        'postal_code',
        'address1',
        'address2',
        'address3',
        'mobile',
        'fax',
        'email',
    ];
    
    protected $appends = [
        'full_name',
        'full_address',
    ];
    
    public function user()
    {
        return $this->hasOne(User::class);
    }
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function updateCycles()
    {
        return $this->hasMany(InstructorUpdateCycle::class);
    }
    
    public function latestCycle()
    {
        return $this->hasOne(InstructorUpdateCycle::class)
            ->latestOfMany('end_date');
    }

    public function pdfUploads()
    {
        return $this->hasMany(PdfUpload::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function bankAccount()
    {
        return $this->hasOne(BankAccount::class);
    }
    public function roles()
    {
        return $this->hasMany(MemberRole::class);
    }
    /* =====================
     |  表示用ラベル
     * ===================== */
    public function getFullNameAttribute()
    {
        return collect([
            $this->last_name,
            $this->first_name,
        ])->filter()->implode('');
    }

    public function getFullAddressAttribute()
    {
        return collect([
            $this->address1,
            $this->address2,
            $this->address3,
        ])->filter()->implode('');
    }

}
