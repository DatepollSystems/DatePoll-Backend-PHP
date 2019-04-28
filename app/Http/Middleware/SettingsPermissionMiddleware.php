<?php

namespace App\Http\Middleware;

use App\Permissions;
use Closure;

class SettingsPermissionMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param \Illuminate\Http\Request $request
   * @param \Closure $next
   * @return mixed
   */
  public function handle($request, Closure $next) {
    $user = $request->auth;
    if (!($user->hasPermission(Permissions::$SETTINGS_ADMINISTRATION) OR $user->hasPermission(Permissions::$ROOT_ADMINISTRATION))) {
      return response()->json(['msg' => 'Permission denied', 'needed_permissions' => 'root.administration or settings.administration'], 403);
    }

    return $next($request);
  }
}
