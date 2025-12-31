<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;

class BankCodeService
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = Config::get('services.bankcodejp.base_url', 'https://apis.bankcode-jp.com/v3/freeword');
        $this->apiKey = Config::get('services.bankcodejp.api_key');
    }

    /**
     * 銀行名検索
     */
    public function searchBanks(string $keyword): array
    {
        return Cache::remember(
            'bank_search_' . md5($keyword),
            now()->addHour(),
            function () use ($keyword) {
                $response = Http::withToken($this->apiKey)
                    ->get("{$this->baseUrl}/banks", [
                        'freeword' => $keyword,
                    ]);

                if ($response->failed()) {
                    return [];
                }

                return collect($response->json('data', []))
                    ->map(fn($bank) => [
                        'code' => $bank['bank_code'],
                        'name' => $bank['bank_name'],
                        'kana' => $bank['bank_name_kana'],
                        'is_yucho' => $bank['is_yucho'] ?? ($bank['bank_code'] === '9900'),
                    ])
                    ->values()
                    ->all();
            }
        );
    }

    /**
     * 支店名検索
     */
    public function searchBranches(string $bankCode, string $branchKeyword): array
    {
        return Cache::remember(
            'branch_search_' . md5($bankCode . $branchKeyword),
            now()->addHour(),
            function () use ($bankCode, $branchKeyword) {
                $response = Http::withToken($this->apiKey)
                    ->get("{$this->baseUrl}/banks/{$bankCode}/branches", [
                        'freeword' => $branchKeyword,
                    ]);

                if ($response->failed()) {
                    return [];
                }

                return collect($response->json('data', []))
                    ->map(fn($branch) => [
                        'branch_code' => $branch['branch_code'],
                        'branch_name' => $branch['branch_name'],
                        'branch_name_kana' => $branch['branch_name_kana'],
                    ])
                    ->values()
                    ->all();
            }
        );
    }
}


