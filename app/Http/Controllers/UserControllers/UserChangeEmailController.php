<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Mail\NewEmailVerifying;
use App\Mail\OldEmailVerifying;
use App\Models\UserCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class UserChangeEmailController extends Controller
{

  public function oldEmailAddressVerification(Request $request) {
    $user = $request->auth;

    $code = UserCode::generateCode();

    $userCode = new UserCode(["code" => $code, "purpose" => "oldEmailVerify", 'user_id' => $user->id]);

    if ($userCode->save()) {
      $name = $user->firstname . ' ' . $user->surname;

      Mail::to($user->email)->send(new OldEmailVerifying($name, $code));

      return response()->json(['msg' => 'Sent'], 200);
    }

    return response()->json(['msg' => 'An error occurred during user_code saving'], 500);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function oldEmailAddressVerificationCodeVerification(Request $request) {
    $this->validate($request, ['code' => 'required|digits:6']);

    $code = $request->input('code');

    $user = $request->auth;

    $userCode = UserCode::where('purpose', 'oldEmailVerify')->where('user_id', $user->id)->orderBy('created_at', 'desc')->first();

    if ($userCode == null) {
      return response()->json(['msg' => 'could_not_find_user_code'], 404);
    }

    if ($userCode->rate_limit >= 11) {
      return response()->json(['msg' => 'rate_limit_exceeded'], 200);
    }

    if ($userCode->code == $code) {
      return response()->json(['msg' => 'code_correct'], 200);
    } else {
      $userCode->rate_limit++;
      if (!$userCode->save()) {
        return response()->json(['msg' => 'Could not save user code after rate limit adding'], 500);
      }

      return response()->json(['msg' => 'code_incorrect'], 200);
    }
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function newEmailAddressVerification(Request $request) {
    $this->validate($request, ['email' => 'required|email']);

    $email = $request->input('email');

    $user = $request->auth;

    $code = UserCode::generateCode();

    $userCode = new UserCode(["code" => $code, "purpose" => "newEmailVerify", 'user_id' => $user->id]);

    if ($userCode->save()) {
      $name = $user->firstname . ' ' . $user->surname;

      Mail::to($email)->send(new NewEmailVerifying($name, $code));

      return response()->json(['msg' => 'Sent'], 200);
    }

    return response()->json(['msg' => 'An error occurred during user_code saving'], 500);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function newEmailAddressVerificationCodeVerification(Request $request) {
    $this->validate($request, ['code' => 'required|digits:6']);

    $code = $request->input('code');

    $user = $request->auth;

    $userCode = UserCode::where('purpose', 'newEmailVerify')->where('user_id', $user->id)->orderBy('created_at', 'desc')->first();

    if ($userCode == null) {
      return response()->json(['msg' => 'could_not_find_user_code'], 404);
    }

    if ($userCode->rate_limit >= 11) {
      return response()->json(['msg' => 'rate_limit_exceeded'], 200);
    }

    if ($userCode->code == $code) {
      return response()->json(['msg' => 'code_correct'], 200);
    } else {
      $userCode->rate_limit++;
      if (!$userCode->save()) {
        return response()->json(['msg' => 'Could not save user code after rate limit adding'], 500);
      }

      return response()->json(['msg' => 'code_incorrect'], 200);
    }
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function changeEmailAddress(Request $request) {
    $this->validate($request, ['oldEmailCode' => 'required|digits:6', 'newEmailCode' => 'required|digits:6', 'newEmailAddress' => 'required|email|max:190']);

    $oldEmailCode = $request->input('oldEmailCode');
    $newEmailCode = $request->input('newEmailCode');
    $newEmailAddress = $request->input('newEmailAddress');

    $user = $request->auth;

    $oldEmailUserCode = UserCode::where('purpose', 'oldEmailVerify')->where('user_id', $user->id)->orderBy('created_at', 'desc')->first();

    $newEmailUserCode = UserCode::where('purpose', 'newEmailVerify')->where('user_id', $user->id)->orderBy('created_at', 'desc')->first();

    if ($oldEmailUserCode == null OR $newEmailUserCode == null) {
      return response()->json(['msg' => 'could_not_find_user_code'], 404);
    }

    if ($oldEmailUserCode->rate_limit >= 11 OR $newEmailUserCode->rate_limit >= 11) {
      return response()->json(['msg' => 'rate_limit_exceeded'], 200);
    }

    if ($oldEmailUserCode->code == $oldEmailCode AND $newEmailUserCode->code == $newEmailCode) {
      $user->email = $newEmailAddress;
      if ($user->save()) {
        /* Delete all other user_codes with email changing purpose because the process was finished */
        DB::table('user_codes')->where('purpose', '=', 'oldEmailVerify')->where('user_id', '=', $user->id)->delete();

        DB::table('user_codes')->where('purpose', '=', 'newEmailVerify')->where('user_id', '=', $user->id)->delete();

        return response()->json(['msg' => 'email_changed'], 200);
      }

      return response()->json(['msg' => 'Could not save user after email changing'], 500);
    } else {
      $oldEmailUserCode->rate_limit++;
      $newEmailUserCode->rate_limit++;
      if (!$oldEmailUserCode->save()) {
        return response()->json(['msg' => 'Could not save user code after rate limit adding'], 500);
      }
      if (!$newEmailUserCode->save()) {
        return response()->json(['msg' => 'Could not save user code after rate limit adding'], 500);
      }

      return response()->json(['msg' => 'codes_incorrect'], 400);
    }
  }
}
