<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use App\Models\Tenant;
use App\Models\User; 

class SensorController extends Controller
{
    // 一覧ページ
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Sensor::query();

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
        if ($model = $request->input('model')) {
            $query->where('model', 'like', "%{$model}%");
        }
        if ($serialNumber = $request->input('serial_number')) {
            $query->where('serial_number', 'like', "%{$serialNumber}%");
        }

        // ソート
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        // ページあたり件数
        $perPage = intval($request->input('per_page', 20));

        $tenants = $user->hasRole('Super Admin') ? Tenant::all() : [];                     

        $sensors = Sensor::query()
            ->when($request->code, fn($q,$v)=>$q->where('code','like',"%$v%"))
            ->when($request->name, fn($q,$v)=>$q->where('name','like',"%$v%"))
            ->when($request->model, fn($q,$v)=>$q->where('model','like',"%$v%"))
            ->when($request->serial_number, fn($q,$v)=>$q->where('serial_number','like',"%$v%"))
            ->orderBy($request->sort_by ?? 'id', $request->sort_dir ?? 'asc')
            ->paginate($perPage)
            ->withQueryString(); // 検索条件をページリンクに保持

        return Inertia::render('Sensors/Index', [
            'sensors' => $sensors,
            'tenants' => $tenants,
            'user' => $user, // Vue 側で判定に必要
            'filters' => $request->only(['code','name','model','serial_number','per_page','sort_by','sort_dir']),
        ]);
    }

    // Create 画面
    public function create(Request $request)
    {
        $sensor = null;

        $user = auth()->user()->load('roles');

        $tenants = $user->hasRole('Super Admin') ? Tenant::all() : [];

        // コピー用モードの場合
        if ($request->input('mode') === 'copy' && $sensor_id = $request->input('sensor_id')) {
            $sensor = Sensor::find($sensor_id);
        }

        return Inertia::render('Sensors/Create', [
            'filters' => $request->only(['code','name','model','serial_number','per_page','sort_by','sort_dir','page']),
            'sensor' => $sensor, // コピー元のデータを渡す
            'tenants' => $tenants,
            'user' => $user,
        ]);
         
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'code' => ['nullable', 'string'],
            'serial_number' => ['required', 'string', Rule::unique('sensors')],
            'name' => ['required', 'string'],
            'model' => ['nullable', 'string'],
            'disabled' => ['required', 'boolean'],
            'display_order' => ['required', 'integer'],
            'tenant_id' => ['nullable', 'exists:tenants,id'], 
        ], [
            'serial_number.required' => __('validation.required', ['attribute' => __('Serial Number')]),
            'serial_number.unique' => __('validation.unique', ['attribute' => __('Serial Number')]),
            'name.required' => __('validation.required', ['attribute' => __('Name')]),
            'display_order.required' => __('validation.required', ['attribute' => __('Display Order')]),
        ]);
        // tenant_id を設定（Super Admin は選択、Tenant Admin は自動）
        $validated['tenant_id'] = $user->hasRole('Super Admin') 
            ? $validated['tenant_id'] 
            : $user->tenant_id;

        Sensor::create($validated);

        return redirect()->route('sensors.index', $request->only(['code','name','model','serial_number','per_page','sort_by','sort_dir','page']))
            ->with('success', __('Sensor has been created.'));
    }

    public function edit(Request $request, Sensor $sensor)
    {
        $user = $request->user();

        $tenants = $user->hasRole('Super Admin') ? Tenant::all() : [];

        return Inertia::render('Sensors/Edit', [
            'sensor' => $sensor,
            'tenants' => $tenants,
            'user' => $user,
            'filters' => $request->only(['code','name','model','serial_number','per_page','sort_by','sort_dir','page'])
        ]);
    }

    public function update(Request $request, Sensor $sensor)
    {
        $user = $request->user();

        $validated = $request->validate([
            'code' => ['nullable', 'string'],
            'serial_number' => ['required', 'string', Rule::unique('sensors')->ignore($sensor->id)],
            'name' => ['required', 'string'],
            'model' => ['nullable', 'string'],
            'disabled' => ['required', 'boolean'],
            'display_order' => ['required', 'integer'],
            'tenant_id' => ['nullable', 'exists:tenants,id'], // 追加
        ], [
            'serial_number.required' => __('validation.required', ['attribute' => __('Serial Number')]),
            'serial_number.unique' => __('validation.unique', ['attribute' => __('Serial Number')]),
            'name.required' => __('validation.required', ['attribute' => __('Name')]),
            'display_order.required' => __('validation.required', ['attribute' => __('Display Order')]),
        ]);
        // tenant_id を設定（Super Admin は選択、Tenant Admin は自動）
        $validated['tenant_id'] = $user->hasRole('Super Admin') 
            ? $validated['tenant_id'] 
            : $user->tenant_id;

        $sensor->update($validated);

        return redirect()->route('sensors.index', $request->only(['code','name','model','serial_number','per_page','sort_by','sort_dir','page']))
            ->with('success', __('Sensor has been updated.'));
    }

    public function destroy(Sensor $sensor)
    {
        $sensor->delete();
        return redirect()->route('sensors.index')->with('success', __('Sensor has been deleted.'));
    }

    public function bulkDelete(Request $request)
    {
        Sensor::whereIn('id', $request->ids)->delete();
        return redirect()->route('sensors.index')->with('success', __('Selected sensors have been deleted.'));
    }

    public function autocomplete(Request $request)
    {
        $search = $request->input('q');

        $sensors = Sensor::query()
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name', 'desc')
            ->limit(20)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'label' => "{$m->name} ({$m->serial_number})"
            ]);

        return response()->json($sensors);
    }

    // API Real Check
    public function checkCode(Request $request)
    {
        $exists = Sensor::where('code', $request->code)
            ->when($request->id, fn($q) => $q->where('id','!=',$request->id))
            ->exists();

        return response()->json(['exists' => $exists]);
    }
    public function checkSerialNumber(Request $request)
    {
        $exists = Sensor::where('serial_number', $request->serial_number)
            ->when($request->id, fn($q) => $q->where('id', '!=',$request->id))
            ->exists();

        return response()->json(['exists' => $exists]);
    }


}



