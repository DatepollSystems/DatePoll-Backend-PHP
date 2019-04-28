<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use App\Models\User\User;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Illuminate\Http\JsonResponse;

class JwtMiddleware
{
  /**
   * @param $request
   * @param Closure $next
   * @param null $guard
   * @return JsonResponse|mixed
   */
  public function handle($request, Closure $next, $guard = null) {
    $token = $request->get('token');

    if (!$token) {
      // Unauthorized response if token not there
      return response()->json(['error' => 'Token not provided.'], 401);
    }
    try {
      $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
    } catch (ExpiredException $e) {
      return response()->json(['error' => 'Provided token is expired.'], 400);
    } catch (Exception $e) {
      return response()->json(['error' => 'Your token is incorrect!'], 400);
    }
    $user = User::find($credentials->sub);
    // Now let's put the user in the request class so that you can grab it from there
    $request->auth = $user;
    return $next($request);
  }
}
