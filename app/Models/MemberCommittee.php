<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class MemberCommittee extends Model
{
    protected $table = 'member_committees';
 
    protected $fillable = [
        'member_id', 'committee', 'started_at', 'ended_at'
    ];
 
    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}