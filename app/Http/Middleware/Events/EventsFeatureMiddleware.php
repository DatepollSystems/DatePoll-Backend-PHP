<?php

namespace App\Http\Middleware\Events;

use Closure;
use Illuminate\Http\Request;

class EventsFeatureMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param Request $request
   * @param Closure $next
   * @return mixed
   */
  public function handle($request, Closure $next) {
    if (!env('APP_FEATURE_EVENTS_ENABLED', false)) {
      return response()->json(['msg' => 'The cinema feature is disabled on this DatePoll server'], 503);
    }

    return $next($request);
  }
}
