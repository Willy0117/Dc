<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;
use App\Models\Member;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
        // ★ ここで members を自動作成 ★
        Member::create([
            'user_id' => $user->id,
            'member_code' => 'M' . str_pad($user->id, 6, '0', STR_PAD_LEFT), // 例: M000001
        ]);
        // Organization を空で作成（後で編集可能）
        $org = Organization::create([
            'name' => '',           // 空
            'billing_name' => '',   // 空
        ]);
        // Member に organization_id をセット（1対1）
        $member->organization_id = $org->id;
        $member->save();

        // デフォルトロール付与
        $user->assignRole('member');

        return $user;
    }
}
