<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaasAccount extends Model
{
    use HasFactory;

    /**
     * モデルに関連付けるテーブル
     * デフォルトではクラス名（SaasAccount）の複数形（saas_accounts）が使用されるため、
     * 明示的に定義する必要はあまりありませんが、念のため記述しておきます。
     */
    protected $table = 'saas_accounts';

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'name',
        'domain',
        'description',
        'is_active',
    ];

    /**
     * Carbonインスタンスに変換する日付属性
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    // 必要に応じて、このSaaSアカウントに属するユーザー、顧客、部門などのリレーションシップを定義します。
    public function users()
    {
        return $this->hasMany(User::class, 'saas_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'saas_id');
    }

    public function departments()
    {
        return $this->hasMany(Department::class, 'saas_id');
    }

    public function customerGroups()
    {
        return $this->hasMany(CustomerGroup::class, 'saas_id');
    }
}