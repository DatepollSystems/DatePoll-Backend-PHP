<?php

namespace App\Http\Middleware;

use App\Models\User\User;
use Closure;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JwtMiddleware {
  /**
   * @param Request $request
   * @param Closure $next
   * @param null $guard
   * @return JsonResponse|mixed
   */
  public function handle(Request $request, Closure $next, $guard = null) {
    $token = $request->get('token');

    if (! $token) {
      // Unauthorized response if token not there
      return response()->json(['msg' => 'Token not provided.', 'error_code' => 'token_not_provided'], 401);
    }
    try {
      $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
    } catch (ExpiredException $e) {
      return response()->json(['msg' => 'Provided token is expired.', 'error_code' => 'token_expired'], 401);
    } catch (Exception $e) {
      return response()->json(['msg' => 'Your token is incorrect!', 'error_code' => 'token_incorrect'], 401);
    }
    $user = User::find($credentials->sub);
    // Put the user into the request
    $request->auth = $user;

    return $next($request);
  }
}
