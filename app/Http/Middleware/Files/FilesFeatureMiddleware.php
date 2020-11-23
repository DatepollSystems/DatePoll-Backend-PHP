<?php

namespace App\Http\Middleware\Files;

use Closure;
use Illuminate\Http\Request;

class FilesFeatureMiddleware {
  /**
   * Handle an incoming request.
   *
   * @param Request $request
   * @param Closure $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next) {
    if (! env('APP_FEATURE_FILES_ENABLED', false)) {
      return response()->json(['msg' => 'The files feature is disabled on this DatePoll server'], 503);
    }

    return $next($request);
  }
}
