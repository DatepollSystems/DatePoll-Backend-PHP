<?php

namespace App\Http\Middleware\Management;

use App\Http\AuthenticatedRequest;
use App\Permissions;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ManagementUsersViewPermissionMiddleware{
  /**
   * Handle an incoming request.
   *
   * @param AuthenticatedRequest $request
   * @param Closure $next
   * @return JsonResponse|Response
   */
  public function handle(AuthenticatedRequest $request, Closure $next): JsonResponse|Response {
    $user = $request->auth;
    if (! $user->hasPermission(Permissions::$MANAGEMENT_USER_VIEW) && !$user->hasPermission(Permissions::$MANAGEMENT_ADMINISTRATION)) {
      return response()->json(['msg' => 'Permission denied',
        'error_code' => 'permissions_denied',
        'needed_permissions' => [
          Permissions::$ROOT_ADMINISTRATION,
          Permissions::$MANAGEMENT_ADMINISTRATION,
          Permissions::$MANAGEMENT_USER_VIEW, ], ], 403);
    }

    return $next($request);
  }
}
