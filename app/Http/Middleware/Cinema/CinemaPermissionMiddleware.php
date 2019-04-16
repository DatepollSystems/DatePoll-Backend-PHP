<?php

namespace App\Http\Middleware\Cinema;

use App\Permissions;
use Closure;
use Illuminate\Http\Request;

class CinemaPermissionMiddleware
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
    if (!($user->hasPermission(Permissions::$CINEMA_MOVIE_ADMINISTRATION) OR $user->hasPermission(Permissions::$ROOT_ADMINISTRATION))) {
      return response()->json(['msg' => 'Permission denied', 'needed_permissions' => 'root.administration or cinema.movie.administration'], 403);
    }

    return $next($request);
  }
}
