<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\Organization;
use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NoticeController extends Controller
{
    public function index()
    {
        $notices = Notice::withCount(['organizations', 'members'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return Inertia::render('Admin/Notices/Index', [
            'notices' => $notices,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Notices/Edit', [
            'notice' => null,
            'allOrganizations' => $this->allOrganizations(),
            'allMembers' => $this->allMembers(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);

        $notice = Notice::create([
            ...$validated,
            'created_by' => auth('admin')->id(),
        ]);

        $notice->organizations()->sync($validated['organization_ids'] ?? []);
        $notice->members()->sync($validated['member_ids'] ?? []);

        return redirect()->route('admin.notices.index')->with('success', 'お知らせを登録しました。');
    }

    public function edit(Notice $notice)
    {
        $notice->load('organizations:id,name', 'members:id,last_name,first_name');

        return Inertia::render('Admin/Notices/Edit', [
            'notice' => $notice,
            'allOrganizations' => $this->allOrganizations(),
            'allMembers' => $this->allMembers(),
        ]);
    }

    public function update(Request $request, Notice $notice)
    {
        $validated = $this->validated($request);

        $notice->update($validated);
        $notice->organizations()->sync($validated['organization_ids'] ?? []);
        $notice->members()->sync($validated['member_ids'] ?? []);

        return redirect()->route('admin.notices.index')->with('success', 'お知らせを更新しました。');
    }

    public function destroy(Notice $notice)
    {
        $notice->delete();

        return redirect()->route('admin.notices.index')->with('success', 'お知らせを削除しました。');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'                  => 'required|string|max:255',
            'body'                   => 'nullable|string',
            'youtube_url'            => 'nullable|url|max:255',
            'video_available_from'   => 'nullable|date',
            'video_available_until'  => 'nullable|date|after_or_equal:video_available_from',
            'target_type'            => 'required|in:all,organizations_all,members_all,organizations,members',
            'published_at'           => 'nullable|date',
            'organization_ids'       => 'array',
            'organization_ids.*'     => 'exists:organizations,id',
            'member_ids'             => 'array',
            'member_ids.*'           => 'exists:members,id',
        ]);
    }

    /**
     * 個別病院選択UI用: 全病院(都道府県付き)
     * 【変更点】tierは変更点1でMember側に移動したため、
     * organizations.tierを参照していたこのメソッドから削除した。
     */
    private function allOrganizations()
    {
        return Organization::with('locationAddress:organization_id,address1')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($o) => [
                'id' => $o->id,
                'name' => $o->name,
                'prefecture' => $o->locationAddress?->address1,
            ]);
    }

    /**
     * 個別先生選択UI用: 全先生
     * 【変更点】グレードで絞り込めるよう、tier・tier_labelを追加。
     * organizationも表示に含める（どの病院の先生か分かりやすくするため）。
     */
    private function allMembers()
    {
        return Member::with('organization:id,name')
            ->orderBy('last_name')
            ->get(['id', 'organization_id', 'last_name', 'first_name', 'tier'])
            ->map(fn ($m) => [
                'id'              => $m->id,
                'name'            => "{$m->last_name} {$m->first_name}",
                'organization_name' => $m->organization?->name,
                'tier'            => $m->tier,
                'tier_label'      => $m->tier_label,
            ]);
    }
}
