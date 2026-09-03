<?php

namespace App\Services;

use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\LicenseMail;

class LicenseService
{
    public function __construct(
        private PdfService $pdfService
    ) {}

    // PDF作成のみ
    public function create($organization, string $displayName = null): string
    {
        // 変更点：ライセンス付与日（license_issued_at）が未設定の組織は、
        // まだ正式に契約が確定していない（sendInvitation・締結完了の
        // どちらも経ていない）とみなし、証書発行自体をブロックする。
        if (!$organization->license_issued_at) {
            throw new \RuntimeException('ライセンス付与日が未設定のため、証書を発行できません。契約手続きが完了しているか確認してください。');
        }

        $contract = $organization->contracts()
            ->whereNotNull('ended_at')
            ->latest()
            ->first();

        if (!$contract) {
            throw new \RuntimeException('有効な契約が見つかりません。');
        }

        $dueDate = Carbon::parse($contract->ended_at)->format('Y年n月j日');

        // 変更点：付与日はcontractsテーブルではなく、organizations.license_issued_at
        // をそのまま使う（新規契約時に一度だけセットされ、再契約では変わらない値）。
        $issuedDate = Carbon::parse($organization->license_issued_at)->format('Y年n月j日');

        return $this->pdfService->createLicensePdf($organization, $dueDate, $displayName, $issuedDate);
    }

    // メール送信のみ
    public function send(Organization $organization, string $email, string $pdfPath): void
    {
        // 署名付きURLからS3キーのみを抽出
        $parsedPath = parse_url($pdfPath, PHP_URL_PATH); // /licenses/OC00003_2026-07-03.pdf
        $relativePath = ltrim($parsedPath, '/'); // licenses/OC00003_2026-07-03.pdf

        \Log::info('relativePath:', ['path' => $relativePath]);

        Mail::to($email)->send(new LicenseMail($organization, $relativePath));
    }
}