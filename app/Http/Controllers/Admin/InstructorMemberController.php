<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\PdfUpload;
use Illuminate\Http\Request;

class InstructorMemberController extends Controller
{
    // Index: 会員一覧
    public function index(Request $request)
    {
        $search = $request->search;
        $page = $request->page ?? 1;

        // ベースクエリ
        $query = Member::whereHas('user.roles', function($q){
            $q->where('name', 'instructor');
        })
        ->with(['updateCycles', 'pdfUploads', 'user']);

        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        // ★ paginate にする（これが最重要）
        $members = $query->paginate(20)->through(function ($member) {

            // 各サイクルの集計
            $member->updateCycles->each(function($cycle) use ($member) {

                $cycle->conference_count = PdfUpload::where('member_id', $member->id)
                    ->whereHas('creditCategory', fn($q) => $q->where('name', '学術集会'))
                    ->whereHas('creditConference', fn($q) => $q->where('name', '日本腎臓リハビリテーション学会'))
                    ->whereBetween('created_at', [$cycle->start_date, $cycle->end_date])
                    ->whereHas('creditRole', fn($q) => $q->where('role', '参加'))
                    ->count();

                $cycle->total_points = PdfUpload::where('member_id', $member->id)
                    ->whereHas('creditCategory', fn($q) => $q->where('name', '学術集会'))
                    ->whereBetween('created_at', [$cycle->start_date, $cycle->end_date])
                    ->sum('points');
            });

            return $member;
        });

        return inertia('Admin/InstructorMembers/Index', [
            'members' => $members,
            'filters' => [
                'search' => $search,
                'page' => $page,
            ]
        ]);
    }

    // Show: 会員の PDF 一覧
    public function show(Request $request,Member $member)
    {
        // 会員関連データを読み込む
        $member->load([
            'updateCycles',
            'pdfUploads.creditCategory',
            'pdfUploads.creditConference',
            'pdfUploads.creditRole',
        ]);

        // 最新のサイクル
        $cycle = $member->updateCycles->first();

        if ($cycle) {

            // ★ 学術集会（日本腎臓リハビリテーション学会）の参加回数
            $cycle->conference_count = PdfUpload::where('member_id', $member->id)
                ->whereHas('creditCategory', fn($q) => $q->where('name', '学術集会'))
                ->whereHas('creditConference', fn($q) => $q->where('name', '日本腎臓リハビリテーション学会'))
                ->whereBetween('created_at', [$cycle->start_date, $cycle->end_date])
                ->whereHas('creditRole', fn($q) => $q->where('role', '参加'))
                ->count();

            // ★ 合計単位（学術集会カテゴリ）
            $cycle->total_points = PdfUpload::where('member_id', $member->id)
                ->whereHas('creditCategory', fn($q) => $q->where('name', '学術集会'))
                ->whereBetween('created_at', [$cycle->start_date, $cycle->end_date])
                ->sum('points');
        }

        return inertia('Admin/InstructorMembers/Show', [
            'member'  => $member,
            'uploads' => $member->pdfUploads,
            'filters' => [
                'search' => $request->search,
                'page'   => $request->page,
            ]
        ]);
    }
    /**
     * インストラクター更新サイクルの審査結果を保存
     */
    public function review(Request $request, InstructorUpdateCycle $updateCycle)
    {
        $request->validate([
            'status' => 'required|in:updated,no_update',
            'reason' => 'nullable|string|max:1000',
        ]);

        $updateCycle->status = $request->status;
        $updateCycle->reason = $request->reason;
        $updateCycle->save();

        return redirect()->back()->with('success', __('Review updated successfully.'));
    }

    // PDF 承認
    public function approve($id)
    {
        $upload = PdfUpload::findOrFail($id);
        $upload->status = 'approved';
        $upload->save();

        return back()->with('success', 'PDF approved.');
    }

    // PDF リジェクト
    public function reject(Request $request, $id)
    {
        $upload = PdfUpload::findOrFail($id);
        $upload->status = 'rejected';
        $upload->reject_reason = $request->reason ?? '';
        $upload->save();

        return back()->with('success', 'PDF rejected.');
    }
}

