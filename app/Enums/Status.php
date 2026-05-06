<?php

namespace App\Enums;

enum Status: string
{
    case RECEIVED = 'received';
    case WORKING = 'working';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match($this) {
            self::RECEIVED => '受付',
            self::WORKING => '作業中',
            self::COMPLETED => '完了',
        };
    }

    public static function options(): array
    {
        return array_map(fn($s) => [
            'value' => $s->value,
            'label' => $s->label(),
        ], self::cases());
    }
}
