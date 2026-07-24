<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormOption extends Model
{
    protected $fillable = [
        'treatment_area',
        'field_name',
        'label',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    // ──────────────────────────────────────────
    // フィールド名定数
    // ──────────────────────────────────────────
    const FIELD_VESSEL        = '穿刺血管';
    const FIELD_DISEASE       = '病名';
    const FIELD_PAIN_RIGHT    = '右手疼痛部位';
    const FIELD_PAIN_LEFT     = '左手疼痛部位';
    const FIELD_TOURNIQUET    = '駆血部位';
    const FIELD_COMPLICATION  = 'トラブル・合併症';

    // ──────────────────────────────────────────
    // スコープ
    // ──────────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByArea($query, string $area)
    {
        return $query->where('treatment_area', $area);
    }

    public function scopeByField($query, string $field)
    {
        return $query->where('field_name', $field);
    }

    // ──────────────────────────────────────────
    // フォーム用選択肢を取得
    // treatment_area => field_name => [label, ...] の形で返す
    // ──────────────────────────────────────────
    public static function getForForm(): array
    {
        $fields = FormField::with(['activeOptions'])
            ->active()
            ->orderBy('sort_order')
            ->get();

        $result = [];
        foreach ($fields as $field) {
            $result[$field->treatment_area][$field->field_name] = [
                'type'    => $field->field_type,
                'options' => $field->activeOptions->pluck('label')->toArray(),
            ];
        }
        return $result;
    }
}
