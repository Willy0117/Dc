<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organization;
use Inertia\Inertia;

class OrganizationController extends Controller
{
    public function edit()
    {
        $organization = auth()->user()->member->organization; // 現在の組織
        $allOrganizations = Organization::all([
            'id','name','billing_name','billing_postal','billing_address',
            'contact_person','contact_email','contact_phone','registration_number'
        ]);

        return Inertia::render('Profile/OrganizationEdit', [
            'organization' => $organization,
            'allOrganizations' => $allOrganizations
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        $member = $user->member;
        $org = $member->organization;

        // 更新するフィールド
        $data = $request->only([
            'name','billing_name','billing_postal','billing_address',
            'contact_person','contact_email','contact_phone','registration_number'
        ]);

        // 1) 組織が無い場合 → 新規作成
        if (!$org) {
            $newOrg = Organization::create($data);
            $member->organization_id = $newOrg->id;
            $member->save();

            // 英語で返す（Vue で __() を使えば ja.json による翻訳が可能）
            return back()->with('success', __('organization_created'));
        }

        // 2) 組織が共有されているか？
        $isShared = $org->members()->count() > 1;

        // 共有されているのに confirm_update が無い → 警告メッセージ
        if ($isShared && $request->input('confirm_update') !== 'yes') {
            return back()->with('warning', __('organization_shared_confirmation'));
        }

        // 3) 更新
        $org->update($data);

        return back()->with('success', __('organization_updated'));
    }

    public function autocomplete(Request $request)
    {
        $q = $request->query('q', '');
        $results = Organization::where('name', 'like', "%{$q}%")
            ->orWhere('contact_phone', 'like', "%{$q}%")
            ->limit(20)
            ->get([
                'id',
                'name',
                'contact_phone',
                'billing_address',
                'billing_name',
                'billing_postal',
                'contact_person',
                'contact_email',
                'registration_number'
            ])
            ->map(function($org) {
                return [
                    'id' => $org->id,
                    'label' => $org->name,  // Autocomplete 表示用
                    'name' => $org->name,
                    'contact_phone' => $org->contact_phone,
                    'billing_address' => $org->billing_address,
                    'billing_name' => $org->billing_name,
                    'billing_postal' => $org->billing_postal,
                    'contact_person' => $org->contact_person,
                    'contact_email' => $org->contact_email,
                    'registration_number' => $org->registration_number
                ];
            });
        return response()->json($results);
    } 
}
