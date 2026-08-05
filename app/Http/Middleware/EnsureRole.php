<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        if ($user->isSuperAdmin()) {
            // Super admin with an active congregation session can access congregation routes
            $needsCongregationAccess = !empty(array_intersect(['congregation_admin', 'member'], $roles));
            if ($needsCongregationAccess && session('admin_congregation_id')) {
                return $next($request);
            }

            return redirect()->route('admin.congregations.index');
        }

        abort(403);
    }
}
