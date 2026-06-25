<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Permission;
use App\Models\Tenant;
use App\Models\User; // ← これを追加！

//use Spatie\Permission\Exceptions\PermissionAlreadyExists;

class PermissionController extends Controller
{
    /**
     * 一覧画面
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Permission::query();

        // テナント絞り込み（super_admin は全件表示）
        if (!$user->hasRole('super_admin')) {
            $query->where('tenant_id', $user->tenant_id);
        }

        // 検索
        if ($request->filled('name')) {
            $query->where('name', 'like', "%{$request->name}%");
        }

        // ソート
        $sort = $request->input('sort', 'id');
        $direction = $request->input('direction', 'asc');
        $query->orderBy($sort, $direction);

        // ページネーション
        $permissions = $query->paginate($request->input('per_page', 20))
                             ->withQueryString();
        $tenants = $user->hasRole('super_admin') ? Tenant::all() : [];                     

        return Inertia::render('Admin/Permissions/Index', [
            'permissions' => $permissions,
            'filters' => $request->all(),
            'tenants' => $tenants,
            'user' => $user, // Vue 側で判定に必要
        ]);
    }
    /**
     * 編集画面用
     */
    public function edit(Permission $permission)
    {
        $user = auth()->user()->load('roles');

        $tenants = $user->hasRole('super_admin') ? Tenant::all() : [];
        
        return Inertia::render('Admin/Permissions/Edit', [
            'permission' => $permission,
            'tenants' => $tenants,
            'user' => $user,
        ]);
    }

    /**
     * 新規作成画面用
     */
    public function create()
    {
        $user = auth()->user()->load('roles');

        $tenants = $user->hasRole('super_admin') ? Tenant::all() : [];

        return Inertia::render('Admin/Permissions/Edit', [
            'permission' => null,
            'tenants' => $tenants,
            'user' => $user,
            'filters' => request()->all(),
        ]);
    }

    /**
     * 新規作成
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $tenantId = $user->hasRole('super_admin') ? $request->tenant_id : $user->tenant_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'tenant_id' => 'nullable|exists:tenants,id',
            'guard_name' => 'required|string',
        ]);

        // Teams モード対応: tenant_id を考慮して create
        Permission::firstOrCreate(
            [
                'name' => $request->name,
                'guard_name' => $request->guard_name,
                'tenant_id' => $tenantId
            ]
        );

        return redirect()->route('admin.permissions.index', $request->filters ?? []);
    }

    /**
     * 更新
     */
    public function update(Request $request, Permission $permission)
    {
        $user = $request->user();

        $tenantId = $user->hasRole('super_admin') ? $request->tenant_id : $user->tenant_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'tenant_id' => 'nullable|exists:tenants,id',
            'guard_name' => 'required|string',
        ]);

        // Teams モード対応: tenant_id を考慮して更新（同じ tenant 内で name 重複しないように）
        $exists = Permission::where('name', $request->name)
            ->where('guard_name', $request->guard_name)
            ->where('tenant_id', $tenantId)
            ->where('id', '!=', $permission->id)
            ->first();

        if ($exists) {
            return back()->withErrors(['name' => '同じテナント内で既に存在する権限です']);
        }

        $permission->update([
            'name' => $request->name,
            'tenant_id' => $tenantId,
            'guard_name' => $request->guard_name,
        ]);

        return redirect()->route('admin.permissions.index', $request->filters ?? []);
    }

    public function assign(Request $request, Permission $permission)
    {
        $user = $request->user();

        $users = User::query()
            ->where('tenant_id', $user->tenant_id)
            ->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Permissions/Assign', [
            'permission' => $permission,
            'users' => $users,
            'filters' => $request->all(), // 前提条件保持
        ]);
    }

    // 割当保存（POST）
    public function assignStore(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $targetUser = User::findOrFail($validated['user_id']);

        // 権限付与（Permission model インスタンスを渡してOK）
        $targetUser->givePermissionTo($permission);

        return redirect()->route('admin.permissions.index', $request->all())
            ->with('success', __('Permission assigned.'));
    }

    /**
     * 削除
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index');
    }

    /**
     * 複数削除
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        Permission::whereIn('id', $ids)->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.permissions.index');
    }
}




