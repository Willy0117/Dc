<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Tenant;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Role::with('permissions');

        if (! $user->hasRole('super_admin')) {
            $query->where('tenant_id', $user->tenant_id);
        }

        // 検索
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // ソート・ページング（既存のフィルタ名に合わせる）
        $sortField = $request->get('sort', 'id');
        $sortOrder = $request->get('order', 'desc');

        $roles = $query->orderBy($sortField, $sortOrder)
                       ->paginate($request->get('per_page', 10))
                       ->withQueryString();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search', 'per_page', 'sort', 'order']),
        ]);
    }

    public function create()
    {
        $user = Auth::user();

        $tenants = auth('admin')->user()->tenant_id === null
            ? Tenant::all()
            : [];

        $permissions = $user->hasRole('super_admin')
            ? Permission::all()
            : Permission::where('tenant_id', $user->tenant_id)->orWhereNull('tenant_id')->get();

        // super_admin がテナントを選べるよう tenants は必要なら追加して渡してください（既にある構成に合わせて）
        return Inertia::render('Admin/Roles/Edit', [
            'role' => null,
            'permissions' => $permissions,
            'user' => $user,
            'tenants' => $tenants,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $tenantId = $user->hasRole('super_admin')
            ? ($request->tenant_id ?? null)
            : $user->tenant_id;

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,NULL,id,tenant_id,' . ($tenantId ?? 'NULL'),
            'permissions' => 'array',
            'guard_name' => 'required|string',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'tenant_id' => $tenantId,
            'guard_name' => $request->guard_name,
        ]);

        if ($request->filled('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return redirect()->route('admin.roles.index')->with('success', __('Role created successfully.'));
    }

    public function edit(Role $role)
    {
        $user = Auth::user();

        $tenants = auth('admin')->user()->tenant_id === null
            ? Tenant::all()
            : [];


        $permissions = ($user->hasRole('super_admin')
            ? Permission::all()
            : Permission::where('tenant_id', $user->tenant_id)
                ->orWhereNull('tenant_id')->get()
        )->map(function ($perm) {
            $tenantName = $perm->tenant_id ? Tenant::find($perm->tenant_id)?->name : null;
            return [
                'id' => $perm->id,
                'name' => $perm->name,
                'guard_name' => $perm->guard_name,
                'tenant_id' => $perm->tenant_id,
                'tenant_label' => $tenantName ? '(' . $tenantName . ')' : '(Global)',
            ];
        });

        $role->load('permissions');

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role,
            'permissions' => $permissions,
            'user' => $user,
            'tenants' => $tenants,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $user = Auth::user();

        // Tenant Admin は自テナント Role のみ編集可能
        if (! $user->hasRole('super_admin') && $role->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        // バリデーション
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id . ',id,tenant_id,' . ($role->tenant_id ?? 'NULL'),
            'permissions' => 'array',
        ]);

        // Role 名更新
        $role->update([
            'name' => $request->name,
        ]);

        // Tenant Admin は自テナント Permission のみ同期
        $permissions = $request->permissions ?? [];
        if (! $user->hasRole('super_admin')) {
            $permissions = Permission::whereIn('id', $permissions)
                                    ->where('tenant_id', $user->tenant_id)
                                    ->pluck('id')
                                    ->toArray();
        }

        $role->syncPermissions($permissions);

        return redirect()->route('admin.roles.index')->with('success', __('Role updated successfully.'));
    }

    public function destroy(Role $role)
    {
        $user = Auth::user();

        if (! $user->hasRole('super_admin') && $role->tenant_id !== $user->tenant_id) {
            abort(403);
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', __('Role deleted successfully.'));
    }

    public function bulkDelete(Request $request)
    {
        $roleIds = $request->input('ids', []);

        $roles = Role::whereIn('id', $roleIds);

        if (!Auth::user()->hasRole('super_admin')) {
            $roles->where('tenant_id', Auth::user()->tenant_id);
        }

        $roles->delete();

        return redirect()->route('admin.roles.index')
                         ->with('success', __('Selected roles deleted successfully.'));
    }
}
