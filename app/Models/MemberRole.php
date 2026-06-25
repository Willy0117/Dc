<?php
// app/Models/MemberRole.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberRole extends Model
{
    protected $table = 'member_roles';

    protected $fillable = [
        'member_id',
        'role',
        'started_at',
        'ended_at',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
