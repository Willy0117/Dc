<?php 

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MemberController extends Controller
{
    // 編集画面
    public function edit(Request $request)
    {
        $member = $request->user()->member;

        return Inertia::render('Profile/MemberEdit', [
            'member' => $member,
        ]);
    }

    // 更新処理
    public function update(Request $request)
    {
        $member = $request->user()->member;

        $request->validate([
            'member_code' => 'required|string|max:50|unique:members,member_code,' . $member->id,
            'name'        => 'required|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'address1'    => 'nullable|string|max:255',
            'address2'    => 'nullable|string|max:255',
            'phone'       => 'nullable|string|max:20',
            'fax'         => 'nullable|string|max:20',
        ]);

        $member->update($request->only([
            'member_code', 'name', 'postal_code', 'address1', 'address2', 'phone', 'fax'
        ]));

        return back()->with('success', '会員情報を更新しました。');
    }
}
