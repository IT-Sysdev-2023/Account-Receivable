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
            'flash' => [
                'warning' => fn() => $request->session()->get('warning'),
                'successful' => fn() => $request->session()->get('successful'),
            ],
            'auth.user' => fn() => $request->user()
                ? $request->user()->only('id', 'name', 'role')
                : null,
            'theme' => $request->user()?->theme ?? 'light',
            'auth.permissions' => fn() => $request->user()
                ? $request->user()
                ->permissions
                ->mapWithKeys(function ($perm) {
                    return [
                        $perm->role_id => [
                            'can_view'     => (bool) $perm->can_view,
                            'can_insert'   => (bool) $perm->can_insert,
                            'can_update'   => (bool) $perm->can_update,
                            'can_delete'   => (bool) $perm->can_delete,
                            'can_print'    => (bool) $perm->can_print,
                            'can_tag'      => (bool) $perm->can_tag,
                            'can_reprint'  => (bool) $perm->can_reprint,
                        ]
                    ];
                })
                : [],
        ]);
    }
}
