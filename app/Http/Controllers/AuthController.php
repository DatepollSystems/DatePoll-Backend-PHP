<?php

namespace App\Http\Controllers;

use Validator;
use App\User;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
  /**
   * Create a new token.
   *
   * @param $userID
   * @return string
   */
  protected function jwt($userID)
  {
    $payload = [
      'iss' => "lumen-jwt", // Issuer of the token
      'sub' => $userID, // Subject of the token
      'iat' => time(), // Time when JWT was issued.
      'exp' => time() + 60 * 60 // Expiration time
    ];

    // As you can see we are passing `JWT_SECRET` as the second parameter that will
    // be used to decode the token in the future.
    return JWT::encode($payload, env('JWT_SECRET'));
  }

  /**
   * Authenticate a user and return the token if the provided credentials are correct.
   *
   * @param Request $request
   * @return mixed
   * @throws \Illuminate\Validation\ValidationException
   */
  public function signin(Request $request)
  {
    $this->validate($request, [
      'email' => 'required|email',
      'password' => 'required'
    ]);

    $user = User::where('email', $request->input('email'))->first();
    if (!$user) {
      return response()->json([
        'error' => 'Email or password is wrong'
      ], 400);
    }

    if (Hash::check($request->input('password'), $user->password)) {
      if($user->force_password_change) {
        return response()->json(['msg' => 'changePassword', 200]);
      }

      return response()->json([
        'token' => $this->jwt($user->id)
      ], 200);
    }

    return response()->json([
      'error' => 'Email or password is wrong'
    ], 400);
  }

  /**
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function changePasswortAfterSignin(Request $request) {
    $this->validate($request, [
      'email' => 'required|email',
      'old_password' => 'required',
      'new_password' => 'required'
    ]);

    $user = User::where('email', $request->input('email'))->first();
    if (!$user) {
      return response()->json([
        'error' => 'Email or password is wrong'
      ], 400);
    }

    if (Hash::check($request->input('old_password'), $user->password)) {
      $user->force_password_change = false;
      $user->password = app('hash')->make($request->input('new_password'));
      $user->save();

      return response()->json([
        'token' => $this->jwt($user->id)
      ], 200);
    }

    return response()->json([
      'error' => 'Email or password is wrong'
    ], 400);
  }

  public function refresh(Request $request) {
    $this->validate($request, [
      'token' => 'required'
    ]);

    $payload = JWT::decode($request->input('token'), env('JWT_SECRET'), ['HS256']);

    $payload_array = (array) $payload;
    $userID = $payload_array['sub'];

    return response()->json([
      'token' => $this->jwt($userID)
    ], 200);
  }
}
