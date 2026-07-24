<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrganizationTierHistorySeeder extends Seeder
{
    public function run(): void
    {
        $organizations = DB::table('organizations')
            ->whereNotNull('license_issued_at')
            ->where(function ($q) {
                $q->whereNotNull('new_contract_date')
                  ->orWhereNotNull('contract_date');
            })
            ->get();

        $this->command->info("対象organizations: {$organizations->count()} 件");

        $inserted = 0;

        foreach ($organizations as $org) {
            $periodStart = Carbon::parse($org->license_issued_at);

            // new_contract_date があればそれを使い、なければ contract_date + 1年
            if (!empty($org->new_contract_date)) {
                $finalEnd = Carbon::parse($org->new_contract_date);
            } elseif (!empty($org->contract_date)) {
                $finalEnd = Carbon::parse($org->contract_date)->addYear();
            } else {
                continue;
            }

            // license_issued_at から new_contract_date まで1年ずつ区切る
            while ($periodStart->lt($finalEnd)) {
                $periodEnd = $periodStart->copy()->addYear()->subDay();

                // period_end が new_contract_date を超えないよう調整
                if ($periodEnd->gte($finalEnd)) {
                    $periodEnd = $finalEnd->copy()->subDay();
                }

                // 期間内の case_reports 件数を集計
                $caseCount = DB::table('case_reports')
                    ->where('organization_id', $org->id)
                    ->whereBetween('submitted_at', [
                        $periodStart->toDateString(),
                        $periodEnd->toDateString() . ' 23:59:59',
                    ])
                    ->count();

                // tier判定（tier3・4は管理者が手動昇格なので自動は1・2のみ）
                $tier = match (true) {
                    $caseCount >= 36 => 2,
                    default          => 1,
                };

                DB::table('organization_tier_histories')->insert([
                    'organization_id' => $org->id,
                    'period_start'    => $periodStart->toDateString(),
                    'period_end'      => $periodEnd->toDateString(),
                    'case_count'      => $caseCount,
                    'tier'            => $tier,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);

                $inserted++;
                $periodStart->addYear();
            }
        }

        $this->command->info("履歴生成: {$inserted} 件");

        // organizations.tier を現在の期間の履歴から更新
        $today = Carbon::today()->toDateString();
        $updated = 0;

        $currentHistories = DB::table('organization_tier_histories')
            ->where('period_start', '<=', $today)
            ->where('period_end', '>=', $today)
            ->get();

        foreach ($currentHistories as $history) {
            DB::table('organizations')
                ->where('id', $history->organization_id)
                ->update(['tier' => $history->tier]);
            $updated++;
        }

        $this->command->info("organizations.tier更新: {$updated} 件");
    }
}
