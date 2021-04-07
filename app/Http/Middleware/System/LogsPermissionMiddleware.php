<?php

namespace App\Http\Middleware\System;

use App\Http\AuthenticatedRequest;
use App\Permissions;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class LogsPermissionMiddleware {
  /**
   * @param AuthenticatedRequest $request
   * @param Closure $next
   * @return JsonResponse|Response|RedirectResponse
   */
  public function handle(AuthenticatedRequest $request, Closure $next): JsonResponse|Response|RedirectResponse {
    $user = $request->auth;
    if (! ($user->hasPermission(Permissions::$SYSTEM_ADMINISTRATION) || $user->hasPermission(Permissions::$SYSTEM_LOGS_ADMINISTRATION))) {
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
