<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileChangeLog extends Model
{
    protected $table = 'profile_change_logs';

    const TARGET_ORGANIZATION = 'organization';
    const TARGET_MEMBER       = 'member';
    const TARGET_EMAIL        = 'email';
    const TARGET_PASSWORD     = 'password';

    const TARGET_LABELS = [
        self::TARGET_ORGANIZATION => '法人・所在地情報',
        self::TARGET_MEMBER       => '先生プロフィール',
        self::TARGET_EMAIL        => 'ログインメールアドレス',
        self::TARGET_PASSWORD     => 'パスワード',
    ];

    protected $fillable = [
        'user_id',
        'target_type',
        'target_id',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTargetLabelAttribute(): string
    {
        return self::TARGET_LABELS[$this->target_type] ?? $this->target_type;
    }

    /**
     * 更新前後の配列から、実際に値が変わったフィールドだけの差分を作る
     * 例: ['name' => ['before' => 'A', 'after' => 'B']]
     */
    public static function diff(array $before, array $after): array
    {
        $changes = [];
        foreach ($after as $key => $newValue) {
            $oldValue = $before[$key] ?? null;
            if ((string) $oldValue !== (string) $newValue) {
                $changes[$key] = ['before' => $oldValue, 'after' => $newValue];
            }
        }
        return $changes;
    }
}
