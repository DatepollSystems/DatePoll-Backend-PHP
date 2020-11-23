<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailJob;
use App\Logging;
use App\Mail\ForgotPassword;
use App\Models\User\UserCode;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserToken\IUserTokenRepository;
use Firebase\JWT\JWT;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {
  protected IUserRepository $userRepository;
  protected IUserTokenRepository $userTokenRepository;

  public function __construct(IUserRepository $userRepository, IUserTokenRepository $userTokenRepository) {
    $this->userRepository = $userRepository;
    $this->userTokenRepository = $userTokenRepository;
  }

  /**
   * Create a new token.
   *
   * @param int $userID
   * @return string
   */
  protected function jwt(int $userID) {
    $payload = ['iss' => 'lumen-jwt',// Issuer of the token
      'sub' => $userID,// Subject of the token
      'iat' => time(),// Time when JWT was issued.
      'exp' => time() + 60 * 60,// Expiration time
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
    $this->validate($request, [
      'username' => 'required|min:1|max:190',
      'password' => 'required|min:6',
      'session_information' => 'min:1|max:190',
      'stay_logged_in' => 'boolean', ]);

    $user = $this->userRepository->getUserByUsername($request->input('username'));
    if ($user == null) {
      return response()->json(['msg' => 'Username or password is wrong',
        'error_code' => 'username_or_password_incorrect', ], 400);
    }

    if ($this->userRepository->checkPasswordOfUser($user, $request->input('password'))) {
      if (! $user->activated) {
        return response()->json(['msg' => 'Account is not activated', 'error_code' => 'not_activated'], 400);
      }

      if ($user->force_password_change) {
        return response()->json(['msg' => 'You have to change your password', 'error_code' => 'change_password'], 400);
      }

      $sessionInformation = $request->input('session_information');
      $stayLoggedIn = $request->input('stay_logged_in');
      if ($stayLoggedIn != null && $sessionInformation != null) {
        if ((bool)$stayLoggedIn) {
          $randomToken = $this->userTokenRepository->generateUniqueRandomToken(64);

          $userToken = $this->userTokenRepository->createUserToken(
            $user,
            $randomToken,
            'stayLoggedIn',
            $sessionInformation
          );

          if ($userToken == null) {
            Logging::error('signin', 'User - ' . $user->id . ' | Could not save user token');

            return response()->json(['msg' => 'An error occurred during session token saving..'], 500);
          }

          Logging::info('signin', 'User - ' . $user->id . ' | logged in; Session token: true');

          return response()->json(['token' => $this->jwt($user->id), 'session_token' => $randomToken], 200);
        }
      }

      Logging::info('signin', 'User - ' . $user->id . ' | logged in; Session token: false');

      return response()->json(['token' => $this->jwt($user->id)], 200);
    }

    return response()->json(['msg' => 'Username or password is wrong',
      'error_code' => 'username_or_password_incorrect', ], 400);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function changePasswordAfterSignin(Request $request) {
    $this->validate($request, [
      'username' => 'required|min:1|max:190',
      'old_password' => 'required',
      'new_password' => 'required|min:6',
      'session_information' => 'min:1|max:190',
      'stay_logged_in' => 'boolean', ]);

    $user = $this->userRepository->getUserByUsername($request->input('username'));
    if ($user == null) {
      return response()->json(['msg' => 'Username or password is wrong',
        'error_code' => 'username_or_password_incorrect', ], 400);
    }

    if ($this->userRepository->checkPasswordOfUser($user, $request->input('old_password'))) {
      if (! $user->activated) {
        return response()->json(['msg' => 'Account is not activated', 'error_code' => 'not_activated'], 400);
      }

      if (! $user->force_password_change) {
        return response()->json(['msg' => 'User does not need to change his password',
          'error_code' => 'no_password_change_needed_for_user', ], 400);
      }

      $user->force_password_change = false;
      if (! $this->userRepository->changePasswordOfUser($user, $request->input('new_password'))) {
        Logging::error('changePasswordAfterSignin', 'Could not save new password');

        return response()->json(['msg' => 'Could not save password'], 500);
      }

      $sessionInformation = $request->input('session_information');
      $stayLoggedIn = $request->input('stay_logged_in');
      if ($stayLoggedIn != null && $sessionInformation != null) {
        if ((bool)$stayLoggedIn) {
          $randomToken = $this->userTokenRepository->generateUniqueRandomToken(64);

          $userToken = $this->userTokenRepository->createUserToken(
            $user,
            $randomToken,
            'stayLoggedIn',
            $sessionInformation
          );

          if ($userToken == null) {
            Logging::error('changePasswordAfterSignin', 'User - ' . $user->id . ' | Could not save user token');

            return response()->json(['msg' => 'An error occurred during session token saving..'], 500);
          }

          Logging::info(
            'changePasswordAfterSignin',
            'User - ' . $user->id . ' | Changed password after sign in; Session token: true'
          );

          return response()->json(['token' => $this->jwt($user->id), 'session_token' => $randomToken], 200);
        }
      }

      Logging::info(
        'changePasswordAfterSignin',
        'User - ' . $user->id . ' | Changed password after sign in; Session token: false'
      );

      return response()->json(['token' => $this->jwt($user->id)], 200);
    }

    return response()->json(['msg' => 'Username or password is wrong',
      'error_code' => 'username_or_password_incorrect', ], 400);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function IamLoggedIn(Request $request) {
    $this->validate($request, [
      'session_token' => 'required',
      'session_information' => 'required|min:1|max:190', ]);

    $sessionToken = $request->input('session_token');

    $userToken = $this->userTokenRepository->getUserTokenByTokenAndPurpose($sessionToken, 'stayLoggedIn');

    if ($userToken == null) {
      return response()->json(['msg' => 'You have been logged out of this session or this session token is incorrect',
        'error_code' => 'session_token_incorrect', ], 400);
    }

    $userToken->description = $request->input('session_information');
    $userToken->save();
    $userToken->touch();

    Logging::info(
      'IamLoggedIn',
      'User - ' . $userToken->user_id . ' | User token - ' . $userToken->id . ' | Got new JWT with session token'
    );

    return response()->json(['msg' => 'Session token is good', 'token' => $this->jwt($userToken->user_id)], 202);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function sendForgotPasswordEmail(Request $request) {
    $this->validate($request, [
      'username' => 'required|min:1|max:190', ]);

    $username = $request->input('username');

    $user = $this->userRepository->getUserByUsername($username);
    if ($user == null) {
      return response()->json(['msg' => 'Unknown username', 'error_code' => 'unknown_username'], 404);
    }

    if (! $user->hasEmailAddresses()) {
      return response()->json(['msg' => 'There are no email addresses for this account',
        'error_code' => 'no_email_addresses', ], 400);
    }

    $code = UserCode::generateCode();
    $userCode = new UserCode(['code' => $code, 'purpose' => 'forgotPassword', 'user_id' => $user->id]);

    if ($userCode->save()) {
      dispatch(new SendEmailJob(
        new ForgotPassword($user->firstname, $code),
        $user->getEmailAddresses()
      ))->onQueue('high');

      return response()->json(['msg' => 'Sent'], 200);
    }

    Logging::error('sendForgotPasswordEmail', 'User - ' . $user->id . ' | Could not send email');

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
      'username' => 'required|min:1|max:190', ]);

    $username = $request->input('username');

    $user = $this->userRepository->getUserByUsername($username);
    if ($user == null) {
      return response()->json(['msg' => 'Unknown username', 'error_code' => 'unknown_username'], 404);
    }

    $userCode = UserCode::where('purpose', 'forgotPassword')->where('user_id', $user->id)->orderBy(
      'created_at',
      'desc'
    )->first();
    if ($userCode == null) {
      return response()->json(['msg' => 'There was no code for a password reset requested'], 400);
    }

    if ($userCode->rate_limit >= 11) {
      return response()->json(['msg' => 'Rate limit exceeded', 'error_code' => 'rate_limit_exceeded'], 400);
    }

    $code = $request->input('code');

    if ($userCode->code == $code) {
      Logging::info('checkForgotPasswordCode', 'User - ' . $user->id . ' | Code correct');

      return response()->json(['msg' => 'Code correct', 'success_code' => 'code_correct'], 200);
    } else {
      $userCode->rate_limit++;
      if (! $userCode->save()) {
        Logging::error(
          'checkForgotPasswordCode',
          'User - ' . $user->id . ' | User code ' . $userCode->id . ' | Could not save after rate limit adding'
        );

        return response()->json(['msg' => 'Could not save user code after rate limit adding'], 500);
      }

      return response()->json(['msg' => 'The code is incorrect', 'error_code' => 'code_incorrect'], 400);
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
      'username' => 'required|min:1|max:190',
      'new_password' => 'required|min:6', ]);

    $username = $request->input('username');

    $user = $this->userRepository->getUserByUsername($username);
    if ($user == null) {
      return response()->json(['msg' => 'Unknown username', 'error_code' => 'unknown_username'], 404);
    }

    $userCode = UserCode::where('purpose', 'forgotPassword')->where('user_id', $user->id)->orderBy(
      'created_at',
      'desc'
    )->first();
    if ($userCode == null) {
      return response()->json(['msg' => 'There was no code for a password reset requested'], 400);
    }

    if ($userCode->rate_limit >= 11) {
      return response()->json(['msg' => 'Rate limit exceeded', 'error_code' => 'rate_limit_exceeded'], 400);
    }

    $code = $request->input('code');

    if ($userCode->code == $code) {
      $user->password = app('hash')->make($request->input('new_password') . $user->id);
      if (! $user->save()) {
        return response()->json(['msg' => 'Could not save user'], 500);
      }

      DB::table('user_codes')
        ->where('purpose', '=', 'forgotPassword')
        ->where('user_id', '=', $user->id)->delete();

      Logging::info('resetPasswordAfterForgotPassword', 'User - ' . $user->id . ' | Changed password');

      return response()->json(['msg' => 'Changed password successful'], 200);
    } else {
      $userCode->rate_limit++;
      if (! $userCode->save()) {
        Logging::error(
          'resetPasswordAfterForgotPassword',
          'User - ' . $user->id . ' | User code ' . $userCode->id . ' | Could not save user code after '
        );

        return response()->json(['msg' => 'Could not save user code after rate limit adding'], 500);
      }

      return response()->json(['msg' => 'The code is incorrect', 'error_code' => 'code_incorrect'], 400);
    }
  }
}
