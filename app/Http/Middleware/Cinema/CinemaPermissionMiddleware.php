<?php

namespace App\Http\Middleware\Cinema;

use App\Permissions;
use Closure;
use Illuminate\Http\Request;

class CinemaPermissionMiddleware {
  /**
   * @param Request $request
   * @param Closure $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next) {
    $user = $request->auth;
    if (! ($user->hasPermission(Permissions::$CINEMA_ADMINISTRATION) or $user->hasPermission(Permissions::$ROOT_ADMINISTRATION))) {
      return response()->json([
        'msg' => 'Permission denied',
        'error_code' => 'permissions_denied',
        'needed_permissions' => [
          Permissions::$ROOT_ADMINISTRATION,
          Permissions::$CINEMA_ADMINISTRATION, ], ], 403);
    }

    return $next($request);
  }
}
