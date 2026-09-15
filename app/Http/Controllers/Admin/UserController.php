<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Setting;
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
        $query = User::with(['roles', 'member.organization']);
        // テナント絞り込み（Super Admin は全件表示）
        if (! $request->user()->hasRole('super_admin|Admin')) {
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

        // ページあたり件数
        $perPage = intval($request->input('per_page', Setting::get('admin.per_page', 20)));
        $users = $query->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'username'  => $request->input('username'),
                'name'      => $request->input('name'),
                'email'     => $request->input('email'),
                'role'      => $request->input('role'),
                'member_id'  => $request->input('member_id'),
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

        return Inertia::render('Admin/Users/Edit', [
            'user' => null,
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
        $currentUser = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            // 変更点：メール単体のunique判定ではなく、「メール＋会員番号(member_id)」の
            // 組み合わせで重複判定する。同じメールアドレスでも、member_idが違えば
            // 許可する仕様のため。
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->where(
                    fn ($query) => $query->where('member_id', $request->member_id)
                ),
            ],
            'password' => 'required|string|confirmed|min:8',
            // 変更点：'stringt' は 'string' のタイプミス
            'username' => 'required|string|max:20',
            'member_id' => 'required|integer',
        ]);

        // Super Admin は tenant_id を選択可能、tenant_admin は自分の tenant_id に固定
/*
        $tenantId = $currentUser->hasRole('Super Admin')
            ? $request->tenant_id
            : $currentUser->tenant_id;
*/
        $user = User::create([
            // 変更点：$required は未定義変数（$request のタイプミス）だったため、
            // このメソッドは呼ばれると必ず致命的エラーになっていた。
            'username' => $request->username,
            'member_id' => $request->member_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $role = Role::findOrFail($request->role_id);

        // ユーザーに role を付与（assignRole 内で permissions も自動付与）
        $user->assignRole($role);

        return redirect()->route('admin.users.index')->with('success', __('User created successfully.'));
    }


    /**
     * 編集画面
     */
    public function edit(User $user)
    {
        $user->load('member');

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

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'selected_role' => $user->roles->first()?->id,
            'tenants' => $availableTenants,
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
            // 変更点：store()と同様、メール＋会員番号の組み合わせで重複判定する。
            // 自分自身（$user->id）は除外する。
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')
                    ->where(fn ($query) => $query->where('member_id', $request->member_id))
                    ->ignore($user->id),
            ],
            'password' => 'nullable|string|confirmed|min:4',
            'username' => 'required|string|max:20',
            'member_id' => 'required|integer',
        ]);
/*
        $tenantId = $currentUser->hasRole('Super Admin')
            ? $validated['tenant_id']
            : $currentUser->tenant_id;
*/
        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->username;
        $user->member_id = $request->member_id;

        // パスワードが入力されていれば更新
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();        

        return redirect()->route('admin.users.index')->with('success', __('User updated successfully.'));
    }

    /**
     * ユーザー削除
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', __('User has been deleted.'));
    }

    /**
     * 複数削除
     */
    public function bulkDelete(Request $request)
    {
        User::whereIn('id', $request->ids)->delete();
        return redirect()->route('admin.users.index')
            ->with('success', __('Selected users have been deleted.'));
    }
}
