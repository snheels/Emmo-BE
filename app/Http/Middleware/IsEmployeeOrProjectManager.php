<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsEmployeeOrProjectManager
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $role = auth()->user()->role;
        
        if (! in_array($role, ['employee', 'project_manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
