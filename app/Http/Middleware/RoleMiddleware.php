<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Debugging: log the role to see if it's passed correctly
        \Log::info('RoleMiddleware invoked with role: '.$role);

        if (! auth()->check()) {
            abort(401);
        }

        if (! auth()->user()->roles()->where('name', $role)->exists()) {
            abort(403);
        }

        return $next($request);
    }
}
