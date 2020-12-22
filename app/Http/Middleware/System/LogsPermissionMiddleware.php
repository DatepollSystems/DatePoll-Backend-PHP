<?php

namespace App\Http\Middleware\System;

use App\Http\AuthenticatedRequest;
use App\Permissions;
use Closure;
use Illuminate\Http\JsonResponse;

class LogsPermissionMiddleware {
  /**
   * @param AuthenticatedRequest $request
   * @param Closure $next
   * @return JsonResponse
   */
  public function handle(AuthenticatedRequest $request, Closure $next): JsonResponse {
    $user = $request->auth;
    if (! ($user->hasPermission(Permissions::$ROOT_ADMINISTRATION) or $user->hasPermission(Permissions::$SYSTEM_ADMINISTRATION) or $user->hasPermission(Permissions::$SYSTEM_LOGS_ADMINISTRATION))) {
      return response()->json([
        'msg' => 'Permission denied',
        'error_code' => 'permissions_denied',
        'needed_permissions' => [
          Permissions::$ROOT_ADMINISTRATION,
          Permissions::$SYSTEM_ADMINISTRATION,
          Permissions::$SYSTEM_LOGS_ADMINISTRATION, ], ], 403);
    }

    return $next($request);
  }
}
