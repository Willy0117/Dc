<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bank;

class BankController extends Controller
{
    /**
     * 銀行検索
     * /banks?keyword=あ
     */
    public function index(Request $request)
    {
        $q = $request->query('q');
        $category = $request->query('category');

        if ($category === 'undefined' || $category === null) {
            $category = null;
        }
        if (!$q && $category != 7) {
            return response()->json([]);
        }

        $kana = mb_convert_kana($q, 'h');

        $query = Bank::query()
            ->where('type', 1); // 本店のみ

        // ★ bank_categories.id → banks.bank_category は id-1
        if (is_numeric($category)) {
            $query->where('bank_category', ((int)$category) - 1);
        }

        return $query
            ->where(function ($q2) use ($q, $kana) {
                $q2->where('name', 'like', "%{$q}%")
                ->orWhere('name_kana', 'like', "%{$kana}%");
            })
            ->orderBy('bank_code')
            ->limit(50)
            ->get([
                'bank_code as id',
                'name as label',
                'bank_code',
                'name',
                'bank_category',
            ]);
    }
    /**
     * 支店検索
     * /branches?bank_code=0005&keyword=し
     */
    public function branches(Request $request)
    {
        $bankCode = $request->query('bank_code');
        $q = $request->query('q');

        if (!$bankCode || !$q) {
            return response()->json([]);
        }

        $kana = mb_convert_kana($q, 'h');

        return Bank::query()
            ->where('type', 2) // 支店
            ->where('bank_code', $bankCode)
            ->where(function ($qq) use ($q, $kana) {
                $qq->where('name', 'like', "%{$q}%")
                ->orWhere('name_kana', 'like', "%{$kana}%");
            })
            ->orderBy('branch_code')
            ->limit(50)
            ->get()
            ->map(fn ($b) => [
                'id' => $b->branch_code,
                'label' => $b->name,
                'branch_code' => $b->branch_code,
            ]);
    }

}


