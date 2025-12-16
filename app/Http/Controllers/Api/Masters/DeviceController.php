<?php

namespace App\Http\Controllers\Api\Masters;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Device;
use App\Models\Tenant;
use App\Helpers\ApiResponse;
use App\Http\Resources\DeviceResource;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\ProcessResource;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        // バリデーション（空・0・負数も弾く）
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
        $query = Device::where('tenant_id', $request->tenant_id)->where('disabled', 1); // ここを追加

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
            $devices = $query->paginate($perPage);
            $data = DeviceResource::collection($devices);
            $meta = [
                'page' => $devices->currentPage(),
                'per_page' => $devices->perPage(),
                'total' => $devices->total(),
            ];
        } else {
            // 全件取得
            $devices = $query->get();
            $data = DeviceResource::collection($devices);
            $meta = [
                'page' => 1,
                'per_page' => $devices->count(),
                'total' => $devices->count(),
            ];
        }

        return ApiResponse::success($data, $meta);
    }
}

