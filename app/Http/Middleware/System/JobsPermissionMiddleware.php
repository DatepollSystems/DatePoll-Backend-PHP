<?php

namespace App\Http\Middleware\System;

use App\Permissions;
use Closure;
use Illuminate\Http\Request;

class JobsPermissionMiddleware
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
    if (!($user->hasPermission(Permissions::$ROOT_ADMINISTRATION) OR $user->hasPermission(Permissions::$SYSTEM_ADMINISTRATION) OR $user->hasPermission(Permissions::$SYSTEM_JOBS_ADMINISTRATION))) {
      return response()->json([
        'msg' => 'Permission denied',
        'error_code' => 'permissions_denied',
        'needed_permissions' => [
          Permissions::$ROOT_ADMINISTRATION,
          Permissions::$SYSTEM_ADMINISTRATION,
          Permissions::$SYSTEM_JOBS_ADMINISTRATION]], 403);
    }

    return $next($request);
  }
}
