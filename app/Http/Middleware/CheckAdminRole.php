<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdminRole
{
    public function handle(Request $request, Closure $next)
    {
        // Only super_admin (role_id = 1) and manager (role_id = 2) can create accounts
        $allowedRoles = [1, 2];

        if (!in_array($request->user()->role_id, $allowedRoles)) {
            return response()->json([
                'error' => 'Unauthorized - Only admins can create accounts'
            ], 403);
        }

        return $next($request);
    }
}
