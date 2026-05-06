<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    protected $table = 'statuses';

    protected $primaryKey = 'id';
    public $incrementing = false;   // id を 1,6,7 など固定値で使う
    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'name',
        'description',
    ];

    /**
     * このステータスを持つ会員
     */
    public function members()
    {
        return $this->hasMany(Member::class, 'status');
    }
}
