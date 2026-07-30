<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LicenseController extends Controller
{
    public function __construct(private FileService $fileService) {}

    // ──────────────────────────────────────────
    // ライセンス証ダウンロード画面
    // ──────────────────────────────────────────
    public function index(Request $request)
    {
        $organization = $this->getOrganization($request);

        if (!$organization) {
            return Inertia::render('License/Index', [
                'license' => null,
            ]);
        }

        $latestFile = $this->findLatestLicenseFile($organization->code);

        return Inertia::render('Licenses/Index', [
            'license' => $latestFile ? [
                'file_url'   => $this->fileService->getUrl($latestFile['path']),
                'issued_date'=> $latestFile['date'],
            ] : null,
        ]);
    }

    // ──────────────────────────────────────────
    // organization取得（type=1:病院 / type=2:先生）
    // ──────────────────────────────────────────
    private function getOrganization(Request $request): ?Organization
    {
        $user = $request->user();
        return match ((int) $user->type) {
            1 => $user->organization,
            2 => $user->member?->organization,
            default => null,
        };
    }

    // ──────────────────────────────────────────
    // S3の licenses/ フォルダから、病院コードに一致する最新ファイルを検索
    // ファイル名形式: {code}_{YYYY-MM-DD}.pdf
    // ──────────────────────────────────────────
    private function findLatestLicenseFile(string $code): ?array
    {
        $files = Storage::disk('s3')->files('licenses');

        $matched = [];
        foreach ($files as $path) {
            $filename = basename($path);

            // "{code}_YYYY-MM-DD.pdf" 形式にマッチするか確認
            if (preg_match('/^' . preg_quote($code, '/') . '_(\d{4}-\d{2}-\d{2})\.pdf$/', $filename, $m)) {
                $matched[] = [
                    'path' => $path,
                    'date' => $m[1],
                ];
            }
        }

        if (empty($matched)) {
            return null;
        }

        // 日付文字列でソートし、最新（最大）を取得
        usort($matched, fn($a, $b) => strcmp($b['date'], $a['date']));

        return $matched[0];
    }
}
