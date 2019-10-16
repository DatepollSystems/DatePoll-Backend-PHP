<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\User\UserToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use stdClass;

class UserTokenController extends Controller
{

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getCalendarToken(Request $request) {
    $user = $request->auth;;

    $tokenObject = UserToken::where('user_id', $user->id)->where('purpose', 'calendar')->first();
    if ($tokenObject == null) {
      $randomToken = '';
      while (true) {
        $randomToken = UserToken::generateRandomString(10);
        if (UserToken::where('token', $randomToken)->first() == null) {
          break;
        }
      }

      $tokenObject = new UserToken(['token' => $randomToken, 'purpose' => 'calendar', 'user_id' => $user->id]);
      if (!$tokenObject->save()) {
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
   */
  public function resetCalendarToken(Request $request) {
    $user = $request->auth;

    $tokenObject = UserToken::where('user_id', $user->id)->where('purpose', 'calendar')->first();
    if ($tokenObject == null) {
      return response()->json(['msg' => 'There is no token to delete'], 200);
    }
    if (!$tokenObject->delete()) {
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

    $sessions = UserToken::where('user_id', $user->id)->where('purpose', 'stayLoggedIn')->orderBy('updated_at')->get();
    foreach ($sessions as $session) {
      $sessionToReturn = new stdClass();
      $sessionToReturn->id = $session->id;
      $sessionToReturn->information = $session->description;
      $sessionToReturn->last_used = $session->updated_at;

      $sessionToReturn->delete_session = [
        'href' => 'api/v1/user/myself/session/{id}',
        'method' => 'DELETE'
      ];

      $sessionsToReturn[] = $sessionToReturn;
    }

    return response()->json(['msg' => 'List of all sessions', 'sessions' => $sessionsToReturn]);
  }

  public function logoutCurrentSession(Request $request) {
    $this->validate($request, ['session_token' => 'required']);

    $user = $request->auth;

    $session = UserToken::where('user_id', $user->id)->where('token', $request->input('session_token'))
      ->where('purpose', 'stayLoggedIn')->first();
    if($session == null) {
      return response()->json(['msg' => 'Session token is incorrect'], 404);
    }

    if(!$session->delete()) {
      return response()->json(['msg' => 'Could not delete session'], 500);
    }
    return response()->json(['msg' => 'Successfully logged out and deleted session'], 200);
  }

  public function removeSession(Request $request, $id) {
    $user = $request->auth;

    $session = UserToken::where('purpose', 'stayLoggedIn')->where('user_id', $user->id)->where('id', $id);
    if($session == null) {
      return response()->json(['msg' => 'Session token does not exist!'], 404);
    }

    if(!$session->delete()) {
      return response()->json(['msg' => 'Could not delete session'], 500);
    }

    return response()->json(['msg' => 'Successfully deleted session'], 200);
  }
}
