<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\Organization;
use App\Models\CaseReport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * organization_id=52「個人契約」は、無関係な複数の個人契約医師
 * （柵木晃・清水勇樹・告野英利・田中健太）が同じorganizationを
 * 共用してしまっている特殊ケース。
 *
 * これを解体し、member 1名ごとに個別のorganizationを新規作成して
 * 付け替える。付随して、その先生のcase_reports.organization_idも
 * 新しいorganizationに揃える（case_reports.member_idは既に
 * 名寄せ済みなので、その情報を頼りに1件ずつ振り分ける）。
 */
class SplitSharedIndividualContractOrganization extends Command
{
    protected $signature = 'organizations:split-shared {organization_id}';
    protected $description = '複数の無関係な個人契約medicoが同居しているorganizationを、1医師1organizationに解体する';

    public function handle(): void
    {
        $sourceOrgId = (int) $this->argument('organization_id');
        $sourceOrg   = Organization::findOrFail($sourceOrgId);

        $members = Member::where('organization_id', $sourceOrgId)->get();

        if ($members->count() <= 1) {
            $this->info("organization_id={$sourceOrgId} に所属するmemberは{$members->count()}件のみです。解体不要の可能性があります。処理を中止します。");
            return;
        }

        $this->info("organization_id={$sourceOrgId}（{$sourceOrg->name}）に所属する{$members->count()}名を解体します。");

        // 1人目は元のorganizationをそのまま使う（他の3名分だけ新規作成）か、
        // 全員新規作成して元のorganizationは空にするか -> 後者を採用（一貫性優先）
        foreach ($members as $member) {
            DB::transaction(function () use ($member, $sourceOrg) {
                $nextContractNo = (Organization::max('contract_no') ?? 0) + 1;

                $newOrg = Organization::create([
                    'contract_no'     => $nextContractNo,
                    'name'            => "{$member->last_name} {$member->first_name}",
                    'contract_status' => Organization::STATUS_PERSONAL, // 4:ドクター個人契約
                    'contract_date'   => $sourceOrg->contract_date,
                    'rep_position'    => $member->position,
                    'rep_last_name'   => $member->last_name,
                    'rep_first_name'  => $member->first_name,
                ]);

                $newOrg->code = 'OC' . str_pad((string) $newOrg->contract_no, 5, '0', STR_PAD_LEFT);
                $newOrg->save();

                // memberの所属を新organizationへ付け替え
                $oldMemberNumber = $member->member_number;
                $member->update([
                    'organization_id' => $newOrg->id,
                    'member_number'   => $newOrg->code . '_a',
                ]);

                // この先生のcase_reportsも新organizationに揃える
                // （case_reports.member_idは既に名寄せ済みという前提）
                $movedCaseCount = CaseReport::where('organization_id', $sourceOrg->id)
                    ->where('member_id', $member->id)
                    ->update(['organization_id' => $newOrg->id]);

                \Log::info('SplitSharedIndividualContractOrganization: 解体・付け替え完了', [
                    'member_id'          => $member->id,
                    'old_organization_id'=> $sourceOrg->id,
                    'old_member_number'  => $oldMemberNumber,
                    'new_organization_id'=> $newOrg->id,
                    'new_organization_code' => $newOrg->code,
                    'moved_case_reports' => $movedCaseCount,
                ]);

                $this->line("  {$member->last_name}{$member->first_name} → {$newOrg->code}（symptom報告 {$movedCaseCount}件移動）");
            });
        }

        // 元のorganizationに紐づくmemberが0件になっているか確認
        $remaining = Member::where('organization_id', $sourceOrgId)->count();
        $remainingCases = CaseReport::where('organization_id', $sourceOrgId)->count();

        $this->info("解体完了。元のorganization_id={$sourceOrgId} に残っているmember: {$remaining}件, case_reports: {$remainingCases}件");

        if ($remaining === 0 && $remainingCases === 0) {
            $this->warn("元のorganization（{$sourceOrg->name}）は空になりました。削除するかどうかは手動で判断してください。");
        } else {
            $this->error("解体しきれていないデータが残っています。個別に確認してください。");
        }
    }
}
