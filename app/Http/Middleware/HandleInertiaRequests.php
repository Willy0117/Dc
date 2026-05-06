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
                        'roles' => $request->user()->tenantRoles()->pluck('name')->toArray(),
                        'permissions' => $request->user()->tenantPermissions()->pluck('name')->toArray(),
                    ]
                    : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }

}
