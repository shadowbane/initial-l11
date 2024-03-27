<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Impersonate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->guest()) {
            return $next($request);
        }

        if ($request->session()->has('impersonate') && auth()->user()->hasRole('System Administrators')) {
            auth()->onceUsingId($request->session()->get('impersonate'));
        }

        return $next($request);
    }
}
