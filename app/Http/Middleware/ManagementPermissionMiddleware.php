<?php

namespace App\Http\Middleware;

use App\Http\AuthenticatedRequest;
use App\Permissions;
use Closure;

class ManagementPermissionMiddleware {
  /**
   * Handle an incoming request.
   *
   * @param AuthenticatedRequest $request
   * @param Closure $next
   * @return mixed
   */
  public function handle(AuthenticatedRequest $request, Closure $next) {
    $user = $request->auth;
    if (! ($user->hasPermission(Permissions::$MANAGEMENT_ADMINISTRATION) or $user->hasPermission(Permissions::$ROOT_ADMINISTRATION))) {
      return response()->json(['msg' => 'Permission denied',
        'error_code' => 'permissions_denied',
        'needed_permissions' => [
          Permissions::$ROOT_ADMINISTRATION,
          Permissions::$MANAGEMENT_ADMINISTRATION, ], ], 403);
    }

    return $next($request);
  }
}
