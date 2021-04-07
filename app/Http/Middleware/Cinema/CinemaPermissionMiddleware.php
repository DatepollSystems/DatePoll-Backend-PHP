<?php

namespace App\Http\Middleware\Cinema;

use App\Http\AuthenticatedRequest;
use App\Permissions;
use Closure;
use Illuminate\Http\JsonResponse;

class CinemaPermissionMiddleware {
  /**
   * @param AuthenticatedRequest $request
   * @param Closure $next
   * @return JsonResponse
   */
  public function handle(AuthenticatedRequest $request, Closure $next): JsonResponse {
    $user = $request->auth;
    if (! $user->hasPermission(Permissions::$CINEMA_ADMINISTRATION)) {
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
