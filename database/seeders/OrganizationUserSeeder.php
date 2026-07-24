<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class OrganizationUserSeeder extends Seeder
{
    /**
     * 病院(organizations)ごとに MyPage 用ユーザーを1件ずつ作成する。
     *
     * - organization_id に organizations.id を紐付け
     * - username は organizations.code を使用(code が NULL の場合は
     *   org_連番 にフォールバック。varchar(20)制約に収まるかは要確認)
     * - tenant_id は固定で 1 を設定
     * - password は固定値 "12345678"(要運用ルールに合わせて変更してください)
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('12345678'); // 本番投入時は要変更 or 個別発行に置き換え

        Organization::query()->orderBy('id')->chunk(50, function ($organizations) use ($defaultPassword) {
            foreach ($organizations as $organization) {
                $username = $this->buildUsername($organization);
                $email = $this->buildEmail($organization);

                User::updateOrCreate(
                    ['organization_id' => $organization->id],
                    [
                        'tenant_id' => 1,
                        'type' => 1, // 1:病院(organization)
                        'name' => $this->buildName($organization),
                        'username' => $username,
                        'email' => $email,
                        'email_verified_at' => now(),
                        'password' => $defaultPassword,
                        'status' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    private function buildName(Organization $organization): string
    {
        $repName = trim(($organization->rep_last_name ?? '') . ' ' . ($organization->rep_first_name ?? ''));

        return $repName !== ''
            ? $repName
            : ($organization->abbr ?? $organization->name);
    }

    private function buildUsername(Organization $organization): string
    {
        // code を優先利用。code が無い場合は連番でフォールバック
        $base = !empty($organization->code)
            ? $organization->code
            : 'org_' . str_pad((string) $organization->id, 8, '0', STR_PAD_LEFT);

        return substr($base, 0, 20);
    }

    private function buildEmail(Organization $organization): string
    {
        $base = !empty($organization->code)
            ? strtolower($organization->code)
            : 'org' . $organization->id;

        return $base . '@example.com';
    }
}