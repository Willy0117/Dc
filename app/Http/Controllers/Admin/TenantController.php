<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class TenantController extends Controller
{
    // 一覧ページ
    public function index(Request $request)
    {
        $query = Tenant::query();

        // 検索条件
        if ($name = $request->input('name')) {
            $query->where('name', 'like', "%{$name}%");
        }
        if ($email = $request->input('contact_email')) {
            $query->where('contact_email', 'like', "%{$email}%");
        }
        if ($phone = $request->input('contact_phone')) {
            $query->where('contact_phone', 'like', "%{$phone}%");
        }

        // ソート
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        // ページあたり件数
        $perPage = intval($request->input('per_page', 10));

        $tenants = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants,
            'filters' => $request->only(['name','contact_email','contact_phone','per_page','sort_by','sort_dir'])
        ]);
    }

    // Create画面
    public function create(Request $request)
    {
        return Inertia::render('Admin/Tenants/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
        ]);

        // 1️⃣ テナント作成
        $tenant = Tenant::create($validated);

        // 2️⃣ ロール作成（各テナント専用）
        $tenantAdmin = Role::create([
            'name' => 'tenant_admin_' . $tenant->id,
            'tenant_id' => $tenant->id,
            'guard_name' => 'web',
        ]);

        $tenantUser = Role::create([
            'name' => 'tenant_user_' . $tenant->id,
            'tenant_id' => $tenant->id,
            'guard_name' => 'web',
        ]);

        // 3️⃣ 権限構成（確定版）
        $permissions = [
            // 管理設定
            'manage roles' => ['admin'],
            'manage permissions' => ['admin'],
            'manage users' => ['admin'],

            // マスター管理
            'manage masters' => ['admin', 'user'],

            // センサー記録系
            'manage sensor' => ['admin', 'user'],

            // 献立管理
            'manage menus' => ['admin', 'user'],
        ];

        // 4️⃣ Permission登録＆Role割当
        foreach ($permissions as $permissionName => $roles) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'tenant_id' => $tenant->id,
                'guard_name' => 'web',
            ]);

            if (in_array('admin', $roles)) {
                $tenantAdmin->givePermissionTo($permission);
            }
            if (in_array('user', $roles)) {
                $tenantUser->givePermissionTo($permission);
            }
        }

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant created successfully with roles & permissions.');
    }



    // Edit画面
    public function edit(Request $request, Tenant $tenant)
    {
        return Inertia::render('Admin/Tenants/Edit', [
            'tenant' => $tenant
        ]);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
        ]);

        // 1️⃣ Tenant情報更新
        $tenant->update($validated);

        // 2️⃣ 既存ロール取得または作成
        $tenantAdmin = Role::firstOrCreate([
            'name' => 'tenant_admin_' . $tenant->id,
            'tenant_id' => $tenant->id,
            'guard_name' => 'web',
        ]);

        $tenantUser = Role::firstOrCreate([
            'name' => 'tenant_user_' . $tenant->id,
            'tenant_id' => $tenant->id,
            'guard_name' => 'web',
        ]);

        // 3️⃣ 権限構成（storeと同じ）
        $permissions = [
            // 管理設定
            'manage roles' => ['admin'],
            'manage permissions' => ['admin'],
            'manage users' => ['admin'],

            // マスター管理
            'manage masters' => ['admin', 'user'],

            // センサー記録系
            'manage sensor' => ['admin', 'user'],

            // 献立管理
            'manage menus' => ['admin', 'user'],
        ];

        // 4️⃣ Permission登録＆Role割当
        foreach ($permissions as $permissionName => $roles) {
            $permission = Permission::firstOrCreate([
                'name' => $permissionName,
                'tenant_id' => $tenant->id,
                'guard_name' => 'web',
            ]);

            if (in_array('admin', $roles)) {
                $tenantAdmin->givePermissionTo($permission);
            }
            if (in_array('user', $roles)) {
                $tenantUser->givePermissionTo($permission);
            }
        }

        return redirect()->route('admin.tenants.index')
            ->with('success', 'Tenant updated successfully with roles & permissions.');
    }




    // 削除
    public function destroy(Tenant $tenant)
    {
        $tenant->delete();
        return redirect()->route('admin.tenants.index')->with('success', __('Tenant has been deleted.'));
    }

    // 複数削除
    public function bulkDelete(Request $request)
    {
        Tenant::whereIn('id', $request->ids)->delete();
        return redirect()->route('tenants.index')->with('success', __('Selected tenants have been deleted.'));
    }
}
