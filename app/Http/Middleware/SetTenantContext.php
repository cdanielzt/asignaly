<?php

namespace App\Http\Middleware;

use App\Services\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function __construct(private TenantContext $tenant) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            if ($user->isSuperAdmin()) {
                $congregationId = session('admin_congregation_id');
                if ($congregationId) {
                    $this->tenant->set((int) $congregationId);
                }
            } elseif ($user->congregation_id) {
                $this->tenant->set($user->congregation_id);
            }
        }

        return $next($request);
    }
}
