<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Services\FileService;

use App\Models\Member;
use App\Models\Organization;
use App\Models\MemberAddress;
use App\Models\MemberEducation;
use App\Models\MemberDegree;
use App\Models\MemberRole;
use App\Models\MemberCommittee;

class MemberController extends Controller
{
    // コンストラクタに追加
    public function __construct(private FileService $fileService) {}


    // ──────────────────────────────────────────
    // 一覧
    // ──────────────────────────────────────────

    public function index(Request $request)
    {
        $sortBy  = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $perPage = (int) $request->input('per_page', 20);

        // 'tier' を追加（変更点1：グレードはmember単位でソート可能にする）
        $allowedSorts = ['id', 'member_number', 'last_name', 'email', 'status_id', 'tier', 'joined_at', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'created_at';

        $members = Member::query()
            // 追加：受講済みステータス表示用に、直近のe-ラーニング招待をeager load
            ->with(['organization', 'currentTierHistory', 'latestElearningInvitation'])
            ->when($request->keyword, fn($q, $kw) => $q->search($kw))
            ->when($request->status_id, fn($q, $s) => $q->where('status_id', $s))
            ->when($request->organization_id, fn($q, $o) => $q->where('organization_id', $o))
            ->when($request->member_type, fn($q, $t) => $q->where('member_type', $t))
            ->when($request->tier, fn($q, $t) => $q->where('tier', $t)) // 追加：グレードで絞り込み
            ->when($request->elearning_status, function ($q, $status) {
                // 追加：受講状況で絞り込み（completed / incomplete）
                if ($status === 'completed') {
                    $q->whereHas('latestElearningInvitation', fn($sub) => $sub->whereNotNull('completed_at'));
                } elseif ($status === 'incomplete') {
                    $q->whereDoesntHave('latestElearningInvitation', fn($sub) => $sub->whereNotNull('completed_at'));
                }
            })
            ->orderBy("members.{$sortBy}", $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        // 受講済みかどうかを各行に付与
        $members->getCollection()->transform(function (Member $member) {
            $member->elearning_completed = $member->latestElearningInvitation?->completed_at !== null;
            $member->elearning_completed_at = $member->latestElearningInvitation?->completed_at?->format('Y-m-d H:i');
            return $member;
        });

        return Inertia::render('Admin/Members/Index', [
            'members' => $members,
            'filters' => [
                'keyword'         => $request->keyword ?? '',
                'status_id'       => $request->status_id ?? '',
                'organization_id' => $request->organization_id ?? '',
                'member_type'     => $request->member_type ?? '',
                'tier'            => $request->tier ?? '',
                'elearning_status'=> $request->elearning_status ?? '', // 追加
                'per_page'        => $perPage,
                'sort_by'         => $sortBy,
                'sort_dir'        => $sortDir,
            ],
            'statusLabels' => Member::STATUS_LABELS,
            'tierLabels'   => Member::TIER_LABELS, // 追加（表示名は「グレード」）
        ]);
    }

    // ──────────────────────────────────────────
    // 作成画面
    // ──────────────────────────────────────────

    public function create()
    {
        return Inertia::render('Admin/Members/Edit', [
            'member'           => null,
            'organization'     => null,
            'home_address'     => null,
            'shipping_address' => null,
            'education'        => null,
            'degrees'          => [],
            'roles'            => [],
            'committees'       => [],
            'filters'          => [],
        ]);
    }

    // ──────────────────────────────────────────
    // 保存
    // ──────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $this->validateMember($request);

        DB::transaction(function () use ($validated) {
            $member = Member::create($validated['member']);
            $this->syncRelatedData($member, $validated);

            // 【変更済み】従来ここで即座にe-ラーニング招待を送信していたが、
            // 変更点：先生登録時ではなく「契約締結・入金確認後」に送信する方式に
            // 変更した（StripeWebhookController / InvoiceController経由、
            // UserInviteService::sendElearningInvitationsForOrganization()参照）。
            // 契約済みの病院に、後から先生が追加された場合（変更点4・5）は、
            // ここで個別に送信する必要がある。
            if ($member->organization
                && $member->organization->contract_status === \App\Models\Organization::STATUS_ACTIVE
                && !empty($member->email)) {
                app(\App\Services\UserInviteService::class)
                    ->sendElearningInvitationIfNeeded($member);
            }
        });

        return redirect()->route('admin.members.index')
            ->with('success', '会員を登録しました。');
    }

    // ──────────────────────────────────────────
    // 詳細
    // ──────────────────────────────────────────

    public function show(Request $request, Member $member)
    {
        $member->load([
            'organization.locationAddress',
            'addresses',
            'educations',
            'degrees',
            'roles',
            'committees',
            'tierHistories',
            'elearningInvitations',
        ]);

        return Inertia::render('Admin/Members/Show', [
            'member'  => $this->formatMember($member),
            'filters' => $request->only(['keyword', 'status_id', 'per_page', 'sort_by', 'sort_dir', 'page']),
        ]);
    }

    // ──────────────────────────────────────────
    // 編集画面
    // ──────────────────────────────────────────

    public function edit(Member $member, Request $request)
    {
        $member->load([
            'organization',
            'addresses',
            'educations',
            'degrees',
            'roles',
            'committees',
        ]);

        $homeAddress     = $member->addresses->firstWhere('type', MemberAddress::TYPE_HOME);
        $shippingAddress = $member->addresses->firstWhere('type', MemberAddress::TYPE_SHIPPING);

        return Inertia::render('Admin/Members/Edit', [
            'member'          => $member,
            'organization'    => $member->organization,
            'home_address'    => $homeAddress,
            'shipping_address'=> $shippingAddress,
            'education'       => $member->educations->first(),
            'degrees'         => $member->degrees,
            'roles'           => $member->roles,
            'committees'      => $member->committees,
            'filters'         => $request->only(['keyword', 'status_id', 'per_page', 'sort_by', 'sort_dir']),
        ]);
    }

    // ──────────────────────────────────────────
    // 更新
    // ──────────────────────────────────────────

    public function update(Request $request, Member $member)
    {
        $validated = $this->validateMember($request, $member->id);

        DB::transaction(function () use ($member, $validated) {
            $member->update($validated['member']);
            $this->syncRelatedData($member, $validated);
        });

        return redirect()->route('admin.members.index')
            ->with('success', '会員情報を更新しました。');
    }

    // ──────────────────────────────────────────
    // 削除
    // ──────────────────────────────────────────

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('admin.members.index')
            ->with('success', '会員を削除しました。');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:members,id',
        ]);

        Member::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.members.index')
            ->with('success', '選択した会員を削除しました。');
    }

    // ──────────────────────────────────────────
    // 組織検索（Vue用オートコンプリート）
    // ──────────────────────────────────────────

    public function searchOrganizations(Request $request)
    {
        $organizations = Organization::search($request->input('q', ''))
            ->select('id', 'name', 'abbr')
            ->limit(20)
            ->get();

        return response()->json($organizations);
    }

    // ──────────────────────────────────────────
    // ステータス更新（モーダル用）
    // ──────────────────────────────────────────

    public function updateStatus(Request $request, Member $member)
    {
        $request->validate([
            'status_id' => 'required|integer|in:1,2,3',
        ]);

        $member->update(['status_id' => $request->status_id]);

        return response()->json(['ok' => true]);
    }

    // ──────────────────────────────────────────
    // 氏名一致検索（管理画面：先生フォーム入力中のリアルタイムチェック用）
    // ──────────────────────────────────────────

    public function checkNameMatch(Request $request)
    {
        $validated = $request->validate([
            'last_name'  => 'required|string',
            'first_name' => 'required|string',
            'exclude_id' => 'nullable|integer',
        ]);

        $normalize = fn (string $s) => trim(str_replace(['　', ' ', "\t", "\n"], '', $s));
        $targetName = $normalize($validated['last_name'] . $validated['first_name']);

        if ($targetName === '') {
            return response()->json(['matches' => []]);
        }

        $matches = Member::with('organization:id,name')
            ->when($request->exclude_id, fn ($q, $id) => $q->where('id', '!=', $id))
            ->get()
            ->filter(fn (Member $m) => $normalize($m->last_name . $m->first_name) === $targetName)
            ->map(fn (Member $m) => [
                'id'                => $m->id,
                'full_name'         => $m->full_name,
                'organization_name' => $m->organization?->name,
                'doctor_group_id'   => $m->doctor_group_id,
            ])
            ->values();

        return response()->json(['matches' => $matches]);
    }

    // ──────────────────────────────────────────
    // グレード昇格・降格（単一エンドポイントで両対応）
    // ルート名・カラム名(tier)は据え置き、表示名のみ「グレード」
    // ──────────────────────────────────────────

    public function upgradeTier(Request $request, Member $member)
    {
        $request->validate([
            'tier' => 'required|integer|in:1,2,3,4',
        ]);

        $newTier = (int) $request->tier;

        Member::where('doctor_group_id', $member->doctor_group_id)
            ->update(['tier' => $newTier]);

        \Log::info('MemberController: グレード手動変更', [
            'member_id'       => $member->id,
            'doctor_group_id' => $member->doctor_group_id,
            'new_tier'        => $newTier,
        ]);

        return back()->with('success', 'グレードを変更しました。');
    }

    public function downgradeTier(Request $request, Member $member)
    {
        $request->validate([
            'tier' => 'required|integer|in:1,2,3',
        ]);

        $newTier = (int) $request->tier;

        Member::where('doctor_group_id', $member->doctor_group_id)
            ->update(['tier' => $newTier]);

        $history = $member->currentTierHistory;
        $history?->update(['tier' => $newTier]);

        return back()->with('success', "{$member->full_name} のグレードを変更しました。");
    }

    // ──────────────────────────────────────────
    // PDFアップロード
    // ──────────────────────────────────────────

    public function uploadDocument(Request $request, Member $member)
    {
        $request->validate([
            'type_id'  => 'required|integer|in:1,2,3,4',
            'document' => 'required|file|mimes:pdf|max:10240',
        ]);

        [$filePath, $thumbPath] = $this->fileService->storeUploadedFile(
            $request->file('document'),
            'members/documents'
        );

        return response()->json([
            'success'       => true,
            'file_url'      => $this->fileService->getUrl($filePath),
            'thumbnail_url' => $thumbPath ? $this->fileService->getUrl($thumbPath) : null,
        ]);
    }

    // ──────────────────────────────────────────
    // Private: バリデーション
    // ──────────────────────────────────────────

    private function validateMember(Request $request, ?int $memberId = null): array
    {
        $memberRules = [
            'member.organization_id' => 'nullable|exists:organizations,id',
            'member.member_number'   => [
                'required', 'string', 'max:20',
                $memberId
                    ? "unique:members,member_number,{$memberId}"
                    : 'unique:members,member_number',
            ],
            'member.position'        => 'nullable|string|max:20',
            'member.last_name'       => 'required|string|max:100',
            'member.first_name'      => 'required|string|max:100',
            'member.last_name_kana'  => 'nullable|string|max:100',
            'member.first_name_kana' => 'nullable|string|max:100',
            'member.gender'          => 'nullable|in:male,female,other',
            'member.birthdate'       => 'nullable|date',
            'member.tel'             => 'nullable|string|max:30',
            'member.mobile'          => 'nullable|string|max:30',
            'member.fax'             => 'nullable|string|max:30',
            'member.email'           => 'required|email|max:255',
            'member.personal_email'  => 'nullable|email|max:255',
            'member.status_id'       => 'nullable|integer|in:1,2,3',
            'member.member_type'     => 'nullable|string|max:50',
            'member.joined_at'       => 'nullable|date',
            'member.withdrawn_at'    => 'nullable|date',
        ];

        $addressRules = [
            'home_address.postal_code' => 'nullable|string|max:20',
            'home_address.address1'    => 'nullable|string|max:255',
            'home_address.address2'    => 'nullable|string|max:255',
            'home_address.address3'    => 'nullable|string|max:255',
            'home_address.tel'         => 'nullable|string|max:30',
            'home_address.fax'         => 'nullable|string|max:30',

            'shipping_address.postal_code' => 'nullable|string|max:20',
            'shipping_address.address1'    => 'nullable|string|max:255',
            'shipping_address.address2'    => 'nullable|string|max:255',
            'shipping_address.address3'    => 'nullable|string|max:255',
        ];

        $otherRules = [
            'education.school_name'  => 'nullable|string|max:255',
            'education.faculty'      => 'nullable|string|max:255',
            'education.graduated_at' => 'nullable|string|max:20',

            'degrees'             => 'nullable|array|max:5',
            'degrees.*.degree'    => 'nullable|string|max:100',
            'degrees.*.obtained_at' => 'nullable|string|max:20',

            'roles'               => 'nullable|array',
            'roles.*.role'        => 'nullable|string|max:100',
            'roles.*.started_at'  => 'nullable|string|max:20',
            'roles.*.ended_at'    => 'nullable|string|max:20',

            'committees'               => 'nullable|array',
            'committees.*.committee'   => 'nullable|string|max:100',
            'committees.*.started_at'  => 'nullable|string|max:20',
            'committees.*.ended_at'    => 'nullable|string|max:20',
        ];

        return $request->validate(array_merge($memberRules, $addressRules, $otherRules));
    }

    // ──────────────────────────────────────────
    // Private: 関連データの同期
    // ──────────────────────────────────────────

    private function syncRelatedData(Member $member, array $data): void
    {
        if (!empty($data['home_address'])) {
            MemberAddress::updateOrCreate(
                ['member_id' => $member->id, 'type' => MemberAddress::TYPE_HOME],
                $data['home_address']
            );
        }

        if (!empty($data['shipping_address'])) {
            MemberAddress::updateOrCreate(
                ['member_id' => $member->id, 'type' => MemberAddress::TYPE_SHIPPING],
                $data['shipping_address']
            );
        } else {
            MemberAddress::where('member_id', $member->id)
                ->where('type', MemberAddress::TYPE_SHIPPING)
                ->delete();
        }

        if (!empty($data['education'])) {
            MemberEducation::updateOrCreate(
                ['member_id' => $member->id],
                $data['education']
            );
        }

        if (isset($data['degrees'])) {
            $member->degrees()->delete();
            foreach ($data['degrees'] as $degree) {
                if (!empty($degree['degree'])) {
                    $member->degrees()->create($degree);
                }
            }
        }

        if (isset($data['roles'])) {
            $member->roles()->delete();
            foreach ($data['roles'] as $role) {
                if (!empty($role['role'])) {
                    $member->roles()->create($role);
                }
            }
        }

        if (isset($data['committees'])) {
            $member->committees()->delete();
            foreach ($data['committees'] as $committee) {
                if (!empty($committee['committee'])) {
                    $member->committees()->create($committee);
                }
            }
        }
    }

    // ──────────────────────────────────────────
    // Private: 詳細用フォーマット
    // ──────────────────────────────────────────

    private function formatMember(Member $member): array
    {
        $homeAddress     = $member->addresses->firstWhere('type', MemberAddress::TYPE_HOME);
        $shippingAddress = $member->addresses->firstWhere('type', MemberAddress::TYPE_SHIPPING);

        return [
            'id'             => $member->id,
            'member_number'  => $member->member_number,
            'full_name'      => $member->full_name,
            'full_name_kana' => $member->full_name_kana,
            'last_name'      => $member->last_name,
            'first_name'     => $member->first_name,
            'position'       => $member->position,
            'gender'         => $member->gender,
            'gender_label'   => $member->gender_label,
            'birthdate'      => $member->birthdate?->format('Y-m-d'),
            'tel'            => $member->tel,
            'mobile'         => $member->mobile,
            'fax'            => $member->fax,
            'email'          => $member->email,
            'personal_email' => $member->personal_email,
            'status_id'      => $member->status_id,
            'status_label'   => $member->status_label,
            'member_type'    => $member->member_type,
            'joined_at'      => $member->joined_at?->format('Y-m-d'),
            'withdrawn_at'   => $member->withdrawn_at?->format('Y-m-d'),
            'tier'           => $member->tier,
            'tier_label'     => $member->tier_label,
            'tier_histories' => $member->tierHistories,
            'elearning_invitations' => $member->elearningInvitations,
            'elearning_completed'   => $member->hasCompletedElearning(),
            'organization'   => $member->organization ? [
                'id'   => $member->organization->id,
                'name' => $member->organization->name,
                'abbr' => $member->organization->abbr,
                'url'  => $member->organization->url,
                'location' => $member->organization->locationAddress,
            ] : null,
            'home_address'     => $homeAddress,
            'shipping_address' => $shippingAddress,
            'educations'       => $member->educations,
            'degrees'          => $member->degrees,
            'roles'            => $member->roles,
            'committees'       => $member->committees,
            'latest_cycle'     => $member->latestCycle,
            'created_at'       => $member->created_at->format('Y-m-d'),
        ];
    }
}