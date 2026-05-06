<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Admin;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;

class AdminController extends Controller
{
    /**
     * ユーザー一覧
     */
    public function index(Request $request)
    {

        $currentUser = auth('admin')->user();

        $query = Admin::with(['roles']);

        // テナント絞り込み（super_admin / Admin は全件）
        if (! $currentUser->hasRole(['super_admin', 'admin'])) {
            $query->where('tenant_id', $currentUser->tenant_id);
        }

        // 検索条件
        if ($name = $request->input('name')) {
            $query->where('name', 'like', "%{$name}%");
        }

        if ($email = $request->input('email')) {
            $query->where('email', 'like', "%{$email}%");
        }

        if ($role = $request->input('role')) {
            $query->whereHas('roles', function($q) use ($role) {
                $q->where('name', 'like', "%{$role}%");
            });
        }

        // ソート
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = $request->input('sort_dir', 'asc');

        if ($sortBy === 'role') {
            // Role名でソート
            $query->join('model_has_roles', 'admins.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->orderBy('roles.name', $sortDir)
                ->select('admins.*');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        // ページあたり件数
        $perPage = intval($request->input('per_page', Setting::get('admin.per_page', 20)));
        $admins = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Admins/Index', [
            'admins' => $admins,
            'filters' => [
                'name'      => $request->input('name'),
                'email'     => $request->input('email'),
                'role'      => $request->input('role'),
                'per_page'  => $perPage, // ← ここが重要！
                'sort_by'   => $request->input('sort_by'),
                'sort_dir'  => $request->input('sort_dir'),
            ],
        ]);
    }


    /**
     * ユーザー作成画面
     */
    public function create(Request $request)
    {
        $currentUser = auth('admin')->user();

        $roles = $currentUser->hasRole('super_admin')
            ? Role::all()
            : Role::where('tenant_id', $currentUser->tenant_id)->get();

        $tenants = Tenant::all()->keyBy('id');

        $roles = $roles->map(function($role) use ($tenants) {
            $role->tenant_name = $role->tenant_id
                ? ($tenants[$role->tenant_id]->name ?? '(Global)')
                : '(Global)';
            return $role;
        });

        $availableTenants = $currentUser->hasRole('super_admin')
            ? Tenant::all()
            : [];

        return Inertia::render('Admin/Admins/Edit', [
            'admin' => null,
            'roles' => $roles,
            'selected_role' => null,
            'tenants' => $availableTenants,
        ]);
    }

    /**
     * 保存処理
     */
    public function store(Request $request)
    {
        $currentUser = auth('admin')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins,email',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $role = Role::findOrFail($request->role_id);
        $admin->assignRole($role);

        return redirect()->route('admin.admins.index')
            ->with('success', __('Admin created successfully.'));
    }


    /**
     * 編集画面
     */
    public function edit(Admin $admin)
    {
        $currentUser = auth('admin')->user();

        $rolesQuery = Role::where('guard_name', 'admin');

        $roles = $currentUser->hasRole('super_admin', 'admin')
            ? $rolesQuery->get()
            : $rolesQuery->where('tenant_id', $currentUser->tenant_id)->get();

        $tenants = Tenant::all()->keyBy('id');

        $roles = $roles->map(function ($role) use ($tenants) {
            $role->tenant_name = $role->tenant_id
                ? ($tenants->get($role->tenant_id)?->name ?? '(Global)')
                : '(Global)';
            return $role;
        });

        $availableTenants = $currentUser->hasRole('super_admin', 'admin')
            ? Tenant::all()
            : [];

        return Inertia::render('Admin/Admins/Edit', [
            'admin' => $admin,
            'roles' => $roles,
            'selected_role' => $admin->roles->first()?->id,
            'tenants' => $availableTenants,
        ]);
    }

    /**
     * 更新処理
     */
    public function update(Request $request, Admin $admin)
    {
        $currentUser = auth('admin')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|string|email|max:255|unique:admins,email,{$admin->id}",
            'password' => 'nullable|string|confirmed|min:4',
            'tenant_id' => 'nullable|exists:tenants,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $admin->name = $validated['name'];
        $admin->email = $validated['email'];
        $admin->tenant_id = $validated['tenant_id'] ?? null;

        if ($request->filled('password')) {
            $admin->password = Hash::make($validated['password']);
        }

        $admin->save();

        // role更新（これが重要）
        $admin->syncRoles([$validated['role_id']]);

        return redirect()->route('admin.admins.index')
            ->with('success', __('Admin updated successfully.'));
    }

    /**
     * ユーザー削除
     */
    public function destroy(Admin $admin)
    {
        $admin->delete();
        return redirect()->route('admin.admins.index')
            ->with('success', __('Admin has been deleted.'));
    }

    /**
     * 複数削除
     */
    public function bulkDelete(Request $request)
    {
        Admin::whereIn('id', $request->ids)->delete();
        return redirect()->route('admin.admins.index')
            ->with('success', __('Selected admins have been deleted.'));
    }
}
