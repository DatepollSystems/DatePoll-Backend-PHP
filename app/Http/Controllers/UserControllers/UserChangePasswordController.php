<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Repositories\User\User\IUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserChangePasswordController extends Controller
{

  protected $userRepository;

  public function __construct(IUserRepository $userRepository) {
    $this->userRepository = $userRepository;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function checkOldPassword(Request $request) {
    $this->validate($request, ['password' => 'required|min:6']);

    $user = $request->auth;

    if ($this->userRepository->checkPasswordOfUser($user, $request->input('password'))) {
      return response()->json(['msg' => 'Password correct'], 200);
    }

    return response()->json(['msg' => 'Password incorrect', 'error_code' => 'password_incorrect'], 400);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function changePassword(Request $request) {
    $this->validate($request, ['old_password' => 'required', 'new_password|min:6' => 'required']);

    $user = $request->auth;

    if ($this->userRepository->checkPasswordOfUser($user, $request->input('old_password'))) {
      if (!$this->userRepository->changePasswordOfUser($user, $request->input('new_password'))) {
        return response()->json(['msg' => 'Could not save user'], 500);
      }

      return response()->json(['msg' => 'Password changed'], 200);
    }

    return response()->json(['msg' => 'Password incorrect', 'error_code' => 'password_incorrect'], 400);
  }
}
