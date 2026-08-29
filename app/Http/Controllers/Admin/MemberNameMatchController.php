<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MemberNameMatchCandidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MemberNameMatchController extends Controller
{
    /**
     * 未確認の氏名一致候補一覧
     */
    public function index()
    {
        $candidates = MemberNameMatchCandidate::pending()
            ->with(['member.organization', 'matchedMember.organization'])
            ->orderByDesc('created_at')
            ->paginate(50);

        return Inertia::render('Admin/MemberNameMatches/Index', [
            'candidates' => $candidates,
        ]);
    }

    /**
     * 同一人物として統合確定
     * doctor_group_id を、より小さいID（先に登録された方）に揃える。
     * 併せて、該当する2名の全doctor_group_idを一括統合する
     * （どちらかが既に他病院分とグループ化済みの場合も考慮）。
     */
    public function confirm(Request $request, MemberNameMatchCandidate $candidate)
    {
        DB::transaction(function () use ($candidate) {
            $memberA = $candidate->member;
            $memberB = $candidate->matchedMember;

            $groupIdA = $memberA->doctor_group_id ?? $memberA->id;
            $groupIdB = $memberB->doctor_group_id ?? $memberB->id;

            $unifiedGroupId = min($groupIdA, $groupIdB);

            // groupIdA, groupIdB どちらかに属していた全memberを統合
            Member::whereIn('doctor_group_id', [$groupIdA, $groupIdB])
                ->update(['doctor_group_id' => $unifiedGroupId]);

            $candidate->update([
                'status'      => MemberNameMatchCandidate::STATUS_CONFIRMED,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
            ]);

            // 同じペアに対する他の未確認候補も、確定に合わせてconfirmed扱いにする
            MemberNameMatchCandidate::where(function ($q) use ($memberA, $memberB) {
                    $q->where('member_id', $memberA->id)->where('matched_member_id', $memberB->id);
                })
                ->orWhere(function ($q) use ($memberA, $memberB) {
                    $q->where('member_id', $memberB->id)->where('matched_member_id', $memberA->id);
                })
                ->update([
                    'status'      => MemberNameMatchCandidate::STATUS_CONFIRMED,
                    'reviewed_at' => now(),
                ]);

            // 統合後、Tierを合算件数で再計算
            $memberA->fresh()->syncTierFromHistory();
        });

        return back()->with('success', '同一人物として統合しました。');
    }

    /**
     * 別人と判断（却下）
     */
    public function reject(Request $request, MemberNameMatchCandidate $candidate)
    {
        $candidate->update([
            'status'      => MemberNameMatchCandidate::STATUS_REJECTED,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        return back()->with('success', '別人として記録しました。');
    }
}
