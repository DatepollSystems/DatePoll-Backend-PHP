<?php

namespace App\Http\Middleware\Services;

use Closure;

class CinemaMiddleware
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
      if(!env('APP_CINEMA_ENABLED', false)) {
        return response()->json(['msg' => 'The cinema feature is disabled on this DatePoll server'], 500);
      }

      return $next($request);
    }
}
