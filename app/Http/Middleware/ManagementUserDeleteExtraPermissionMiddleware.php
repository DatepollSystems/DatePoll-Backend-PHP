<?php

namespace App\Http\Middleware;

use App\Http\AuthenticatedRequest;
use App\Permissions;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ManagementUserDeleteExtraPermissionMiddleware {
  /**
   * Handle an incoming request.
   *
   * @param AuthenticatedRequest $request
   * @param Closure $next
   * @return JsonResponse|Response
   */
  public function handle(AuthenticatedRequest $request, Closure $next): JsonResponse|Response {
    $user = $request->auth;
    if (! $user->hasPermission(Permissions::$MANAGEMENT_EXTRA_USER_DELETE)) {
      return response()->json(['msg' => 'Permission denied',
        'error_code' => 'permissions_denied',
        'needed_permissions' => [
          Permissions::$ROOT_ADMINISTRATION,
          Permissions::$MANAGEMENT_EXTRA_USER_DELETE, ], ], 403);
    }

    return $next($request);
  }
}
