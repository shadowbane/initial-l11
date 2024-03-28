<?php

namespace App\Http\Middleware;

use Closure;

class ActivityLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (config('system.base.log.web_access')) {
            if (auth()->user() instanceof \Illuminate\Database\Eloquent\Model) {
                activity('access')
                    ->log('Web / Api Access')
                    ->causedBy(auth()->user());
            } else {
                activity('access')
                    ->log('Web / Api Access');
            }
        }

        return $next($request);
    }
}
