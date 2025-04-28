<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeUser
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user_role = $request->user()->getRole();

        if (in_array($user_role, $roles)) {
            return $next($request);
        }

        abort(403, 'Forbidden. You are not authorized to access this page.');
    }
}
