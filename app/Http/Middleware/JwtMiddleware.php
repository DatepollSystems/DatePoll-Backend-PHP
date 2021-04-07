<?php

namespace App\Http\Middleware;

use App\Http\AuthenticatedRequest;
use App\Models\User\User;
use Closure;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class JwtMiddleware {
  /**
   * @param Request $request
   * @param Closure $next
   * @param null $guard
   * @return Response|JsonResponse|RedirectResponse
   */
  public function handle(Request $request, Closure $next, $guard = null): Response|JsonResponse|RedirectResponse {
    $token = $request->get('token');

    if (! $token) {
      // Unauthorized response if token not there
      return response()->json(['msg' => 'Token not provided.', 'error_code' => 'token_not_provided'], 401);
    }
    try {
      $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
    } catch (ExpiredException) {
      return response()->json(['msg' => 'Provided token is expired.', 'error_code' => 'token_expired'], 401);
    } catch (Exception) {
      return response()->json(['msg' => 'Your token is incorrect!', 'error_code' => 'token_incorrect'], 401);
    }
    $user = User::find($credentials->sub);
    // Put the user into the request
    /** @noinspection PhpPossiblePolymorphicInvocationInspection */
    $request->auth = $user;

    $authenticatedRequest = new AuthenticatedRequest($request);
    $authenticatedRequest->auth = $user;

    return $next($authenticatedRequest);
  }
}
