<?php

namespace App\Http\Middleware\System;

use App\Permissions;
use Closure;
use Illuminate\Http\Request;

class LogsPermissionMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param Request $request
   * @param Closure $next
   * @return mixed
   */
  public function handle($request, Closure $next) {
    $user = $request->auth;
    if (!($user->hasPermission(Permissions::$SYSTEM_LOGS) OR $user->hasPermission(Permissions::$ROOT_ADMINISTRATION))) {
      return response()->json(['msg' => 'Permission denied', 'needed_permissions' => 'root.administration or system.logs.*'], 403);
    }

    return $next($request);
  }
}
