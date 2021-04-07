<?php

namespace App\Http\Middleware\SeatReservation;

use App\Http\AuthenticatedRequest;
use App\Permissions;
use Closure;
use Illuminate\Http\JsonResponse;

class SeatReservationAdministrationPermissionMiddleware {
  /**
   * @param AuthenticatedRequest $request
   * @param Closure $next
   * @return JsonResponse
   */
  public function handle(AuthenticatedRequest $request, Closure $next): JsonResponse {
    $user = $request->auth;
    if (! $user->hasPermission(Permissions::$SEAT_RESERVATION_ADMINISTRATION)) {
      return response()->json([
        'msg' => 'Permission denied',
        'error_code' => 'permissions_denied',
        'needed_permissions' => [
          Permissions::$ROOT_ADMINISTRATION,
          Permissions::$SEAT_RESERVATION_ADMINISTRATION, ], ], 403);
    }

    return $next($request);
  }
}
