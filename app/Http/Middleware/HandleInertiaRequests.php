<?php

namespace App\Http\Middleware;

use App\Models\Congregation;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $activeCongregation = null;
        $adminViewingCongregation = false;

        if ($user) {
            if ($user->isSuperAdmin()) {
                $adminCongId = session('admin_congregation_id');
                if ($adminCongId) {
                    $adminViewingCongregation = true;
                    $activeCongregation = Congregation::find($adminCongId)?->only('id', 'name');
                }
            } elseif ($user->congregation) {
                $activeCongregation = $user->congregation->only('id', 'name');
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'role'         => $user->role,
                    'congregation' => $activeCongregation,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'adminViewingCongregation' => $adminViewingCongregation,
            'impersonating' => (bool) session('impersonator_id'),
            'allCongregations' => fn () => $user && $user->isSuperAdmin()
                ? Congregation::orderBy('name')->get(['id', 'name'])
                : null,
        ];
    }
}
