<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElearningQuestion extends Model
{
    protected $table = 'elearning_questions';

    protected $fillable = [
        'question',
        'choice_a',
        'choice_b',
        'choice_c',
        'choice_d',
        'correct_answer',
        'explanation',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(ElearningAttemptAnswer::class, 'question_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getChoiceAttribute(string $letter): ?string
    {
        return match (strtoupper($letter)) {
            'A' => $this->choice_a,
            'B' => $this->choice_b,
            'C' => $this->choice_c,
            'D' => $this->choice_d,
            default => null,
        };
    }
}
