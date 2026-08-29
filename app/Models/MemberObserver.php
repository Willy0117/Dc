<?php

namespace App\Observers;

use App\Models\Member;
use App\Models\MemberNameMatchCandidate;

class MemberObserver
{
    /**
     * 新規member登録時、氏名が一致する既存memberがいないか確認し、
     * いれば管理者確認用の候補レコードを作成する。
     */
    public function created(Member $member): void
    {
        $this->checkNameMatch($member);
    }

    /**
     * 氏名（last_name / first_name）が変更された場合も、
     * 新規登録時と同じロジックで名寄せ候補を検知する。
     */
    public function updated(Member $member): void
    {
        if ($member->wasChanged(['last_name', 'first_name'])) {
            $this->checkNameMatch($member);
        }
    }

    /**
     * 氏名一致チェックの共通処理
     *
     * 判定基準（次セッション確認事項での合意通り）：
     *   ・完全一致ではなく、スペース（全角/半角/有無）の正規化程度は行う
     *   ・旧字体マッピング等の高度なあいまい一致は行わない
     */
    private function checkNameMatch(Member $member): void
    {
        $normalizedName = $this->normalize($member->last_name . $member->first_name);

        if ($normalizedName === '') {
            return;
        }

        // 自分以外の全member（退会済みも含めて対象。同一人物が休会→別病院で再登録等もあり得るため）
        $candidates = Member::where('id', '!=', $member->id)
            ->get()
            ->filter(function (Member $other) use ($normalizedName) {
                return $this->normalize($other->last_name . $other->first_name) === $normalizedName;
            });

        foreach ($candidates as $matched) {
            // 既に同じdoctor_group_idに属している場合はスキップ（既に統合済み）
            if ($member->doctor_group_id !== null
                && $member->doctor_group_id === $matched->doctor_group_id) {
                continue;
            }

            MemberNameMatchCandidate::firstOrCreate([
                'member_id'         => $member->id,
                'matched_member_id' => $matched->id,
            ], [
                'matched_name' => $normalizedName,
                'status'       => MemberNameMatchCandidate::STATUS_PENDING,
            ]);
        }
    }

    /**
     * 氏名の正規化：全角/半角スペース・前後の空白を除去する程度に留める。
     * （旧字体マッピング等は対象外。次セッションでの合意事項）
     */
    private function normalize(string $name): string
    {
        $name = str_replace(['　', ' ', "\t", "\n"], '', $name);
        return trim($name);
    }
}
