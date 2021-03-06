<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Repositories\User\User\IUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserChangePasswordController extends Controller {
  protected IUserRepository $userRepository;

  public function __construct(IUserRepository $userRepository) {
    $this->userRepository = $userRepository;
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function checkOldPassword(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['password' => 'required|min:6']);

    $user = $request->auth;

    if ($this->userRepository->checkPasswordOfUser($user, $request->input('password'))) {
      return response()->json(['msg' => 'Password correct'], 200);
    }

    return response()->json(['msg' => 'Password incorrect', 'error_code' => 'password_incorrect'], 400);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function changePassword(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, ['old_password' => 'required', 'new_password' => 'required|min:6']);

    $user = $request->auth;

    if ($this->userRepository->checkPasswordOfUser($user, $request->input('old_password'))) {
      if (! $this->userRepository->changePasswordOfUser($user, $request->input('new_password'))) {
        return response()->json(['msg' => 'Could not save user'], 500);
      }

      return response()->json(['msg' => 'Password changed'], 200);
    }

    return response()->json(['msg' => 'Password incorrect', 'error_code' => 'password_incorrect'], 400);
  }
}
