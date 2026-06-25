<?php

// database/seeders/HospitalSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\Organization;
use App\Models\OrganizationAddress;
use App\Models\Member;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/契約病院リスト_修正版.xlsx');
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        foreach (range(3, $highestRow) as $rowIndex) {
            // 直接セルから生の値を取得
            $getValue = fn($col) => $sheet->getCell($col . $rowIndex)->getValue();

            if (empty($getValue('C'))) continue;

            $organization = Organization::firstOrCreate(
                ['name' => trim($getValue('C'))],
                [
                    'contract_no'     => $getValue('A') ?? null,
                    'abbr'            => null,
                    'url'             => $getValue('O') ?? null,
                    'contract_status' => $getValue('B') ?? 0,
                    'contract_date'   => $this->parseDate($getValue('Y')),
                ]
            );

            // 住所
            OrganizationAddress::firstOrCreate(
                ['organization_id' => $organization->id, 'type' => 1],
                [
                    'postal_code' => $getValue('J') ? ltrim((string)$getValue('J'), "'") : null,
                    'address1'    => $getValue('L') ?? null,
                    'address2'    => $getValue('M') ?? null,
                    'address3'    => null,
                    'tel'         => $getValue('P') ?? null,
                ]
            );

            // 担当者①
            if (!empty($getValue('E'))) {
                Member::firstOrCreate(
                    ['email' => $getValue('S'), 'organization_id' => $organization->id],
                    [
                        'organization_id' => $organization->id,
                        'position'        => $getValue('D') ?? null,
                        'last_name'       => $getValue('E') ?? null,
                        'first_name'      => $getValue('F') ?? null,
                        'tel'             => $getValue('Q') ?? null,
                        'email'           => $getValue('S') ?? null,
                        'joined_at'       => $this->parseDate($getValue('Y')),
                        'status_id'       => 1,
                    ]
                );
            }

            // 担当者②
            if (!empty($getValue('H'))) {
                Member::firstOrCreate(
                    ['organization_id' => $organization->id, 'last_name' => $getValue('H')],
                    [
                        'organization_id' => $organization->id,
                        'position'        => $getValue('G') ?? null,
                        'last_name'       => $getValue('H') ?? null,
                        'first_name'      => $getValue('I') ?? null,
                        'status_id'       => 1,
                    ]
                );
            }

            $this->command->info("✔ {$getValue('C')}");
        }
    }

    private function parseDate($value): ?string
    {
        if (empty($value)) return null;

        // シリアル値（数値）
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value)
                ->format('Y-m-d');
        }

        // "8/9/2022" → m/d/Y
        try {
            return \Carbon\Carbon::createFromFormat('n/j/Y', trim($value))->format('Y-m-d');
        } catch (\Exception $e) {}

        // "2022/10/15" → Y/m/d
        try {
            return \Carbon\Carbon::createFromFormat('Y/m/d', trim($value))->format('Y-m-d');
        } catch (\Exception $e) {}

        return null;
    }
}