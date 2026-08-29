<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'admin' => fn () => auth('admin')->user()
                    ? [
                        'id' => auth('admin')->id(),
                        'name' => auth('admin')->user()->name,
                        'roles' => auth('admin')->user()->tenantRoles()->pluck('name')->toArray(),
                        'permissions' => auth('admin')->user()->tenantPermissions()->pluck('name')->toArray(),
                    ]
                    : null,

                'user' => fn () => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'type' => $request->user()->type, // 追加：1=病院(organization), 2=先生(member)
                        'roles' => $request->user()->tenantRoles()->pluck('name')->toArray(),
                        'permissions' => $request->user()->tenantPermissions()->pluck('name')->toArray(),
                        // 追加（変更点10）：先生ログイン時のみTier情報を含める。
                        // 病院ログイン(type=1)の場合はnullになり、My Page側の
                        // TierProgressコンポーネントは表示されない。
                        'member' => $request->user()->type === 2 && $request->user()->member_id
                            ? (function () use ($request) {
                                $member = \App\Models\Member::with('currentTierHistory')
                                    ->find($request->user()->member_id);

                                if (!$member) {
                                    return null;
                                }

                                return [
                                    'id'                    => $member->id,
                                    'tier'                  => $member->tier,
                                    'tier_label'            => $member->tier_label,
                                    'current_tier_history'  => $member->currentTierHistory,
                                ];
                            })()
                            : null,
                    ]
                    : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'warning' => fn () => $request->session()->get('warning'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'appName' => config('app.name'),
        ]);
    }

}