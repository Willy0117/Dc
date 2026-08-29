<?php

namespace App\Console\Commands;

use App\Models\Member;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * シーダー・直接SQL投入で作られたmemberは、
 * OrganizationController::syncMembers() を経由していないため、
 *   1. member_number が採番されていない
 *   2. 対応する users（ログインアカウント）が作成されていない
 * という2つの不備を抱えている。これを一括で修復する。
 */
class FixMissingMemberNumbersAndUsers extends Command
{
    protected $signature = 'members:fix-missing-numbers-and-users';
    protected $description = 'member_number未採番・users未作成のmemberを一括修復する';

    public function handle(): void
    {
        $fixedNumbers = 0;
        $createdUsers = 0;
        $relinkedUsers = 0;

        // ------------------------------------------------------------
        // Step 1: member_number が未採番のmemberに、
        //         組織コードベースで採番する（syncMembers()と同じロジック）
        // ------------------------------------------------------------
        $targets = Member::whereNull('member_number')
            ->orWhere('member_number', '')
            ->with('organization')
            ->orderBy('organization_id')
            ->orderBy('id')
            ->get()
            ->groupBy('organization_id');

        $suffixes = range('a', 'z');

        foreach ($targets as $organizationId => $members) {
            $organization = $members->first()->organization;

            if (!$organization) {
                $this->warn("organization_id={$organizationId} が見つからないため、{$members->count()}件をスキップします。");
                continue;
            }

            foreach ($members as $member) {
                // 既存の採番済みsuffixを毎回再取得（同一組織内で連番がずれないように）
                $existingSuffixes = Member::where('organization_id', $organization->id)
                    ->whereNotNull('member_number')
                    ->where('member_number', '!=', '')
                    ->pluck('member_number')
                    ->map(fn($n) => str_replace($organization->code . '_', '', $n))
                    ->toArray();

                $suffix = collect($suffixes)->first(fn($s) => !in_array($s, $existingSuffixes));

                if (!$suffix) {
                    $this->error("member_id={$member->id}：組織{$organization->code}の空きsuffixがありません。手動対応してください。");
                    continue;
                }

                $newNumber = $organization->code . '_' . $suffix;
                $member->update(['member_number' => $newNumber]);
                $fixedNumbers++;

                $this->line("  member_id={$member->id} {$member->last_name}{$member->first_name} → {$newNumber}");

                \Log::info('FixMissingMemberNumbersAndUsers: member_number採番', [
                    'member_id'     => $member->id,
                    'member_number' => $newNumber,
                ]);
            }
        }

        $this->info("member_number 採番: {$fixedNumbers}件");

        // ------------------------------------------------------------
        // Step 2: users（ログインアカウント）が存在しないmemberに作成する
        // ------------------------------------------------------------
        $membersWithoutUser = Member::doesntHave('user')->get();

        $this->info("users未作成のmember: {$membersWithoutUser->count()}件");

        foreach ($membersWithoutUser as $member) {
            if (empty($member->email)) {
                $this->warn("member_id={$member->id}（{$member->last_name}{$member->first_name}）：emailが未設定のためuser作成をスキップします。");
                continue;
            }

            // 同じusername（member_number）を持つ既存のusersレコードが
            // 無いか先に確認する（member_idが正しく紐付いていない孤立行の可能性）
            $existingUser = User::where('username', $member->member_number)->first();

            if ($existingUser) {
                $existingUser->update(['member_id' => $member->id]);
                $relinkedUsers++;
                $this->line("  既存user行を紐付け直し: {$member->last_name}{$member->first_name}（{$member->member_number}, user_id={$existingUser->id}）");

                \Log::info('FixMissingMemberNumbersAndUsers: 既存user行を再紐付け', [
                    'member_id' => $member->id,
                    'user_id'   => $existingUser->id,
                    'username'  => $member->member_number,
                ]);
                continue;
            }

            DB::transaction(function () use ($member, &$createdUsers) {
                User::create([
                    'tenant_id' => 1,
                    'member_id' => $member->id,
                    'type'      => 2, // 2:先生(member)
                    'username'  => $member->member_number,
                    'name'      => "{$member->last_name} {$member->first_name}",
                    'email'     => $member->email,
                    'password'  => Hash::make(Str::random(32)),
                    'status'    => 1,
                ]);
                $createdUsers++;
            });

            $this->line("  user作成: {$member->last_name}{$member->first_name}（{$member->member_number}）");
        }

        $this->info("users 新規作成: {$createdUsers}件 / 既存行の再紐付け: {$relinkedUsers}件");
        $this->info('完了しました。');
    }
}
