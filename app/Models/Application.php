<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use App\Enums\Status;

class Application extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'organization_id',
        'application_date',
        'delivery_date',
        'funeral_datetime',
        'staff_name',
        'last_name',
        'first_name',
        'deceased_furigana',
        'age_at_death',
        'gender',
        'spouse_status',
        'children_count',
        'grandchildren_count',
        'chief_mourner_name',
        'relationship_to_deceased',
        'traits',
        'special_notes',
        'text_color',
        'bg_color',
        'remarks',
        'working_at',
        'completed_at',
        'status',
        'apply_type',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'application_date'   => 'datetime',
        'delivery_date'      => 'datetime',
        'funeral_datetime'   => 'datetime',
        'traits'             => 'array',
        'working_at'         => 'datetime',
        'completed_at'       => 'datetime',

    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        // これにより、Vueには "2026-03-12 16:42:00" という形式で渡ります
        return $date->format('Y-m-d H:i:s');
    }
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function documents()
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function getFullNameAttribute()
    {
        return $this->last_name . ' ' . $this->first_name;
    }

    public function getOrderCodeAttribute()
    {
        return 'P' . str_pad($this->id, 8, '0', STR_PAD_LEFT);
    }

        // JSON化時に自動で fullname を含める
    protected $appends = ['fullname', 'order_code'];

    public function getStatusAttribute($value)
    {
        return Status::from($value)->label();
    }
    /*

     */
    public function pdfDocuments()
    {
        return $this->hasMany(ApplicationDocument::class)
                    ->where('type', 'pdf');
    }
    public function canvasDocument()
    {
        return $this->hasOne(ApplicationDocument::class)
                    ->where('type', 'canvas');
    }
}