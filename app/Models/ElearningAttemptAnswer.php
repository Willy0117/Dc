<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElearningAttemptAnswer extends Model
{
    protected $table = 'elearning_attempt_answers';

    protected $fillable = [
        'attempt_id',
        'question_id',
        'sort_order',
        'selected_answer',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ElearningAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ElearningQuestion::class, 'question_id');
    }
}
