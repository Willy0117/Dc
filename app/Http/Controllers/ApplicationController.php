<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LicenseFeeMaster;
use App\Models\Member;
use App\Models\Organization;
use App\Models\OrganizationAddress;
use App\Models\User;
use App\Services\CloudSignService;
use App\Services\InvoiceService;
use App\Services\StripeService;
use App\Services\PdfService;
use App\Services\FileService;

use App\Exceptions\CloudSignException;
use App\Exceptions\InvoiceException;
use App\Exceptions\StripePaymentException;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Inertia\Inertia;


class ApplicationController extends Controller
{
    // ──────────────────────────────────────────
    // Step 1: 登録フォーム
    // ──────────────────────────────────────────
    public function register(Request $request)
    {
        $token = $request->query('token');
        $needsAgreement = $request->boolean('agreement');

        if (!$token) {
            abort(403);
        }

        $organization = Organization::with([
                'locationAddress',
                'shippingAddress',
                'billingAddress',
                'members',
            ])
            ->where('register_token', $token)
            ->firstOrFail();

        $loc  = $organization->locationAddress;
        $ship = $organization->shippingAddress;
        $bill = $organization->billingAddress;

        $licenses = $organization->members->map(fn($m) => [
            'position'       => $m->position,
            'last_name'      => $m->last_name,
            'first_name'     => $m->first_name,
            'doctor_number'  => $m->doctor_number,
        ])->toArray();

        // 料金マスタ取得
        $feeMaster = $this->getFeeMaster($organization);
        
        if (session('application.token') !== $token) {
            session()->forget('application');
        }

        $data = session('application') ?? [
            'organization_id'    => $organization->id,
            'corp_name'          => $organization->name,
            'clinic_name'        => $organization->abbr,
            'postal_code'        => $loc?->postal_code,
            'address1'           => $loc?->address1,
            'address2'           => $loc?->address2,
            'address3'           => $loc?->address3,
            'tel'                => $loc?->tel,
            'email'              => $loc?->email,
            'payment_method'     => $organization->payment_method ?? 2,
            'rep_position'   => $organization->rep_position  ?? $licenses[0]['position']  ?? '',
            'rep_last_name'  => $organization->rep_last_name  ?? $licenses[0]['last_name']  ?? '',
            'rep_first_name' => $organization->rep_first_name ?? $licenses[0]['first_name'] ?? '',
            'contact_postal_code'=> $ship?->postal_code,
            'contact_address1'   => $ship?->address1,
            'contact_address2'   => $ship?->address2,
            'contact_address3'   => $ship?->address3,
            'contact_tel'        => $ship?->tel,
            'contact_email'      => $bill?->email ?? $ship?->email ?? $loc?->email,
            'licenses'           => $licenses,
            'corporate_fee'      => $feeMaster->corporate_fee,
            'personal_fee'       => $feeMaster->personal_fee,
            'token'              => $token,  
            'needs_agreement'    => $needsAgreement,
        ];

        return Inertia::render('Applications/Register', [
            'data'  => $data,
        ]);
    }

    public function registerStore(Request $request)
    {
        $request->validate([
            'corp_name'           => 'required|string|max:255',
            'clinic_name'         => 'required|string|max:255',
            'postal_code'         => 'required|string|max:20',
            'address1'            => 'required|string|max:255',
            'address2'            => 'required|string|max:255',
            'address3'            => 'nullable|string|max:255',
            'tel'                 => 'required|string|max:30',
            'email'               => 'required|email|max:255',
            'payment_method'      => 'required|integer',
            'same_as_clinic'      => 'boolean',
            'contact_name'        => 'nullable|string|max:255',
            'contact_postal_code' => 'nullable|string|max:20',
            'contact_address1'    => 'nullable|string|max:255',
            'contact_address2'    => 'nullable|string|max:255',
            'contact_address3'    => 'nullable|string|max:255',
            'contact_tel'         => 'nullable|string|max:30',
            'contact_email'       => 'nullable|email|max:255',
            'rep_position'        => 'required|string|max:50',
            'rep_last_name'       => 'required|string|max:100',
            'rep_first_name'      => 'required|string|max:100',
            'licenses'            => 'required|array|min:1',
            'licenses.*.last_name'  => 'required|string|max:100',
            'licenses.*.first_name' => 'required|string|max:100',
            'licenses.*.position'   => 'nullable|string|max:50',
            'licenses.*.doctor_number'  => 'nullable|digits:6',
            'corporate_fee'         => 'required|integer',
            'personal_fee'          => 'required|integer',
            'subtotal'              => 'required|integer',
            'tax'                   => 'required|integer',
            'total'                 => 'required|integer',
        ]);

        $validated = $request->all();

        session(['application' => $validated]);

        return redirect()->route('applications.confirm');
    }

    // ──────────────────────────────────────────
    // Step 2: 確認画面
    // ──────────────────────────────────────────

    public function confirm()
    {
        $data = session('application');

        if (!$data) {
            return redirect()->route('applications.register');
        }

        return Inertia::render('Applications/Confirm', [
            'data' => $data,
        ]);
    }

    // ──────────────────────────────────────────
    // Step 3: 契約書確認
    // ──────────────────────────────────────────
 
    public function contract(Request $request)
    {
        $data = session('application');
 
        if (!$data) {
            return redirect()->route('applications.register', ['token' => $data['token'] ?? '']);
        }

        $organization = Organization::findOrFail($data['organization_id']);
        $pdfPath = app(PdfService::class)->createContractPdf($organization, $data);
        $data['pdf_path']           = $pdfPath;
        $data['pdf_thumbnail_path'] = null;
        // 合意書PDF生成（再契約の場合のみ）
        if (!empty($data['needs_agreement'])) {
            $agreementPath = app(PdfService::class)->createAgreementPdf($organization, $data);
            $data['agreement_pdf_path'] = $agreementPath;
        }

        session(['application' => $data]);

        return Inertia::render('Applications/Contract', [
            'data'    => $data,
            'pdf_url' => app(FileService::class)->getUrl($data['pdf_path']),
//            'agreement_pdf_url' => Storage::url($data['agreement_pdf_path']),
            'agreement_pdf_url' => !empty($data['needs_agreement'])
               ? app(FileService::class)->getUrl($data['agreement_pdf_path'])
               : null,
        ]);
    }
 
    // ──────────────────────────────────────────
    // Step 4: 署名・保存
    // ──────────────────────────────────────────
    
    public function sign(Request $request)
    {
        $data = session('application');

        if (!$data) {
            return redirect()->route('applications.register');
        }

        try {
            DB::transaction(function () use ($data) {
                // 1. Organization 更新
                $organization = Organization::findOrFail($data['organization_id']);
                $organization->update([
                    'name'            => $data['corp_name'],
                    'abbr'            => $data['clinic_name'],
                    'contract_status' => 1,
                    'payment_method'  => $data['payment_method'],
                    'rep_position'    => $data['rep_position'],
                    'rep_last_name'   => $data['rep_last_name'],
                    'rep_first_name'  => $data['rep_first_name'],
                ]);

                // 2. 所在地住所 更新
                OrganizationAddress::updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'type'            => OrganizationAddress::TYPE_LOCATION,
                    ],
                    [
                        'postal_code' => $data['postal_code'],
                        'address1'    => $data['address1'],
                        'address2'    => $data['address2'],
                        'address3'    => $data['address3'] ?? null,
                        'tel'         => $data['tel'],
                        'email'       => $data['email'],
                    ]
                );

                // 3. 契約窓口住所 更新
                if (!($data['same_as_clinic'] ?? false)) {
                    OrganizationAddress::updateOrCreate(
                        [
                            'organization_id' => $organization->id,
                            'type'            => OrganizationAddress::TYPE_SHIPPING,
                        ],
                        [
                            'name'        => $data['contact_name']        ?? null,
                            'postal_code' => $data['contact_postal_code'] ?? null,
                            'address1'    => $data['contact_address1']    ?? null,
                            'address2'    => $data['contact_address2']    ?? null,
                            'address3'    => $data['contact_address3']    ?? null,
                            'tel'         => $data['contact_tel']         ?? null,
                            'email'       => $data['contact_email']       ?? null,
                        ]
                    );
                }

                // 4. ライセンス対象者を members に更新・追加
                foreach ($data['licenses'] as $index => $license) {
                    $email = $index === 0 ? $data['email'] : null;

                    $member = Member::where('organization_id', $organization->id)
                        ->where('last_name', $license['last_name'])
                        ->where('first_name', $license['first_name'])
                        ->first();

                    if ($member) {
                        // 既存: Memberは常に更新
                        $member->update([
                            'position'  => $license['position'] ?? null,
                            'email'     => $email,
                            'status_id' => 1,
                            'doctor_number'  => $license['doctor_number'] ?: $member->doctor_number,
                        ]);

                        // emailがある場合のみ、対応するUserも追随して更新
                        if (!empty($email)) {
                            $user = User::where('member_id', $member->id)->first();
                            if ($user) {
                                $user->update([
                                    'name'  => $license['last_name'] . ' ' . $license['first_name'],
                                    'email' => $email,
                                ]);
                            }
                        }
                    } else {
                        // 新規: Memberは常に作成
                        $member = Member::create([
                            'organization_id' => $organization->id,
                            'last_name'       => $license['last_name'],
                            'first_name'      => $license['first_name'],
                            'position'        => $license['position'] ?? null,
                            'email'           => $email,
                            'status_id'       => 1,
                            'member_number'   => $this->nextMemberNumber($organization),
                            'doctor_number'   => $license['doctor_number'] ?? null,
                        ]);

                        // emailがある場合のみUserも作成(お知らせできないユーザーは作らない)
                        if (!empty($email)) {
                            User::create([
                                'tenant_id' => 1,
                                'member_id' => $member->id,
                                'type'      => 2,
                                'username'  => $member->member_number,
                                'name'      => $license['last_name'] . ' ' . $license['first_name'],
                                'email'     => $email,
                                'password'  => Hash::make(Str::random(32)),
                                'status'    => 1,
                            ]);
                        }
                    }
                }

                // 4.5. 病院(organization)側MyPageユーザー更新(パスワードは変更しない)
                $user = User::where('organization_id', $organization->id)->firstOrFail();
                $user->update([
                    'name'  => trim($data['rep_last_name'] . ' ' . $data['rep_first_name']),
                    'email' => $data['email'],
                ]);

                // 5. DB登録・クラウドサイン送信(PDF生成済み)
                app(CloudSignService::class)->send($organization, $data);

                // 6. 請求処理
                if ((int)$data['payment_method'] === 1) {
                    app(InvoiceService::class)->createAndSend($organization, $data);
                } else {
                    app(StripeService::class)->createAndSend($organization, $data);
                }
            });
        } catch (CloudSignException $e) {
            \Log::error('契約書送信（クラウドサイン）でエラーが発生しました', [
                'organization_id' => $data['organization_id'] ?? null,
                'message'         => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => '契約書の送信に失敗しました（電子署名サービスとの通信エラー）。時間をおいて再度お試しいただくか、運営事務局までお問い合わせください。',
            ]);
        } catch (InvoiceException $e) {
            \Log::error('請求書作成処理でエラーが発生しました', [
                'organization_id' => $data['organization_id'] ?? null,
                'message'         => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => '請求書の作成に失敗しました。契約書の送信は完了していない可能性があります。運営事務局までお問い合わせください。',
            ]);
        } catch (StripePaymentException $e) {
            \Log::error('Stripe決済リンク作成処理でエラーが発生しました', [
                'organization_id' => $data['organization_id'] ?? null,
                'message'         => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => '決済リンクの作成に失敗しました。契約書の送信は完了していない可能性があります。運営事務局までお問い合わせください。',
            ]);
        } catch (\Throwable $e) {
            \Log::error('契約締結処理で予期しないエラーが発生しました', [
                'organization_id' => $data['organization_id'] ?? null,
                'message'         => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => '契約手続きの処理に失敗しました。時間をおいて再度お試しいただくか、運営事務局までお問い合わせください。',
            ]);
        }

        session()->forget('application');

        return redirect()->route('applications.complete');
    }
    
    // ──────────────────────────────────────────
    // Private: member_number 採番(病院code + a,b,c...)
    // ──────────────────────────────────────────
    
    private function nextMemberNumber(Organization $organization): string
    {
        $suffixes = range('a', 'z');
    
        $existingSuffixes = $organization->members()
            ->pluck('member_number')
            ->map(fn($n) => str_replace($organization->code . '_', '', $n))
            ->toArray();
    
        $suffix = collect($suffixes)->first(fn($s) => !in_array($s, $existingSuffixes));
    
        return $organization->code . '_' . $suffix;
    }
 
    // ──────────────────────────────────────────
    // 完了画面
    // ──────────────────────────────────────────
 
    public function complete()
    {
        return Inertia::render('Applications/Complete', [
            'companyName' => config('mail.from.name'),
        ]);
    }
 
    // ──────────────────────────────────────────
    // Stripe決済完了画面
    // ──────────────────────────────────────────
  
    public function stripeComplete()
    {
        return Inertia::render('Applications/StripeComplete', [
            'companyName' => config('mail.from.name'),
        ]);
    }
    // ──────────────────────────────────────────
    // 料金マスタ取得
    // ──────────────────────────────────────────

    private function getFeeMaster(Organization $organization)
    {
        // 個別契約料金を優先
        $contract = $organization->contracts()
            ->where('started_at', '<=', today())
            ->where(function ($query) {
                $query->whereNull('ended_at')
                      ->orWhere('ended_at', '>=', today());
            })
            ->latest('started_at')
            ->first();

        // なければマスタから取得、それもなければデフォルト値
        return $contract ?? LicenseFeeMaster::where('started_at', '<=', today())
            ->orderByDesc('started_at')
            ->first() ?? (object)[
                'corporate_fee' => 120000,
                'personal_fee'  => 10000,
            ];
    }
}