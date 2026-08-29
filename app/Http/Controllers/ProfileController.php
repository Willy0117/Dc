<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberAddress;
use App\Models\Organization;
use App\Models\OrganizationAddress;
use App\Models\ProfileChangeLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class ProfileController extends Controller
{
    // ──────────────────────────────────────────
    // プロフィール編集画面
    // ──────────────────────────────────────────
    public function edit(Request $request)
    {
        $user = $request->user();

        // 病院代表アカウント
        if ((int) $user->type === 1) {
            $organization = $user->organization;
            $organization->load('addresses');

            $locationAddress = $organization->addresses->firstWhere('type', OrganizationAddress::TYPE_LOCATION);
            $shippingAddress = $organization->addresses->firstWhere('type', OrganizationAddress::TYPE_SHIPPING);
            $billingAddress  = $organization->addresses->firstWhere('type', OrganizationAddress::TYPE_BILLING);

            return Inertia::render('Profile/EditOrganization', [
                'organization'     => $organization,
                'location_address' => $locationAddress,
                'shipping_address' => $shippingAddress,
                'billing_address'  => $billingAddress,
                'user'             => ['id' => $user->id, 'email' => $user->email],
            ]);
        }

        // 先生個人アカウント
        $member = $user->member;
        $member->load(['addresses', 'educations', 'degrees', 'roles', 'committees']);

        $homeAddress     = $member->addresses->firstWhere('type', MemberAddress::TYPE_HOME);
        $shippingAddress = $member->addresses->firstWhere('type', MemberAddress::TYPE_SHIPPING);

        return Inertia::render('Profile/EditMember', [
            'member'           => $member,
            'organization'     => $member->organization ? [
                'id'   => $member->organization->id,
                'name' => $member->organization->name,
                'abbr' => $member->organization->abbr,
            ] : null,
            'home_address'     => $homeAddress,
            'shipping_address' => $shippingAddress,
            'education'        => $member->educations->first(),
            'degrees'          => $member->degrees,
            'roles'            => $member->roles,
            'committees'       => $member->committees,
            'user'             => ['id' => $user->id, 'email' => $user->email],
        ]);
    }

    // ──────────────────────────────────────────
    // 更新（病院代表アカウント）
    // ──────────────────────────────────────────
    public function updateOrganization(Request $request)
    {
        $user = $request->user();
        abort_unless((int) $user->type === 1, 403);

        $organization = $user->organization;

        $validated = $request->validate([
            'organization.url'            => 'nullable|url|max:255',
            'organization.rep_position'   => 'nullable|string|max:50',
            'organization.rep_last_name'  => 'required|string|max:100',
            'organization.rep_first_name' => 'required|string|max:100',
            'organization.payment_method' => 'nullable|integer|in:1,2',

            'location_address.postal_code' => 'nullable|string|max:20',
            'location_address.address1'    => 'nullable|string|max:255',
            'location_address.address2'    => 'nullable|string|max:255',
            'location_address.address3'    => 'nullable|string|max:255',
            'location_address.tel'         => 'nullable|string|max:30',
            'location_address.fax'         => 'nullable|string|max:30',
            'location_address.email'       => 'required|email|max:255',

            'shipping_address.name'        => 'nullable|string|max:255',
            'shipping_address.postal_code' => 'nullable|string|max:20',
            'shipping_address.address1'    => 'nullable|string|max:255',
            'shipping_address.address2'    => 'nullable|string|max:255',
            'shipping_address.address3'    => 'nullable|string|max:255',
            'shipping_address.tel'         => 'nullable|string|max:30',
            'shipping_address.fax'         => 'nullable|string|max:30',
            'shipping_address.email'       => 'nullable|email|max:255',

            'billing_address.name'         => 'nullable|string|max:255',
            'billing_address.postal_code'  => 'nullable|string|max:20',
            'billing_address.address1'     => 'nullable|string|max:255',
            'billing_address.address2'     => 'nullable|string|max:255',
            'billing_address.address3'     => 'nullable|string|max:255',
            'billing_address.tel'          => 'nullable|string|max:30',
            'billing_address.fax'          => 'nullable|string|max:30',
            'billing_address.email'        => 'nullable|email|max:255',
        ]);

        // 変更前の状態を保存（履歴用）
        $before = array_merge(
            $organization->only(['url', 'rep_position', 'rep_last_name', 'rep_first_name', 'payment_method']),
            ['location_address' => $organization->addresses->firstWhere('type', OrganizationAddress::TYPE_LOCATION)?->only(['postal_code', 'address1', 'address2', 'address3', 'tel', 'fax', 'email']) ?? []]
        );

        DB::transaction(function () use ($organization, $validated, $user, $before) {
            // name・abbrは含めず更新（変更不可）
            $organization->update($validated['organization']);

            $types = [
                'location_address' => OrganizationAddress::TYPE_LOCATION,
                'shipping_address' => OrganizationAddress::TYPE_SHIPPING,
                'billing_address'  => OrganizationAddress::TYPE_BILLING,
            ];

            foreach ($types as $key => $type) {
                if (!empty($validated[$key])) {
                    OrganizationAddress::updateOrCreate(
                        ['organization_id' => $organization->id, 'type' => $type],
                        $validated[$key]
                    );
                }
            }

            // 変更履歴を記録
            $after = array_merge(
                $validated['organization'],
                ['location_address' => $validated['location_address'] ?? []]
            );
            $changes = ProfileChangeLog::diff($before, $after);

            if (!empty($changes)) {
                ProfileChangeLog::create([
                    'user_id'     => $user->id,
                    'target_type' => ProfileChangeLog::TARGET_ORGANIZATION,
                    'target_id'   => $organization->id,
                    'changes'     => $changes,
                ]);
            }
        });

        return redirect()->back()->with('success', 'プロフィールを更新しました。');
    }

    // ──────────────────────────────────────────
    // 更新（先生個人アカウント）
    // ──────────────────────────────────────────
    public function updateMember(Request $request)
    {
        $user = $request->user();
        abort_unless((int) $user->type === 2 && $user->member_id, 403);

        $member = $user->member;

        $validated = $request->validate([
//            'member.doctor_number'   => 'nullable|digits:6',
            'member.position'        => 'nullable|string|max:20',
            'member.last_name'       => 'required|string|max:100',
            'member.first_name'      => 'required|string|max:100',
            'member.last_name_kana'  => 'nullable|string|max:100',
            'member.first_name_kana' => 'nullable|string|max:100',
            'member.gender'          => 'nullable|in:male,female,other',
            'member.birthdate'       => 'nullable|date',
            'member.tel'             => 'nullable|string|max:30',
            'member.mobile'          => 'nullable|string|max:30',
            'member.fax'             => 'nullable|string|max:30',
            'member.email'           => 'nullable|email|max:255|unique:members,email,' . $member->id,
            'member.personal_email'  => 'nullable|email|max:255',
            'member.member_type'     => 'nullable|string|max:50',

            'home_address.postal_code' => 'nullable|string|max:20',
            'home_address.address1'    => 'nullable|string|max:255',
            'home_address.address2'    => 'nullable|string|max:255',
            'home_address.address3'    => 'nullable|string|max:255',
            'home_address.tel'         => 'nullable|string|max:30',
            'home_address.fax'         => 'nullable|string|max:30',

            'shipping_address.postal_code' => 'nullable|string|max:20',
            'shipping_address.address1'    => 'nullable|string|max:255',
            'shipping_address.address2'    => 'nullable|string|max:255',
            'shipping_address.address3'    => 'nullable|string|max:255',
            'shipping_address.tel'         => 'nullable|string|max:30',
            'shipping_address.fax'         => 'nullable|string|max:30',

            'education.school_name'  => 'nullable|string|max:255',
            'education.faculty'      => 'nullable|string|max:255',
            'education.graduated_at' => 'nullable|string|max:20',

            'degrees'               => 'nullable|array|max:5',
            'degrees.*.degree'      => 'nullable|string|max:100',
            'degrees.*.obtained_at' => 'nullable|string|max:20',

            'roles'              => 'nullable|array',
            'roles.*.role'       => 'nullable|string|max:100',
            'roles.*.started_at' => 'nullable|string|max:20',
            'roles.*.ended_at'   => 'nullable|string|max:20',

            'committees'               => 'nullable|array',
            'committees.*.committee'   => 'nullable|string|max:100',
            'committees.*.started_at'  => 'nullable|string|max:20',
            'committees.*.ended_at'    => 'nullable|string|max:20',
        ]);

        // doctor_numberは、既に値が入っている場合は変更不可（改ざん防止）
//        if (!empty($member->doctor_number)) {
//            unset($validated['member']['doctor_number']);
//        }

        // 変更前の状態を保存（履歴用）
        $before = $member->only([
            'position', 'last_name', 'first_name',
            'last_name_kana', 'first_name_kana', 'gender', 'birthdate',
            'tel', 'mobile', 'fax', 'email', 'personal_email', 'member_type',
        ]);

        DB::transaction(function () use ($member, $validated, $user, $before) {
            // member_number・status_id・joined_at・withdrawn_atは含めず更新（変更不可）
            $member->update($validated['member']);

            if (!empty($validated['home_address'])) {
                MemberAddress::updateOrCreate(
                    ['member_id' => $member->id, 'type' => MemberAddress::TYPE_HOME],
                    $validated['home_address']
                );
            }

            if (!empty($validated['shipping_address'])) {
                MemberAddress::updateOrCreate(
                    ['member_id' => $member->id, 'type' => MemberAddress::TYPE_SHIPPING],
                    $validated['shipping_address']
                );
            } else {
                MemberAddress::where('member_id', $member->id)
                    ->where('type', MemberAddress::TYPE_SHIPPING)
                    ->delete();
            }

            if (!empty($validated['education'])) {
                $member->educations()->updateOrCreate([], $validated['education']);
            }

            if (isset($validated['degrees'])) {
                $member->degrees()->delete();
                foreach ($validated['degrees'] as $degree) {
                    if (!empty($degree['degree'])) {
                        $member->degrees()->create($degree);
                    }
                }
            }

            if (isset($validated['roles'])) {
                $member->roles()->delete();
                foreach ($validated['roles'] as $role) {
                    if (!empty($role['role'])) {
                        $member->roles()->create($role);
                    }
                }
            }

            if (isset($validated['committees'])) {
                $member->committees()->delete();
                foreach ($validated['committees'] as $committee) {
                    if (!empty($committee['committee'])) {
                        $member->committees()->create($committee);
                    }
                }
            }

            // 変更履歴を記録
            $changes = ProfileChangeLog::diff($before, $validated['member']);

            if (!empty($changes)) {
                ProfileChangeLog::create([
                    'user_id'     => $user->id,
                    'target_type' => ProfileChangeLog::TARGET_MEMBER,
                    'target_id'   => $member->id,
                    'changes'     => $changes,
                ]);
            }
        });

        return redirect()->back()->with('success', 'プロフィールを更新しました。');
    }

    // ──────────────────────────────────────────
    // ログインメールアドレス変更（病院代表・先生個人 共通）
    // ──────────────────────────────────────────
    public function updateEmail(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);

        $oldEmail = $user->email;

        $user->update([
            'email' => $validated['email'],
        ]);

        if ($oldEmail !== $validated['email']) {
            ProfileChangeLog::create([
                'user_id'     => $user->id,
                'target_type' => ProfileChangeLog::TARGET_EMAIL,
                'target_id'   => $user->id,
                'changes'     => [
                    'email' => ['before' => $oldEmail, 'after' => $validated['email']],
                ],
            ]);
        }

        return redirect()->back()->with('success', 'メールアドレスを変更しました。');
    }

    // ──────────────────────────────────────────
    // パスワード変更（病院代表・先生個人 共通）
    // ──────────────────────────────────────────
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => '現在のパスワードが正しくありません。']);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // パスワード自体は記録せず、「変更があった」という事実のみ記録
        ProfileChangeLog::create([
            'user_id'     => $user->id,
            'target_type' => ProfileChangeLog::TARGET_PASSWORD,
            'target_id'   => $user->id,
            'changes'     => null,
        ]);

        return redirect()->back()->with('success', 'パスワードを変更しました。');
    }
}
