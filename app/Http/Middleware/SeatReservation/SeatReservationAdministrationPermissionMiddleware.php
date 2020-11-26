<?php

namespace App\Http\Middleware\SeatReservation;

use App\Http\AuthenticatedRequest;
use App\Permissions;
use Closure;

class SeatReservationAdministrationPermissionMiddleware {
  /**
   * @param AuthenticatedRequest $request
   * @param Closure $next
   * @return mixed
   */
  public function handle(AuthenticatedRequest $request, Closure $next) {
    $user = $request->auth;
    if (! ($user->hasPermission(Permissions::$SEAT_RESERVATION_ADMINISTRATION) or $user->hasPermission(Permissions::$ROOT_ADMINISTRATION))) {
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
