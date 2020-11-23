<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Repositories\User\UserToken\IUserTokenRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use stdClass;

class UserTokenController extends Controller {
  protected IUserTokenRepository $userTokenRepository;

  public function __construct(IUserTokenRepository $userTokenRepository) {
    $this->userTokenRepository = $userTokenRepository;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getCalendarToken(Request $request) {
    $user = $request->auth;

    $tokenObject = $this->userTokenRepository->getUserTokenByUserAndPurpose($user, 'calendar');
    if ($tokenObject == null) {
      $randomToken = $this->userTokenRepository->generateUniqueRandomToken(10);

      $tokenObject = $this->userTokenRepository->createUserToken($user, $randomToken, 'calendar');
      if ($tokenObject == null) {
        return response()->json(['msg' => 'Could not save the calendar token', 'error_code' => 'token_not_saved'], 500);
      }

      return response()->json(['msg' => 'Token generated', 'token' => $randomToken], 200);
    } else {
      return response()->json(['msg' => 'Token already generated', 'token' => $tokenObject->token], 200);
    }
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws Exception
   */
  public function resetCalendarToken(Request $request) {
    $user = $request->auth;

    $tokenObject = $this->userTokenRepository->getUserTokenByUserAndPurpose($user, 'calendar');
    if ($tokenObject == null) {
      return response()->json(['msg' => 'There is no token to delete'], 200);
    }
    if ($this->userTokenRepository->deleteUserToken($tokenObject) != null) {
      return response()->json(['msg' => 'Could not delete token'], 500);
    }
    return response()->json(['msg' => 'Deleted token successfully'], 200);
  }


  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getAllSessions(Request $request) {
    $user = $request->auth;

    $sessionsToReturn = [];

    $sessions = $this->userTokenRepository->getUserTokensByUserAndPurposeOrderedByDate($user, 'stayLoggedIn');
    foreach ($sessions as $session) {
      $sessionToReturn = new stdClass();
      $sessionToReturn->id = $session->id;
      $sessionToReturn->information = $session->description;
      $sessionToReturn->last_used = $session->updated_at;

      $sessionsToReturn[] = $sessionToReturn;
    }

    return response()->json(['msg' => 'List of all sessions', 'sessions' => $sessionsToReturn]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function logoutCurrentSession(Request $request) {
    $this->validate($request, ['session_token' => 'required']);

    $user = $request->auth;

    $session = $this->userTokenRepository->getUserTokenByUserAndTokenAndPurpose($user, $request->input('session_token'),
                                                                                'stayLoggedIn');
    if ($session == null) {
      return response()->json(['msg' => 'Session token is incorrect', 'error_code' => 'session_token_incorrect'], 404);
    }

    if ($this->userTokenRepository->deleteUserToken($session) != null) {
      return response()->json(['msg' => 'Could not delete session'], 500);
    }
    return response()->json(['msg' => 'Successfully logged out and deleted session'], 200);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function removeSession(Request $request, int $id) {
    $user = $request->auth;

    $session = $this->userTokenRepository->getUserTokenByIdAndUserAndPurpose($id, $user, 'stayLoggedIn');
    if ($session == null) {
      return response()->json(['msg' => 'Session token does not exist!', 'error_code' => 'session_token_not_found'],
                              404);
    }

    if ($this->userTokenRepository->deleteUserToken($session) != null) {
      return response()->json(['msg' => 'Could not delete session'], 500);
    }

    return response()->json(['msg' => 'Successfully deleted session'], 200);
  }
}
