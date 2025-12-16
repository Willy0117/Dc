<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Process;

class DeviceController extends Controller
{
    // 一覧ページ
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Device::query();
        // テナント絞り込み（Super Admin は全件表示）
        if (!$user->hasRole('Super Admin')) {
            $query->where('tenant_id', $user->tenant_id);
        }

        // 個別検索
        if ($code = $request->input('code')) {
            $query->where('code', 'like', "%{$code}%");
        }
        if ($name = $request->input('name')) {
            $query->where('name', 'like', "%{$name}%");
        }
        if ($processId = $request->input('process_id')) {
            $query->where('process_id', $processId);
        }
        if ($measurement = $request->input('measurement')) {
            $query->where('measurement', $measurement);
        }

        // ソート
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        // ページあたり件数
        $perPage = intval($request->input('per_page', 10));

        $tenants = $user->hasRole('Super Admin') ? Tenant::all() : [];                     
        $processes = Process::all(['id', 'name']);

        $devices = Device::with('process')
            ->when($request->code, fn($q,$v)=>$q->where('code','like',"%$v%"))
            ->when($request->name, fn($q,$v)=>$q->where('name','like',"%$v%"))
            ->when($request->process_id, fn($q,$v) => $q->where('process_id', $v))
            ->when($request->measurement, fn($q,$v) => $q->where('measurement', $v))
            ->orderBy($request->sort_by ?? 'id', $request->sort_dir ?? 'asc')
            ->paginate($perPage)
            ->withQueryString(); // 検索条件をページリンクに保持


        return Inertia::render('Devices/Index', [
            'devices' => $devices,
            'tenants' => $tenants,
            'user' => $user, 
            'processes' => $processes,
            'filters' => $request->only(['code','name','process_id','measurement','per_page','sort_by','sort_dir']),
        ]);
    }

    // Create 画面
    public function create(Request $request)
    {
        $device = null;
        $user = auth()->user()->load('roles');
        $tenants = $user->hasRole('Super Admin') ? Tenant::all() : [];

        // コピー用モードの場合
        if ($request->input('mode') === 'copy' && $device_id = $request->input('device_id')) {
            $device = Device::find($device_id);
        }
        // process 選択肢を取得
        $processes = Process::all(['id', 'name']);

        return Inertia::render('Devices/Create', [
            'filters' => $request->only(['code','name','process_id','measurement','per_page','sort_by','sort_dir','page']),
            'tenants' => $tenants,
            'user' => $user, // Vue 側で判定に必要
            'device' => $device, // コピー元のデータを渡す
            'processes' => $processes,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'code' => ['required', 'string', Rule::unique('devices')],
            'name' => ['required', 'string'],
            'process_id' => ['required', 'integer', 'exists:processes,id'],
            'measurement' => ['required', 'boolean'],
            'disabled' => ['required', 'boolean'],
            'display_order' => ['required', 'integer'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],             
        ], [
            'code.required' => __('validation.required', ['attribute' => __('Code')]),
            'code.unique' => __('validation.unique', ['attribute' => __('Code')]),
            'name.required' => __('validation.required', ['attribute' => __('Name')]),
            'display_order.required' => __('validation.required', ['attribute' => __('Display Order')]),
        ]);
        // tenant_id を設定（Super Admin は選択、Tenant Admin は自動）
        $validated['tenant_id'] = $user->hasRole('Super Admin') 
            ? $validated['tenant_id'] 
            : $user->tenant_id;

        Device::create($validated);

        return redirect()->route('devices.index', $request->only(['code','name','process_id','measurement','per_page','sort_by','sort_dir','page']))
            ->with('success', __('device has been created.'));
    }

    public function edit(Request $request, Device $device)
    {
        $user = $request->user();
        $tenants = $user->hasRole('Super Admin') ? Tenant::all() : [];
        // process 選択肢を取得
        $processes = Process::all(['id', 'name']);

        return Inertia::render('Devices/Edit', [
            'device' => $device,
            'tenants' => $tenants,
            'user' => $user, // Vue 側で判定に必要
            'processes' => $processes,
            'filters' => $request->only(['code','name','process_id','measurement','per_page','sort_by','sort_dir','page'])
        ]);
    }

    public function update(Request $request, Device $device)
    {
        $user = $request->user();

        $validated = $request->validate([
            'code' => ['required', 'string', Rule::unique('devices')->ignore($device->id)],
            'measurement' => ['required', 'boolean'],
            'name' => ['required', 'string'],
            'process_id' => ['required', 'integer', 'exists:processes,id'],
            'disabled' => ['required', 'boolean'],
            'display_order' => ['required', 'integer'],
            'tenant_id' => ['nullable', 'exists:tenants,id'],             
        ], [
            'code.required' => __('validation.required', ['attribute' => __('Code')]),
            'code.unique' => __('validation.unique', ['attribute' => __('Code')]),
            'name.required' => __('validation.required', ['attribute' => __('Name')]),
            'display_order.required' => __('validation.required', ['attribute' => __('Display Order')]),
        ]);
        // tenant_id を設定（Super Admin は選択、Tenant Admin は自動）
        $validated['tenant_id'] = $user->hasRole('Super Admin') 
            ? $validated['tenant_id'] 
            : $user->tenant_id;

        $device->update($validated);

        return redirect()->route('devices.index', $request->only(['code','name','process_id','measurement','per_page','sort_by','sort_dir','page']))
            ->with('success', __('device has been updated.'));
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('devices.index')->with('success', __('device has been deleted.'));
    }

    public function bulkDelete(Request $request)
    {
        Device::whereIn('id', $request->ids)->delete();
        return redirect()->route('devices.index')->with('success', __('Selected devices have been deleted.'));
    }

    public function autocomplete(Request $request)
    {
        $search = $request->input('q');

        $devices = Device::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'label' => "{$m->name} ({$m->code})"
            ]);

        return response()->json($devices);
    }

    // API Real Check
    public function checkCode(Request $request)
    {
        $exists = Device::where('code', $request->code)
            ->when($request->id, fn($q) => $q->where('id','!=',$request->id))
            ->exists();

        return response()->json(['exists' => $exists]);
    }

}

