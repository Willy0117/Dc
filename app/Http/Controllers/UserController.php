<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant;

class UserController extends Controller
{
    /**
     * ユーザー一覧
     */
    public function index(Request $request)
    {
        $query = User::with(['roles','tenant']);

        $tenantId = $request->input('tenant_id');

        if ($request->user()->hasRole('Super Admin')) {
            // Super Admin → tenant_id が指定されていれば絞り込み
            if (!empty($tenantId)) {
                $query->where('tenant_id', $tenantId);
            }
        } else {
            // Super Admin 以外 → 常に自分の tenant_id で固定
            $query->where('tenant_id', $request->user()->tenant_id);
        }

        // 検索条件: 名前、メール、Role名
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
            $query->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->orderBy('roles.name', $sortDir)
                ->select('users.*');
        } else {
            $query->orderBy($sortBy, $sortDir);
        }
        // Super Admin の場合のみテナント一覧を取得
        $tenants = $request->user()->hasRole('Super Admin') 
            ? Tenant::orderBy('name')->get(['id', 'name'])
            : collect(); // その他のユーザーは空配列
        // ページあたり件数
        $perPage = intval($request->input('per_page', 10));
        $users = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => request()->only(['code', 'name', 'email', 'tenant_id', 'per_page', 'sort_by', 'sort_dir', 'page']),
            'tenants' => $tenants,
        ]);
    }


    /**
     * ユーザー作成画面
     */
    public function create(Request $request)
    {
        $currentUser = $request->user();

        $roles = $currentUser->hasRole('Super Admin')
            ? Role::all()
            : Role::where('tenant_id', $currentUser->tenant_id)->get();

        // tenant 名をマッピング
        $tenants = Tenant::all()->keyBy('id');
        $roles = $roles->map(function($role) use ($tenants) {
            $role->tenant_name = $role->tenant_id ? ($tenants[$role->tenant_id]->name ?? '(Global)') : '(Global)';
            return $role;
        });

        $availableTenants = $currentUser->hasRole('Super Admin') ? Tenant::all() : [];

        return Inertia::render('Users/Edit', [
            'user' => null,
            'roles' => $roles,
            'selected_role' => null,
            'tenants' => $availableTenants,
            'filters' => request()->only(['code', 'name', 'email', 'tenant_id', 'per_page', 'sort_by', 'sort_dir', 'page']),
        ]);
    }

    /**
     * 保存処理
     */
    public function store(Request $request)
    {
        $currentUser = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|confirmed|min:8',
            'role_id' => 'required|exists:roles,id',
            'tenant_id' => 'nullable|exists:tenants,id',
        ]);

        // Super Admin は tenant_id を選択可能、tenant_admin は自分の tenant_id に固定
        $tenantId = $currentUser->hasRole('Super Admin')
            ? $request->tenant_id
            : $currentUser->tenant_id;

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'tenant_id' => $tenantId,
            'password' => Hash::make($request->password),
        ]);

        $role = Role::findOrFail($request->role_id);

        // ユーザーに role を付与（assignRole 内で permissions も自動付与）
        $user->assignRole($role);

        return redirect()->route('users.index')->with('success', __('User created successfully.'));
    }


    /**
     * 編集画面
     */
    public function edit(User $user)
    {
        $currentUser = auth()->user();

        $roles = $currentUser->hasRole('Super Admin')
            ? Role::all()
            : Role::where('tenant_id', $currentUser->tenant_id)->get();

        $tenants = Tenant::all()->keyBy('id');
        $roles = $roles->map(function($role) use ($tenants) {
            $role->tenant_name = $role->tenant_id ? ($tenants[$role->tenant_id]->name ?? '(Global)') : '(Global)';
            return $role;
        });

        $availableTenants = $currentUser->hasRole('Super Admin') ? Tenant::all() : [];

        return Inertia::render('Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'selected_role' => $user->roles->first()?->id,
            'tenants' => $availableTenants,
            'filters' => request()->only(['code', 'name', 'email', 'tenant_id', 'per_page', 'sort_by', 'sort_dir', 'page']),
        ]);
    }

    /**
     * 更新処理
     */
    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|string|email|max:255|unique:users,email,{$user->id}",
            'password' => 'nullable|string|confirmed|min:8',
            'role_id' => 'required|exists:roles,id',
            'tenant_id' => 'nullable|exists:tenants,id',
        ]);

        $tenantId = $currentUser->hasRole('Super Admin')
            ? $validated['tenant_id']
            : $currentUser->tenant_id;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->tenant_id = $tenantId;

        // パスワードが入力されていれば更新
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();        
        if ($request->filled('role_id')) {
            $role = Role::findOrFail($request->role_id);
            $user->syncRoles([$role]);
        }

        return redirect()->route('users.index')->with('success', __('User updated successfully.'));
    }

    /**
     * ユーザー削除
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')
            ->with('success', __('User has been deleted.'));
    }

    /**
     * 複数削除
     */
    public function bulkDelete(Request $request)
    {
        User::whereIn('id', $request->ids)->delete();
        return redirect()->route('users.index')
            ->with('success', __('Selected users have been deleted.'));
    }
}
