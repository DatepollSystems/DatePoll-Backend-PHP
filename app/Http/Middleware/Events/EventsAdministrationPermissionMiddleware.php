<?php

namespace App\Http\Middleware\Events;

use App\Permissions;
use Closure;
use Illuminate\Http\Request;

class EventsAdministrationPermissionMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param Request $request
   * @param Closure $next
   * @return mixed
   */
  public function handle($request, Closure $next) {
    $user = $request->auth;
    if (!($user->hasPermission(Permissions::$EVENTS_ADMINISTRATION) OR $user->hasPermission(Permissions::$ROOT_ADMINISTRATION))) {
      return response()->json(['msg' => 'Permission denied',
                               'needed_permissions' => [
                                 Permissions::$ROOT_ADMINISTRATION,
                                 Permissions::$EVENTS_ADMINISTRATION]], 403);
    }

    return $next($request);
  }
}
