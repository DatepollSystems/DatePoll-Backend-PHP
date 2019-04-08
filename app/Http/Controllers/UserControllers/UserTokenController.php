<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\UserToken;
use Illuminate\Http\Request;

class UserTokenController extends Controller
{

  /**
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function getCalendarToken(Request $request) {
    $user = $request->auth;;

    $tokenObject = UserToken::where('user_id', $user->id)->where('purpose', 'calendar')->first();
    if($tokenObject == null) {
      $randomToken = '';
      while(true) {
        $randomToken = UserToken::generateRandomString(10);
        if(UserToken::where('token', $randomToken)->first() == null) {
          break;
        }
      }

      $tokenObject = new UserToken(['token' => $randomToken,
        'purpose' => 'calendar',
        'user_id' => $user->id]);
      if(!$tokenObject->save()) {
        return response()->json(['msg' => 'Could not save the calendar token', 'error_code' => 'token_not_saved'], 500);
      }

      return response()->json(['msg' => 'Token generated', 'token' => $randomToken], 200);
    } else {
      return response()->json(['msg' => 'Token already generated', 'token' => $tokenObject->token], 200);
    }
  }

  /**
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function resetCalendarToken(Request $request) {
    $user = $request->auth;

    $tokenObject = UserToken::where('user_id', $user->id)->where('purpose', 'calendar')->first();
    if($tokenObject == null) {
      return response()->json(['msg' => 'There is no token to delete'], 200);
    }
    if(!$tokenObject->delete()) {
      return response()->json(['msg' => 'Could not delete token'], 500);
    }
    return response()->json(['msg' => 'Deleted token successfully'], 200);
  }
}
