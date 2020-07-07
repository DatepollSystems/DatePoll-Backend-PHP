<?php

namespace App\Http\Middleware;

use App\Permissions;
use Closure;
use Illuminate\Http\Request;

class SettingsPermissionMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param Request $request
   * @param \Closure $next
   * @return mixed
   */
  public function handle($request, Closure $next) {
    $user = $request->auth;
    if (!($user->hasPermission(Permissions::$SETTINGS_ADMINISTRATION) OR $user->hasPermission(Permissions::$ROOT_ADMINISTRATION))) {
      return response()->json(['msg' => 'Permission denied',
                               'error_code' => 'permissions_denied',
                               'needed_permissions' => [
                                 Permissions::$ROOT_ADMINISTRATION,
                                 Permissions::$SETTINGS_ADMINISTRATION]], 403);
    }

    return $next($request);
  }
}
