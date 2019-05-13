<?php

namespace App\Http\Controllers;

use App\Models\User\User;
use App\Models\User\UserToken;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
  /**
   * Create a new token.
   *
   * @param $userID
   * @return string
   */
  protected function jwt($userID) {
    $payload = ['iss' => "lumen-jwt", // Issuer of the token
      'sub' => $userID, // Subject of the token
      'iat' => time(), // Time when JWT was issued.
      'exp' => time() + 60 * 60// Expiration time
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
   * @throws ValidationException
   */
  public function signin(Request $request) {
    $this->validate($request, ['email' => 'required|email', 'password' => 'required']);

    $user = User::where('email', $request->input('email'))->first();
    if (!$user) {
      return response()->json(['error' => 'Email or password is wrong'], 400);
    }

    if (Hash::check($request->input('password') . $user->id, $user->password)) {
      if (!$user->activated) {
        return response()->json(['msg' => 'notActivated'], 201);
      }

      if ($user->force_password_change) {
        return response()->json(['msg' => 'changePassword', 201]);
      }

      $sessionInformation = $request->input('sessionInformation');
      $stayLoggedIn = $request->input('stayLoggedIn');
      if($stayLoggedIn != null && $sessionInformation != null) {
        if($stayLoggedIn) {
          $randomToken = '';
          while (true) {
            $randomToken = UserToken::generateRandomString(64);
            if (UserToken::where('token', $randomToken)->first() == null) {
              break;
            }
          }

          $userToken = new UserToken([
            'user_id' => $user->id,
            'token' => $randomToken,
            'purpose' => 'stayLoggedIn',
            'description' => $sessionInformation
          ]);

          if(!$userToken->save()) {
            return response()->json(['An error occurred during session token saving..'], 500);
          }

          return response()->json(['token' => $this->jwt($user->id), 'sessionToken' => $randomToken], 200);
        }
      }

      return response()->json(['token' => $this->jwt($user->id)], 200);
    }

    return response()->json(['error' => 'Email or password is wrong'], 400);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function changePasswordAfterSignin(Request $request) {
    $this->validate($request, ['email' => 'required|email', 'old_password' => 'required', 'new_password' => 'required']);

    $user = User::where('email', $request->input('email'))->first();
    if (!$user) {
      return response()->json(['error' => 'Email or password is wrong'], 400);
    }

    if (Hash::check($request->input('old_password') . $user->id, $user->password)) {
      $user->force_password_change = false;
      $user->password = app('hash')->make($request->input('new_password') . $user->id);
      $user->save();

      return response()->json(['token' => $this->jwt($user->id)], 200);
    }

    return response()->json(['error' => 'Email or password is wrong'], 400);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function refresh(Request $request) {
    $this->validate($request, ['token' => 'required']);

    $payload = JWT::decode($request->input('token'), env('JWT_SECRET'), ['HS256']);

    $payload_array = (array)$payload;
    $userID = $payload_array['sub'];

    return response()->json(['token' => $this->jwt($userID)], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function IamLoggedIn(Request $request) {
    $this->validate($request, ['sessionToken' => 'required', 'sessionInformation' => 'required']);

    $sessionToken = $request->input('sessionToken');

    $userToken = UserToken::where('token', $sessionToken)->where('purpose', 'stayLoggedIn')->first();

    if($userToken == null) {
      return response()->json(['msg' => 'You have been logged out of this session or this session token is incorrect', 'error_code' => 'session_token_incorrect'], 400);
    }

    $userToken->description = $request->input('sessionInformation');
    $userToken->save();
    $userToken->touch();
    return response()->json(['msg' => 'Session token is good', 'token' => $this->jwt($userToken->user_id)], 200);
  }
}
