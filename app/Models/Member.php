<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'login_id',    // ログインID
        'member_code', // 会員ID
        'name',        // 氏名
        'postal_code', // 郵便番号
        'address1',    // 住所1
        'address2',    // 住所2
        'phone',       // 電話番号
        'fax',       // fax
        'organization_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    // Organization とのリレーション
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
    // Pdffile とのリレーション
    public function pdfUploads()
    {
        return $this->hasMany(PdfUpload::class);
    }
    public function updateCycles()
    {
        return $this->hasMany(InstructorUpdateCycle::class);
    }

    public function currentUpdateCycle()
    {
        $today = now()->toDateString();

        return $this->hasOne(InstructorUpdateCycle::class)
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);
    }

    public function pdfUploads()
    {
        return $this->hasMany(PdfUpload::class);
    }

    /**
     * 指定更新サイクル内の単位合計
     */
    public function totalPoints($cycle)
    {
        if (!$cycle) return 0;

        return $this->pdfUploads()
            ->whereBetween('created_at', [$cycle->start_date, $cycle->end_date])
            ->sum('points');
    }

    /**
     * 指定更新サイクル内の学術集会参加回数
     */
    public function conferenceCount($cycle)
    {
        if (!$cycle) return 0;

        return $this->pdfUploads()
            ->where('category', 'conference')
            ->whereBetween('created_at', [$cycle->start_date, $cycle->end_date])
            ->count();
    }

}
