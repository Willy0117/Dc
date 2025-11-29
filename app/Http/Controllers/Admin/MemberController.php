<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use App\Models\Member;

class MemberController extends Controller
{
    // 一覧ページ
    public function index(Request $request)
    {
        $query = Member::query();

        // 検索
        if ($login_id = $request->input('login_id')) {
            $query->where('login_id', 'like', "%{$login_id}%");
        }
        if ($name = $request->input('name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        // ソート
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = intval($request->input('per_page', 10));

        $members = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Members/Index', [
            'members' => $members,
            'filters' => $request->only(['login_id','name','per_page','sort_by','sort_dir']),
        ]);
    }

    // 作成画面
    public function create()
    {
        return Inertia::render('Admin/Members/Create', [
            'member' => null
        ]);
    }

    // 保存
    public function store(Request $request)
    {
        $validated = $request->validate([
            'login_id' => ['required','string','unique:members'],
            'name'     => ['required','string'],
            'phone'    => ['nullable','string'],
            'address'  => ['nullable','string'],
            'status'   => ['required', Rule::in(['provisional','regular','suspended','expelled'])],
            'member_number' => ['nullable','string','unique:members'],
        ]);

        Member::create($validated);

        return redirect()->route('admin.members.index')
            ->with('success', __('member_created'));
    }

    // 編集画面
    public function edit(Member $member)
    {
        return Inertia::render('Admin/Members/Edit', [
            'member' => $member
        ]);
    }

    // 更新
    public function update(Request $request, Member $member)
    {
        $validated = $request->validate([
            'login_id' => ['required','string',Rule::unique('members')->ignore($member->id)],
            'name'     => ['required','string'],
            'phone'    => ['nullable','string'],
            'postal_code' => 'nullable|string|max:20',
            'address'  => ['nullable','string'],
            'status'   => ['required', Rule::in(['provisional','regular','suspended','expelled'])],
            'member_number' => ['nullable','string',Rule::unique('members')->ignore($member->id)],
        ]);

        $member->update($validated);

        return redirect()->route('admin.members.index')
            ->with('success', __('profile.member_updated'));
    }

    // 削除
    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->route('admin.members.index')
            ->with('success', __('member_deleted'));
    }

    // 複数削除
    public function bulkDelete(Request $request)
    {
        Member::whereIn('id', $request->ids)->delete();
        return redirect()->route('admin.members.index')
            ->with('success', __('selected_members_deleted'));
    }
}
