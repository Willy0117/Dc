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
        $contract = $organization->contracts()
            ->whereNotNull('ended_at')
            ->latest()
            ->first();

        if (!$contract) {
            throw new \RuntimeException('有効な契約が見つかりません。');
        }

        $dueDate = Carbon::parse($contract->ended_at)->format('Y年n月j日');

        return $this->pdfService->createLicensePdf($organization, $dueDate, $displayName);
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