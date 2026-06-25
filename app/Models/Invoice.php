<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'invoice_no',
        'amount',
        'corporate_fee',
        'personal_fee',
        'member_count',
        'subtotal',
        'tax',
        'billing_date',
        'due_date',
        'note',
        'pdf_path',
        'status',
        'email_sent',
        'email_sent_at',
        'stripe_payment_link',
        'stripe_session_id',
        'stripe_payment_intent',
        'paid_at',
    ];

    protected $casts = [
        'amount'        => 'integer',
        'corporate_fee' => 'integer',
        'personal_fee'  => 'integer',
        'member_count'  => 'integer',
        'subtotal'      => 'integer',
        'tax'           => 'integer',
        'billing_date'  => 'date',
        'due_date'      => 'date',
        'email_sent'    => 'boolean',
        'email_sent_at' => 'datetime',
        'paid_at'       => 'datetime',
    ];

    // ──────────────────────────────────────────
    // ステータス定数
    // ──────────────────────────────────────────
    const STATUS_UNSENT  = 0;
    const STATUS_SENT    = 1;
    const STATUS_PAID    = 2;
    const STATUS_CANCEL  = 3;

    public static array $statusLabels = [
        self::STATUS_UNSENT => '未送信',
        self::STATUS_SENT   => '送信済み',
        self::STATUS_PAID   => '支払済み',
        self::STATUS_CANCEL => 'キャンセル',
    ];

    // ──────────────────────────────────────────
    // リレーション
    // ──────────────────────────────────────────
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function contract(): HasOne
    {
        return $this->hasOne(OrganizationContract::class);
    }

    // ──────────────────────────────────────────
    // 契約日から年次請求日を計算
    // ──────────────────────────────────────────
    public static function calcBillingDate(string $contractDate): Carbon
    {
        return Carbon::parse($contractDate)->addYear();
    }

    // ──────────────────────────────────────────
    // アクセサ
    // ──────────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? '-';
    }
}