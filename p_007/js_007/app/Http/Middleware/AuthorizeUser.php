<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeUser
{
    public function handle(Request $request, Closure $next, $role = ''): Response
    {
        $user = $request->user();

        if ($user->hasRole($role)) {
            return $next($request);
        }

        abort(403, 'Forbidden. You are not authorized to access this page.');
    }
}
