<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\LicenseFeeMaster;
use App\Models\Member;
use App\Models\Organization;
use App\Models\OrganizationAddress;
use App\Services\CloudSignService;
use App\Services\InvoiceService;
use App\Services\StripeService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            'position'   => $m->position,
            'last_name'  => $m->last_name,
            'first_name' => $m->first_name,
        ])->toArray();

        // 料金マスタ取得
        $feeMaster = $this->getFeeMaster($organization);

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
            'pdf_url' => Storage::url($data['pdf_path']),
            'agreement_pdf_url' => !empty($data['needs_agreement'])
               ? Storage::url($data['agreement_pdf_path'])
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
 
        DB::transaction(function () use ($data) {
            // 1. Organization 更新
            $organization = Organization::findOrFail($data['organization_id']);
            $organization->update([
                'name'            => $data['corp_name'],
                'abbr'            => $data['clinic_name'],
                'contract_status' => 0,
                'payment_method'  => $data['payment_method'],
                'rep_position'    => $data['rep_position'],   // 追加
                'rep_last_name'   => $data['rep_last_name'],  // 追加
                'rep_first_name'  => $data['rep_first_name']
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
                Member::updateOrCreate(
                    [
                        'organization_id' => $organization->id,
                        'last_name'       => $license['last_name'],
                        'first_name'      => $license['first_name'],
                    ],
                    [
                        'position'  => $license['position'] ?? null,
                        'email'     => $index === 0 ? $data['email'] : null,
                        'status_id' => 1,
                    ]
                );
            }
 
            // 5. DB登録・クラウドサイン送信（PDF生成済み）
            app(CloudSignService::class)->send($organization, $data);
 
            // 6. 請求処理
            if ((int)$data['payment_method'] === 1) {
                app(InvoiceService::class)->createAndSend($organization, $data);
            } else {
                app(StripeService::class)->createAndSend($organization, $data);
            }
        });
 
        session()->forget('application');
 
        return redirect()->route('applications.complete');
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