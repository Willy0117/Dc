<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use App\Models\Organization;
use App\Models\OrganizationAddress;
use App\Models\LicenseFeeMaster;
use App\Models\User;

use App\Services\InvoiceService;
use App\Services\StripeService;
use App\Services\LicenseService;

class OrganizationController extends Controller
{
    // ──────────────────────────────────────────
    // 一覧
    // ──────────────────────────────────────────

    public function index(Request $request)
    {
        $sortBy  = $request->input('sort_by', 'contract_date');
        $sortDir = $request->input('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';
        $perPage = (int) $request->input('per_page', 20);

        $allowedSorts = ['id', 'name', 'contract_no', 'contract_status', 'contract_date', 'tel'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'contract_date';

        $organizations = Organization::query()
            ->leftJoin('organization_addresses', function ($join) {
                $join->on('organizations.id', '=', 'organization_addresses.organization_id')
                    ->where('organization_addresses.type', OrganizationAddress::TYPE_LOCATION);
            })
            ->select(
                'organizations.*',
                'organization_addresses.tel as location_tel',
                'organization_addresses.address1 as location_address1'
            )
            ->with(['locationAddress', 'addresses'])
            ->when($request->keyword, fn($q, $kw) =>
                $q->where(function ($sub) use ($kw) {
                    $sub->where('organizations.name', 'like', "%{$kw}%")
                        ->orWhere('organizations.abbr', 'like', "%{$kw}%");
                })
            )
            ->when($request->contract_status !== null && $request->contract_status !== '' && $request->contract_status !== 'all',
                fn($q) => $q->where('organizations.contract_status', $request->contract_status)
            )
            ->when($request->address1 && $request->address1 !== 'all',
                fn($q, $a) => $q->where('organization_addresses.address1', 'like', "%{$a}%")
            )
            ->when($request->payment_method && $request->payment_method !== 'all',
                fn($q) => $q->where('organizations.payment_method', $request->payment_method)
            )
            // 契約日の期間指定
            ->when($request->contract_date_from,
                fn($q, $d) => $q->where('organizations.contract_date', '>=', $d)
            )
            ->when($request->contract_date_to,
                fn($q, $d) => $q->where('organizations.contract_date', '<=', $d)
            )
            ->orderBy(
                $sortBy === 'tel' ? 'organization_addresses.tel' : "organizations.{$sortBy}",
                $sortDir
            )
            ->paginate($perPage)
            ->withQueryString();

        $organizations->getCollection()->transform(function ($org) {
            $org->documents_map = ($org->applicationDocuments ?? collect())
                ->sortByDesc('id')
                ->unique('type')
                ->keyBy('type')
                ->map(fn($doc) => [
                    'id'       => $doc->id,
                    'type'     => $doc->type,
                    'pdf_url'  => Storage::url($doc->file_path),
                ]);
            return $org;
        });

        return Inertia::render('Admin/Organizations/Index', [
            'organizations'        => $organizations,
            'filters'              => [
                'keyword'            => $request->keyword            ?? '',
                'contract_status'    => $request->contract_status    ?? '',
                'address1'           => $request->address1           ?? '',
                'contract_date_from' => $request->contract_date_from ?? '',
                'contract_date_to'   => $request->contract_date_to   ?? '',
                'payment_method'     => $request->payment_method     ?? '',
                'per_page'           => $perPage,
                'sort_by'            => $sortBy,
                'sort_dir'           => $sortDir,
                'page'               => $request->input('page', 1), 
            ],
            'contractStatusLabels' => Organization::STATUS_LABELS,
        ]);
    }
    // ──────────────────────────────────────────
    // 新規作成
    // ──────────────────────────────────────────

    public function create()
    {
        return $this->edit(request(), new Organization());
    }
    // ──────────────────────────────────────────
    // 保存
    // ──────────────────────────────────────────

    public function store(Request $request)
    {
        $validated = $this->validateOrganization($request);

        DB::transaction(function () use ($validated) {
            $nextContractNo = (Organization::max('contract_no') ?? 0) + 1;

            $organization = Organization::create(array_merge(
                $validated['organization'],
                ['contract_no' => $nextContractNo]
            ));

            $organization->code = 'OC' . str_pad($organization->contract_no, 5, '0', STR_PAD_LEFT);
            $organization->save();

            
            $this->syncAddresses($organization, $validated);
            $this->syncMembers($organization, $validated);
        });

        return redirect()->route('admin.organizations.index')
            ->with('success', '契約先を登録しました。');
    }

    // ──────────────────────────────────────────
    // 詳細
    // ──────────────────────────────────────────

    public function show(Request $request, Organization $organization)
    {
        $organization->load(['addresses', 'members']);

        return Inertia::render('Admin/Organizations/Show', [
            'organization' => $this->formatOrganization($organization),
            'filters' => $request->only([
                'keyword',
                'contract_status',
                'address1',
                'contract_date_from',  // ← 追加
                'contract_date_to',    // ← 追加
                'payment_method',      // ← 追加
                'per_page',
                'sort_by',
                'sort_dir',
                'page',                // ← 追加
            ]),
        ]);
    }

    // ──────────────────────────────────────────
    // 編集画面
    // ──────────────────────────────────────────
    public function edit(Request $request, ?Organization $organization = null)
    {
        if ($organization?->id) {
            $organization->load(['addresses', 'members.addresses']);
            $locationAddress = $organization->addresses->firstWhere('type', OrganizationAddress::TYPE_LOCATION);
            $shippingAddress = $organization->addresses->firstWhere('type', OrganizationAddress::TYPE_SHIPPING);
            $billingAddress  = $organization->addresses->firstWhere('type', OrganizationAddress::TYPE_BILLING);
        }

        return Inertia::render('Admin/Organizations/Edit', [
            'organization'     => $organization?->id ? $organization : null,
            'location_address' => $locationAddress ?? null,
            'shipping_address' => $shippingAddress ?? null,
            'billing_address'  => $billingAddress  ?? null,
            'members' => $organization?->id ? $organization->members->map(fn($m) => [
                'id'              => $m->id,
                'member_number'   => $m->member_number,
                'position'        => $m->position,
                'last_name'       => $m->last_name,
                'first_name'      => $m->first_name,
                'last_name_kana'  => $m->last_name_kana,
                'first_name_kana' => $m->first_name_kana,
                'gender'          => $m->gender,
                'birthdate'       => $m->birthdate?->format('Y-m-d'),
                'tel'             => $m->tel,
                'mobile'          => $m->mobile,
                'fax'             => $m->fax,
                'email'           => $m->email,
                'personal_email'  => $m->personal_email,
                'status_id'       => $m->status_id,
                'member_type'     => $m->member_type,
                'joined_at'       => $m->joined_at?->format('Y-m-d'),
                'withdrawn_at'    => $m->withdrawn_at?->format('Y-m-d'),
                'addresses'       => $m->addresses,  // ← 追加
            ]) : [],
            'filters' => $request->only([
                'keyword',
                'contract_status',
                'address1',
                'contract_date_from',  // ← 追加
                'contract_date_to',    // ← 追加
                'payment_method',      // ← 追加
                'per_page',
                'sort_by',
                'sort_dir',
                'page',                // ← 追加
            ]),
         ]);
    }

    // ──────────────────────────────────────────
    // 更新
    // ──────────────────────────────────────────

    public function update(Request $request, Organization $organization)
    {
        $validated = $this->validateOrganization($request);

        DB::transaction(function () use ($organization, $validated) {
            $organization->update($validated['organization']);
            $this->syncAddresses($organization, $validated);
            $this->syncMembers($organization, $validated);
        });
    }

    // ──────────────────────────────────────────
    // 削除
    // ──────────────────────────────────────────

    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()->route('admin.organizations.index')
            ->with('success', '契約を削除しました。');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:organizations,id',
        ]);

        Organization::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.organizations.index')
            ->with('success', '選択した契約を削除しました。');
    }
    // ──────────────────────────────────────────
    // 契約申込メール送信（1件）
    // ──────────────────────────────────────────
    public function sendInvitation(Request $request, Organization $organization)
    {
        $request->validate([
            'email'             => 'required|email',
            'contract_date'     => 'required|date',
            'new_contract_date' => 'required|date',
            'needs_agreement'   => 'boolean',
        ]);
 
        // contract_date・new_contract_date を organizations テーブルに保存
        // new_contract_date が既に設定済みの場合は上書きしない
        $updates = ['contract_date' => $request->contract_date];
        if (!$organization->new_contract_date) {
            $updates['new_contract_date'] = $request->new_contract_date;
        }
        $organization->update($updates);
 
        // 申込URLを生成（合意書が必要な場合は agreement パラメータを付加）
        $token = $organization->generateRegisterToken();
        $url   = route('applications.register', array_filter([
            'token'     => $token,
            'agreement' => $request->boolean('needs_agreement') ? 1 : null,
        ]));
 
        \Mail::to($request->email)
            ->send(new \App\Mail\InvitationMail($organization, $url));
 
        return redirect()->back()->with('success', '契約申込メールを送信しました。');
    }

    // ──────────────────────────────────────────
    // 契約申込メール送信（複数）
    // ──────────────────────────────────────────
    public function bulkSendInvitation(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:organizations,id',
        ]);

        Organization::whereIn('id', $request->ids)
            ->get()
            ->each(function ($organization) {
                $token = $organization->generateRegisterToken();
                $url   = route('applications.register', ['token' => $token]);

                \Mail::to($organization->billing_email)
                    ->send(new \App\Mail\InvitationMail($organization, $url));
            });

        return redirect()->back()->with('success', '契約申込メールを送信しました。');
    }
 

    // ──────────────────────────────────────────
    // 請求書作成・送信（単発）
    // ──────────────────────────────────────────
    public function createInvoice(Request $request)
    {
        $validated = $request->validate([
            'organization_ids'   => 'required|array|min:1|max:1', // 単発のみ対応
            'organization_ids.*' => 'required|exists:organizations,id',
            'amount'             => 'required|integer|min:0',
            'due_date'           => 'required|date',
            'note'               => 'nullable|string|max:1000',
            'send_email'         => 'boolean',
            'base'               => 'nullable|integer|min:0',
            'extra'              => 'nullable|integer|min:0',
        ]);
 
        $organization = Organization::with(['locationAddress'])
            ->findOrFail($validated['organization_ids'][0]);
 
        $total    = (int) $validated['amount'];
        $subtotal = (int) round($total / 1.1);
        $tax      = $total - $subtotal;
        $base     = $validated['base']  ?? $subtotal;
        $extra    = $validated['extra'] ?? 0;
 
        $feeMaster = $this->getFeeMaster($organization);
 
        $data = [
            'corp_name'     => $organization->name,
            'email'         => $organization->locationAddress?->email,
            'postal_code'   => $organization->locationAddress?->postal_code,
            'subtotal'      => $subtotal,
            'tax'           => $tax,
            'total'         => $total,
            'corporate_fee' => $base,
            'personal_fee'  => $feeMaster->personal_fee,
            'extra'         => $extra,
            'due_date'      => $validated['due_date'],
            'note'          => $validated['note'] ?? '',
        ];
 
        if (!$data['email']) {
            return back()->withErrors(['error' => '送付先メールアドレスが登録されていません。']);
        }
 
        try {
            $invoice = app(InvoiceService::class)->createAndSend($organization, $data);

            \Log::info('管理画面: 請求書発行', [
                'organization_id' => $organization->id,
                'invoice_id'      => $invoice->id,
                'invoice_no'      => $invoice->invoice_no,
            ]);
 
            return back()->with('success', '請求書を作成し、メールを送信しました。');
        } catch (\Throwable $e) {
            \Log::error('管理画面: 請求書発行エラー', [
                'organization_id' => $organization->id,
                'message'         => $e->getMessage(),
            ]);
 
            return back()->withErrors(['error' => '請求書の作成に失敗しました。']);
        }
    }

    // ──────────────────────────────────────────
    // Stripe決済リンク作成・送信（単発）
    // ──────────────────────────────────────────
    public function createStripePayment(Request $request)
    {
        $validated = $request->validate([
            'organization_ids'   => 'required|array|min:1|max:1', // 単発のみ対応
            'organization_ids.*' => 'required|exists:organizations,id',
            'amount'             => 'required|integer|min:0',
            'due_date'           => 'nullable|date',
            'note'               => 'nullable|string|max:1000',
            'send_email'         => 'boolean',
            'base'               => 'nullable|integer|min:0',
            'extra'              => 'nullable|integer|min:0',
        ]);
 
        $organization = Organization::with(['locationAddress'])
            ->findOrFail($validated['organization_ids'][0]);
 
        $total    = (int) $validated['amount'];
        $subtotal = (int) round($total / 1.1);
        $tax      = $total - $subtotal;
        $base     = $validated['base']  ?? $subtotal;
        $extra    = $validated['extra'] ?? 0;
 
        $feeMaster = $this->getFeeMaster($organization);
 
        $data = [
            'corp_name'     => $organization->name,
            'email'         => $organization->locationAddress?->email,
            'postal_code'   => $organization->locationAddress?->postal_code,
            'subtotal'      => $subtotal,
            'tax'           => $tax,
            'total'         => $total,
            'corporate_fee' => $base,
            'personal_fee'  => $feeMaster->personal_fee,
            'extra'         => $extra,
            'due_date'      => $validated['due_date'] ?? now()->addDays(30)->toDateString(),
            'note'          => $validated['note'] ?? '',
        ];
 
        if (!$data['email']) {
            return back()->withErrors(['error' => '送付先メールアドレスが登録されていません。']);
        }
 
        try {
            $invoice = app(StripeService::class)->createAndSend($organization, $data);
 
            \Log::info('管理画面: Stripe決済リンク発行', [
                'organization_id' => $organization->id,
                'invoice_id'      => $invoice->id,
                'invoice_no'      => $invoice->invoice_no,
            ]);
 
            return back()->with('success', 'Stripe決済リンクを作成し、メールを送信しました。');
        } catch (\Throwable $e) {
            \Log::error('管理画面: Stripe決済リンク発行エラー', [
                'organization_id' => $organization->id,
                'message'         => $e->getMessage(),
            ]);
 
            return back()->withErrors(['error' => 'Stripe決済リンクの作成に失敗しました。']);
        }
    }

    // ──────────────────────────────────────────
    // ライセンス証作成・送信（単発）
    // ──────────────────────────────────────────

    public function issueLicense(Request $request, $id)
    {
        $organization = Organization::findOrFail($id);

        try {
            $fileName = app(LicenseService::class)->create($organization, $request->display_name);

            return response()->json([
                'url' => Storage::url($fileName),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    public function mailLicense($id, Request $request)
    {
        $organization = Organization::findOrFail($id);

        try {
            app(LicenseService::class)->send($organization, $request->email, $request->pdf_path);

            return response()->json(['message' => 'メールを送付しました。']);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
    // ──────────────────────────────────────────
    // リマインダーメール一括送信
    // ──────────────────────────────────────────
    public function bulkSendReminder(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'exists:organizations,id',
        ]);

        Organization::whereIn('id', $request->ids)
            ->whereNotNull('new_contract_date')
            ->with('locationAddress')
            ->get()
            ->each(function ($organization) {
                $email = $organization->locationAddress?->email;
                if (!$email) return;

                \Mail::to($email)->send(new \App\Mail\ReminderMail($organization));
            });

        return back()->with('success', 'リマインダーメールを送信しました。');
    }

    // ──────────────────────────────────────────
    // 自由記述メール一括送信
    // ──────────────────────────────────────────
    public function bulkSendMail(Request $request)
    {
        $request->validate([
            'ids'     => 'required|array',
            'ids.*'   => 'exists:organizations,id',
            'subject' => 'required|string|max:255',
            'body'    => 'required|string',
        ]);

        Organization::whereIn('id', $request->ids)
            ->with('locationAddress')
            ->get()
            ->each(function ($organization) use ($request) {
                $email = $organization->locationAddress?->email;
                if (!$email) return;

                $body = str_replace('{organization_name}', $organization->name, $request->body);

                \Mail::to($email)->send(new \App\Mail\BulkMail(
                    $request->subject,
                    $body,
                    $organization->name,  // ← 追加
                ));
            });

        return back()->with('success', 'メールを送信しました。');
    }

    // ──────────────────────────────────────────
    // 検索API（Member form用）
    // ──────────────────────────────────────────

    public function search(Request $request)
    {
        $organizations = Organization::search($request->input('q', ''))
            ->select('id', 'name', 'abbr')
            ->limit(20)
            ->get();

        return response()->json($organizations);
    }

    // ──────────────────────────────────────────
    // Private: バリデーション
    // ──────────────────────────────────────────

    private function validateOrganization(Request $request): array
    {
        return $request->validate([
            'organization.contract_no'     => 'nullable|integer',
            'organization.name'            => 'required|string|max:255',
            'organization.abbr'            => 'nullable|string|max:100',
            'organization.url'             => 'nullable|url|max:255',
            'organization.contract_status' => 'required|integer|in:0,1,2,3,4,5',
            'organization.contract_date'   => 'nullable|date',
            'organization.payment_method'  => 'nullable|integer|in:1,2',
            'organization.rep_position'    => 'nullable|string|max:50',
            'organization.rep_last_name'   => 'required|string|max:100',
            'organization.rep_first_name'  => 'required|string|max:100',

            'location_address.name'        => 'nullable|string|max:255',
            'location_address.postal_code' => 'nullable|string|max:20',
            'location_address.address1'    => 'nullable|string|max:255',
            'location_address.address2'    => 'nullable|string|max:255',
            'location_address.address3'    => 'nullable|string|max:255',
            'location_address.tel'         => 'nullable|string|max:30',
            'location_address.fax'         => 'nullable|string|max:30',
            'location_address.email'       => 'nullable|email|max:255',

            'shipping_address.name'        => 'nullable|string|max:255',
            'shipping_address.postal_code' => 'nullable|string|max:20',
            'shipping_address.address1'    => 'nullable|string|max:255',
            'shipping_address.address2'    => 'nullable|string|max:255',
            'shipping_address.address3'    => 'nullable|string|max:255',
            'shipping_address.tel'         => 'nullable|string|max:30',
            'shipping_address.fax'         => 'nullable|string|max:30',
            'shipping_address.email'       => 'nullable|email|max:255',

            'billing_address.name'         => 'nullable|string|max:255',
            'billing_address.postal_code'  => 'nullable|string|max:20',
            'billing_address.address1'     => 'nullable|string|max:255',
            'billing_address.address2'     => 'nullable|string|max:255',
            'billing_address.address3'     => 'nullable|string|max:255',
            'billing_address.tel'          => 'nullable|string|max:30',
            'billing_address.fax'          => 'nullable|string|max:30',
            'billing_address.email'        => 'nullable|email|max:255',
            'members'                        => 'nullable|array',
            'members.*.id'                   => 'nullable|integer',
            'members.*.last_name'            => 'required|string|max:100',
            'members.*.first_name'           => 'required|string|max:100',
            'members.*.last_name_kana'       => 'nullable|string|max:100',
            'members.*.first_name_kana'      => 'nullable|string|max:100',
            'members.*.member_number'        => 'nullable|string|max:20',
            'members.*.position'             => 'nullable|string|max:20',
            'members.*.gender'               => 'nullable|string|max:20',
            'members.*.birthdate'            => 'nullable|date',
            'members.*.tel'                  => 'nullable|string|max:30',
            'members.*.mobile'               => 'nullable|string|max:30',
            'members.*.fax'                  => 'nullable|string|max:30',
            'members.*.email'                => 'nullable|email|max:255',
            'members.*.personal_email'       => 'nullable|email|max:255',
            'members.*.status_id'            => 'nullable|integer',
            'members.*.member_type'          => 'nullable|string|max:50',
            'members.*.joined_at'            => 'nullable|date',
            'members.*.withdrawn_at'         => 'nullable|date',
            'members.*.addresses'                => 'nullable|array',
            'members.*.addresses.*.type'         => 'required|integer|in:1,2',
            'members.*.addresses.*.postal_code'  => 'nullable|string|max:20',
            'members.*.addresses.*.address1'     => 'nullable|string|max:255',
            'members.*.addresses.*.address2'     => 'nullable|string|max:255',
            'members.*.addresses.*.address3'     => 'nullable|string|max:255',
            'members.*.addresses.*.tel'          => 'nullable|string|max:30',
            'members.*.addresses.*.fax'          => 'nullable|string|max:30',
        ]);
    }

    // ──────────────────────────────────────────
    // Private: 住所同期
    // ──────────────────────────────────────────

    private function syncAddresses(Organization $organization, array $data): void
    {
        $types = [
            'location_address' => OrganizationAddress::TYPE_LOCATION,
            'shipping_address' => OrganizationAddress::TYPE_SHIPPING,
            'billing_address'  => OrganizationAddress::TYPE_BILLING,
        ];

        foreach ($types as $key => $type) {
            if (!empty($data[$key])) {
                OrganizationAddress::updateOrCreate(
                    ['organization_id' => $organization->id, 'type' => $type],
                    $data[$key]
                );
            }
        }
    }

    // ──────────────────────────────────────────
    // Private: 住所同期
    // ──────────────────────────────────────────

    private function syncMembers(Organization $organization, array $validated): void
    {
        $existingIds  = $organization->members->pluck('id')->toArray();
        $submittedIds = collect($validated['members'] ?? [])->pluck('id')->filter()->toArray();

        // 送信されなかったものは削除
        $organization->members()
            ->whereIn('id', array_diff($existingIds, $submittedIds))
            ->delete();

        $suffixes = range('a', 'z');
        $newMemberIndex = 0;

        foreach (($validated['members'] ?? []) as $index => $memberData) {
            $addresses = $memberData['addresses'] ?? [];
            unset($memberData['addresses']);

            if (!empty($memberData['id'])) {
                // 更新：member_numberはそのまま
                $member = $organization->members()->find($memberData['id']);
                $member?->update($memberData);
            } else {
                // 新規：既存のsuffixを避けて採番
                $existingSuffixes = $organization->members()
                    ->pluck('member_number')
                    ->map(fn($n) => str_replace($organization->code . '_', '', $n))
                    ->toArray();

                $suffix = collect($suffixes)->first(fn($s) => !in_array($s, $existingSuffixes));
                $memberData['member_number'] = $organization->code . '_' . $suffix;

                $member = $organization->members()->create($memberData);

                User::create([
                    'tenant_id' => 1,
                    'member_id' => $member->id,
                    'username'  => $memberData['member_number'],
                    'name'      => $memberData['last_name'] . ' ' . $memberData['first_name'],
                    'email'     => $memberData['email'],
                    'password'  => bcrypt('12345678'),
                    'status'    => 1,
                ]);
            }

            foreach ($addresses as $address) {
                $member->addresses()->updateOrCreate(
                    ['type' => $address['type']],
                    $address
                );
            }
        }     
    }
    // ──────────────────────────────────────────
    // Private: 詳細用フォーマット
    // ──────────────────────────────────────────

    private function formatOrganization(Organization $organization): array
    {
        $locationAddress = $organization->addresses->firstWhere('type', OrganizationAddress::TYPE_LOCATION);
        $shippingAddress = $organization->addresses->firstWhere('type', OrganizationAddress::TYPE_SHIPPING);
        $billingAddress  = $organization->addresses->firstWhere('type', OrganizationAddress::TYPE_BILLING);

        return [
            'id'               => $organization->id,
            'contract_no'      => $organization->contract_no,
            'name'             => $organization->name,
            'abbr'             => $organization->abbr,
            'url'              => $organization->url,
            'rep_position'     => $organization->rep_position,
            'rep_last_name'    => $organization->rep_last_name,
            'rep_first_name'   => $organization->rep_first_name,
 
            'contract_status'  => $organization->contract_status,
            'contract_date'    => $organization->contract_date?->format('Y-m-d'),
            'status_label'     => $organization->contract_status_label,
            'billable'         => $organization->billable,
            'location_address' => $locationAddress,
            'shipping_address' => $shippingAddress,
            'billing_address'  => $billingAddress,
            'members'          => $organization->members->map(fn($m) => [
                'id'           => $m->id,
                'full_name'    => $m->full_name,
                'position'     => $m->position,
                'email'        => $m->email,
                'tel'          => $m->tel,
                'status_label' => $m->status_label,
            ]),
            'created_at' => $organization->created_at->format('Y-m-d'),
        ];
    }
    // ──────────────────────────────────────────
    // 料金情報取得（請求書作成Dialog用）
    // ──────────────────────────────────────────
 
    public function fee(Organization $organization)
    {
        $organization->loadMissing('locationAddress');
        $feeMaster   = $this->getFeeMaster($organization);
        $memberCount = $organization->members()->count();
 
        $base     = $feeMaster->corporate_fee;
        $extra    = max(0, $memberCount - 3) * $feeMaster->personal_fee;
        $subtotal = $base + $extra;
        $tax      = (int) round($subtotal * 0.1);
        $total    = $subtotal + $tax;
 
        return response()->json([
            'organization_id' => $organization->id,
            'email'           => $organization->locationAddress?->email,
            'member_count'    => $memberCount,
            'corporate_fee'   => $feeMaster->corporate_fee,
            'personal_fee'    => $feeMaster->personal_fee,
            'base'            => $base,
            'extra'           => $extra,
            'subtotal'        => $subtotal,
            'tax'             => $tax,
            'total'           => $total,
        ]);
    }
 
## 3. Private セクションに追加（validateOrganization()の前あたり）
 
    private function getFeeMaster(Organization $organization)
    {
        $contract = $organization->contracts()
            ->where('started_at', '<=', today())
            ->where(function ($query) {
                $query->whereNull('ended_at')
                      ->orWhere('ended_at', '>=', today());
            })
            ->latest('started_at')
            ->first();
 
        return $contract ?? LicenseFeeMaster::where('started_at', '<=', today())
            ->orderByDesc('started_at')
            ->first() ?? (object)[
                'corporate_fee' => 120000,
                'personal_fee'  => 10000,
            ];
    }
}