<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    protected $table = 'progresses';

    protected $fillable = [
        'id',
        'name',
        'sort_order',
    ];

    public function members()
    {
        return $this->hasMany(Member::class, 'progress');
    }
}
