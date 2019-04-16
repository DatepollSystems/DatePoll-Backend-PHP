<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserChangePasswordController extends Controller
{
  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function checkOldPassword(Request $request) {
    $this->validate($request, ['password' => 'required']);

    $user = $request->auth;

    if (Hash::check($request->input('password') . $user->id, $user->password)) {

      return response()->json(['msg' => 'password_correct'], 200);
    }

    return response()->json(['msg' => 'password_incorrect'], 400);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function changePassword(Request $request) {
    $this->validate($request, ['old_password' => 'required', 'new_password' => 'required']);

    $user = $request->auth;

    if (Hash::check($request->input('old_password') . $user->id, $user->password)) {
      $user->password = app('hash')->make($request->input('new_password') . $user->id);
      $user->save();

      return response()->json(['msg' => 'password_changed'], 200);
    }

    return response()->json(['msg' => 'password_incorrect'], 400);
  }
}
