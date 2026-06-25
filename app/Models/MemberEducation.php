<?php
// app/Models/MemberEducation.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberEducation extends Model
{
    protected $table = 'member_educations';
    
    protected $fillable = [
        'member_id',
        'school_name',
        'faculty',
        'graduated_at',
    ];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
