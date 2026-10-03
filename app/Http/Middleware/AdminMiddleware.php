<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('api')->user();

        // role_id 1 = Super Admin, 2 = Manager
        if (! $user || ! in_array($user->role_id, [1, 2])) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
