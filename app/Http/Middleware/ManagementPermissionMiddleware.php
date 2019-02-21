<?php

namespace App\Http\Middleware;

use App\Permissions;
use Closure;

class ManagementPermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      $user = $request->auth;
      if(!($user->hasPermission(Permissions::$MANAGEMENT_ADMINISTRATION)
        OR $user->hasPermission(Permissions::$ROOT_ADMINISTRATION))) {
        return response()->json(['msg' => 'Permission denied', 'needed_permissions' => 'root.administration or management.administration'], 403);
      }

      return $next($request);
    }
}
