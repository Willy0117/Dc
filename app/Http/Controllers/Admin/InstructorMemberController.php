<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;

class InstructorMemberController extends Controller
{
    // 会員一覧
    public function index()
    {
        // role が instructor の会員のみ取得
        $members = Member::where('role', 'instructor')
            ->with(['updateCycles', 'pdfUploads'])
            ->get();

        $members = $members->map(function ($m) {
            $cycle = $m->currentUpdateCycle;

            $m->cycle = $cycle ? [
                'start_date' => $cycle->start_date,
                'end_date' => $cycle->end_date,
                'status' => $cycle->status,
                'total_points' => $m->totalPoints($cycle),
                'conference_count' => $m->conferenceCount($cycle),
            ] : null;

            return $m;
        });

        return inertia('Admin/InstructorMembers/Index', ['members' => $members]);
    }

    // 会員詳細
    public function show(Member $member)
    {
        // role が instructor でなければアクセス不可
        if ($member->role !== 'instructor') {
            abort(403);
        }

        $member->load(['pdfUploads', 'updateCycles']);
        $cycle = $member->currentUpdateCycle;

        return inertia('Admin/InstructorMembers/Show', [
            'member' => $member,
            'cycle' => $cycle,
            'uploads' => $member->pdfUploads,
        ]);
    }
}
