<?php

namespace App\Http\Middleware;

use App\Http\AuthenticatedRequest;
use App\Permissions;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ManagementPermissionMiddleware {
  /**
   * Handle an incoming request.
   *
   * @param AuthenticatedRequest $request
   * @param Closure $next
   * @return JsonResponse|Response
   */
  public function handle(AuthenticatedRequest $request, Closure $next): JsonResponse|Response {
    $user = $request->auth;
    if (! $user->hasPermission(Permissions::$MANAGEMENT_ADMINISTRATION)) {
      return response()->json(['msg' => 'Permission denied',
        'error_code' => 'permissions_denied',
        'needed_permissions' => [
          Permissions::$ROOT_ADMINISTRATION,
          Permissions::$MANAGEMENT_ADMINISTRATION, ], ], 403);
    }

    return $next($request);
  }
}
