<?php

namespace App\Http\Middleware\Files;

use App\Permissions;
use Closure;
use Illuminate\Http\Request;

class FilesAdministrationPermissionMiddleware {
  /**
   * @param Request $request
   * @param Closure $next
   * @return mixed
   */
  public function handle(Request $request, Closure $next) {
    $user = $request->auth;
    if (! ($user->hasPermission(Permissions::$FILES_ADMINISTRATION) or $user->hasPermission(Permissions::$ROOT_ADMINISTRATION))) {
      return response()->json([
        'msg' => 'Permission denied',
        'error_code' => 'permissions_denied',
        'needed_permissions' => [
          Permissions::$ROOT_ADMINISTRATION,
          Permissions::$FILES_ADMINISTRATION, ], ], 403);
    }

    return $next($request);
  }
}
