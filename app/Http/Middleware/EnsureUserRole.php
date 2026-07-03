<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userRole = session('role');

        if (! $userRole) {
            abort(403, 'Unauthorized. No role assigned.');
        }

        if (! empty($roles) && ! in_array($userRole, $roles)) {
            abort(403, 'Unauthorized. You do not have the required role to access this resource.');
        }

        return $next($request);
    }
}
