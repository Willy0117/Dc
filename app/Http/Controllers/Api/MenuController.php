<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Tenant;
use App\Helpers\ApiResponse;
use App\Http\Resources\MenuResource;
use Illuminate\Validation\ValidationException;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        // バリデーション
        try {
            $request->validate([
                'tenant_id'  => ['required', 'integer', 'min:1'],
                'page'       => ['integer'],
                'per_page'   => ['integer'],
                'q'          => ['string', 'nullable'],

                // 日付対象カラム
                'date_type'  => ['string', 'nullable', 'in:serving_date,cooking_date'],

                // 並び替え
                'sort'       => ['string', 'nullable'],

                'start_date' => ['date', 'nullable'],
                'end_date'   => ['date', 'nullable', 'after_or_equal:start_date'],
            ], [
                'tenant_id.required' => 'Tenant ID not found',
                'tenant_id.integer'  => 'Tenant ID not found',
                'tenant_id.min'      => 'Tenant ID not found',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::error($e->errors()['tenant_id'] ?? ['Tenant ID not found']);
        }

        // tenant_id が存在するかチェック
        if (!Tenant::where('id', $request->tenant_id)->exists()) {
            return ApiResponse::error(['Tenant ID not found']);
        }

        // クエリ作成
        $query = Menu::where('tenant_id', $request->tenant_id);

        // 名前検索
        if ($request->q) {
            $query->where('name', 'like', "%{$request->q}%");
        }

        // 日付対象カラム
        $dateField = $request->date_type ?? 'serving_date';

        // 日付範囲検索
        if ($request->start_date && $request->end_date) {
            $query->whereBetween($dateField, [$request->start_date, $request->end_date]);
        } elseif ($request->start_date) {
            $query->where($dateField, '>=', $request->start_date);
        } elseif ($request->end_date) {
            $query->where($dateField, '<=', $request->end_date);
        }

        $sorted = false;

        // 明示的 sort がある場合
        if ($request->sort) {
            [$field, $direction] = explode(':', $request->sort);

            if (
                in_array($field, ['serving_date', 'cooking_date', 'name']) &&
                in_array(strtolower($direction), ['asc', 'desc'])
            ) {
                $query->orderBy($field, $direction);
                $sorted = true;
            }
        }

        // ★ デフォルトソート（sort が無い or 不正）
        if (!$sorted) {
            if ($request->date_type === 'cooking_date') {
                // 調理日基準
                $query
                    ->orderBy('cooking_date', 'asc')
                    ->orderBy('serving_date', 'asc')
                    ->orderBy('serving_time', 'asc')
                    ->orderBy('display_order', 'asc');
            } else {
                // 献立日基準（デフォルト）
                $query
                    ->orderBy('serving_date', 'asc')
                    ->orderBy('serving_time', 'asc')
                    ->orderBy('display_order', 'asc');
            }
        }

        // ページネーション or 全件取得
        $perPage = $request->per_page;

        if ($perPage) {
            $menus = $query->paginate($perPage);
            $data = MenuResource::collection($menus);
            $meta = [
                'page' => $menus->currentPage(),
                'per_page' => $menus->perPage(),
                'total' => $menus->total(),
            ];
        } else {
            $menus = $query->get();
            $data = MenuResource::collection($menus);
            $meta = [
                'page' => 1,
                'per_page' => $menus->count(),
                'total' => $menus->count(),
            ];
        }

        return ApiResponse::success($data, $meta);
    }
    /*/ バリデーション（空・0・負数も弾く）
    public function index(Request $request)
    {
        try {
            $request->validate([
                'tenant_id' => ['required', 'integer', 'min:1'],
                'page' => 'integer',
                'per_page' => 'integer',
                'q' => 'string|nullable',
                'sort' => 'string|nullable',
            ], [
                'tenant_id.required' => 'Tenant ID not found',
                'tenant_id.integer' => 'Tenant ID not found',
                'tenant_id.min' => 'Tenant ID not found',
            ]);
        } catch (ValidationException $e) {
            return ApiResponse::error($e->errors()['tenant_id'] ?? ['Tenant ID not found']);
        }

        // DBに存在する tenant_id かチェック
        if (!Tenant::where('id', $request->tenant_id)->exists()) {
            return ApiResponse::error(['Tenant ID not found']);
        }

        // クエリ作成
        $query = Menu::where('tenant_id', $request->tenant_id);

        if ($request->q) {
            $query->where('name', 'like', "%{$request->q}%");
        }

        if ($request->sort) {
            [$field, $direction] = explode(':', $request->sort);
            $query->orderBy($field, $direction);
        } else {
            $query->orderBy('display_order', 'asc');
        }

        $perPage = $request->per_page;

        if ($perPage) {
            // ページネーションあり
            $menus = $query->paginate($perPage);
            $data = MenuResource::collection($menus);
            $meta = [
                'page' => $menus->currentPage(),
                'per_page' => $menus->perPage(),
                'total' => $menus->total(),
            ];
        } else {
            // 全件取得
            $menus = $query->get();
            $data = MenuResource::collection($menus);
            $meta = [
                'page' => 1,
                'per_page' => $menus->count(),
                'total' => $menus->count(),
            ];
        }

        return ApiResponse::success($data, $meta);
    }
    */
}


