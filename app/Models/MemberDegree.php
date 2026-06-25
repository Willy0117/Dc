<?php
// app/Models/MemberDegree.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberDegree extends Model
{
    protected $table = 'member_degrees';
    
    protected $fillable = [
        'member_id',
        'degree',
        'obtained_at',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}