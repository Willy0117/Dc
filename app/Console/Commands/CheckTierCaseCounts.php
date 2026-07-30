<?php

namespace App\Console\Commands;

use App\Models\Organization;
use App\Models\CaseReport;
use Illuminate\Console\Command;

class CheckTierCaseCounts extends Command
{
    /**
     * 実行例:
     * php artisan tier:check-counts storage/app/tier_list_raw.tsv
     */
    protected $signature = 'tier:check-counts {path : TSVファイルのパス}';

    protected $description = '外部リストの全期間件数と、DBのcase_reports件数を突合し、不一致を報告する（DBは更新しない）';

    public function handle(): int
    {
        $path = $this->argument('path');

        if (!file_exists($path)) {
            $this->error("ファイルが見つかりません: {$path}");
            return self::FAILURE;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $header = str_getcsv(array_shift($lines), "\t");

        $rows = [];
        foreach ($lines as $line) {
            $cols = str_getcsv($line, "\t");
            $rows[] = array_combine($header, $cols);
        }

        $this->info('対象行数: ' . count($rows));

        $mismatches = [];
        $notFound = [];
        $matchedCount = 0;

        foreach ($rows as $row) {
            $contractNo = (int) $row['contract_no'];
            $expectedCount = (int) $row['total_case_count'];
            $listedName = $row['name'];

            $organization = Organization::where('contract_no', $contractNo)->first();

            if (!$organization) {
                $notFound[] = [$contractNo, $listedName];
                continue;
            }

            $actualCount = CaseReport::where('organization_id', $organization->id)->count();

            if ($actualCount !== $expectedCount) {
                $mismatches[] = [
                    $contractNo,
                    $organization->name,
                    $expectedCount,
                    $actualCount,
                    $actualCount - $expectedCount,
                ];
            } else {
                $matchedCount++;
            }
        }

        $this->newLine();
        $this->info("一致: {$matchedCount}件");

        if (!empty($mismatches)) {
            $this->newLine();
            $this->error('不一致: ' . count($mismatches) . '件');
            $this->table(
                ['契約No', '病院名(DB)', 'リスト件数', 'DB件数', '差分'],
                $mismatches
            );
        }

        if (!empty($notFound)) {
            $this->newLine();
            $this->warn('該当organizationが見つからない: ' . count($notFound) . '件');
            $this->table(['契約No', 'リスト上の病院名'], $notFound);
        }

        return self::SUCCESS;
    }
}
