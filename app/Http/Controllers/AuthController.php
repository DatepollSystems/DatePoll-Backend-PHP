<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPassword;
use App\Models\User\User;
use App\Models\User\UserToken;
use App\Models\UserCode;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

    return response()->json(['token' => $this->jwt($userID), 'msg' => 'Refresh successful'], 202);
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
    return response()->json(['msg' => 'Session token is good', 'token' => $this->jwt($userToken->user_id)], 202);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function sendForgotPasswordEmail(Request $request) {
    $this->validate($request, [
      'emailAddress' => 'required|email|max:190'
    ]);

    $emailAddress = $request->input('emailAddress');

    $user = User::where('email', $emailAddress)->first();
    if($user == null) {
      return response()->json(['msg' => 'Unknown email address', 'code' => 'unknown_email'], 404);
    }

    $code = UserCode::generateCode();
    $userCode = new UserCode(["code" => $code, "purpose" => "forgotPassword", 'user_id' => $user->id]);

    if ($userCode->save()) {
      $name = $user->firstname . ' ' . $user->surname;

      Mail::to($emailAddress)->send(new ForgotPassword($name, $code));

      return response()->json(['msg' => 'Sent'], 200);
    }

    return response()->json(['msg' => 'An error occurred during user_code saving'], 500);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function checkForgotPasswordCode(Request $request) {
    $this->validate($request, [
      'code' => 'required|digits:6',
      'emailAddress' => 'required|email|max:190'
    ]);

    $emailAddress = $request->input('emailAddress');

    $user = User::where('email', $emailAddress)->first();
    if($user == null) {
      return response()->json(['msg' => 'Unknown email address', 'code' => 'unknown_email'], 404);
    }

    $userCode = UserCode::where('purpose', 'forgotPassword')->where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
    if($userCode == null) {
      return response()->json(['msg' => 'There is no code for forgotPassword'], 400);
    }

    if ($userCode->rate_limit >= 11) {
      return response()->json(['msg' => 'Rate limit exceeded', 'code' => 'rate_limit_exceeded'], 400);
    }

    $code = $request->input('code');

    if ($userCode->code == $code) {
      return response()->json(['msg' => 'Code correct', 'code' => 'code_correct'], 200);
    } else {
      $userCode->rate_limit++;
      if (!$userCode->save()) {
        return response()->json(['msg' => 'Could not save user code after rate limit adding'], 500);
      }

      return response()->json(['msg' => 'The code is incorrect', 'code' => 'code_incorrect'], 400);
    }
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function resetPasswordAfterForgotPassword(Request $request) {
    $this->validate($request, [
      'code' => 'required|digits:6',
      'emailAddress' => 'required|email|max:190',
      'new_password' => 'required'
    ]);

    $emailAddress = $request->input('emailAddress');

    $user = User::where('email', $emailAddress)->first();
    if($user == null) {
      return response()->json(['msg' => 'Unknown email address', 'code' => 'unknown_email'], 404);
    }

    $userCode = UserCode::where('purpose', 'forgotPassword')->where('user_id', $user->id)->orderBy('created_at', 'desc')->first();
    if($userCode == null) {
      return response()->json(['msg' => 'There is no code for forgotPassword'], 400);
    }

    if ($userCode->rate_limit >= 11) {
      return response()->json(['msg' => 'Rate limit exceeded', 'code' => 'rate_limit_exceeded'], 400);
    }

    $code = $request->input('code');

    if ($userCode->code == $code) {
      $user->password = app('hash')->make($request->input('new_password') . $user->id);
      if(!$user->save()) {
        return response()->json(['msg' => 'Could not save user'], 500);
      }

      DB::table('user_codes')
        ->where('purpose', '=', 'forgotPassword')
        ->where('user_id', '=', $user->id)->delete();

      return response()->json(['msg' => 'Changed password successful'], 200);

    } else {
      $userCode->rate_limit++;
      if (!$userCode->save()) {
        return response()->json(['msg' => 'Could not save user code after rate limit adding'], 500);
      }

      return response()->json(['msg' => 'The code is incorrect', 'code' => 'code_incorrect'], 400);
    }

  }
}
