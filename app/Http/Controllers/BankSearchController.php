<?php

namespace App\Http\Controllers;

use App\Services\BankCodeService;
use Illuminate\Http\Request;

class BankSearchController extends Controller
{
    protected BankCodeService $bankCodeService;

    public function __construct(BankCodeService $bankCodeService)
    {
        $this->bankCodeService = $bankCodeService;
    }

    /**
     * 銀行検索 API
     */
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        if (!$keyword) {
            return response()->json(['banks' => []]);
        }

        $banks = $this->bankCodeService->searchBanks($keyword);
        return response()->json(['banks' => $banks]);
    }

    /**
     * 支店検索 API
     */
    public function searchBranches(Request $request)
    {
        $bankCode = $request->input('bank_code');
        $branchKeyword = $request->input('keyword');

        if (!$bankCode || !$branchKeyword) {
            return response()->json(['branches' => []]);
        }

        $branches = $this->bankCodeService->searchBranches($bankCode, $branchKeyword);
        return response()->json(['branches' => $branches]);
    }
}
