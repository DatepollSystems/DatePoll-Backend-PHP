<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Repositories\User\User\IUserRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserChangeEmailController extends Controller {
  protected IUserRepository $userRepository;

  public function __construct(IUserRepository $userRepository) {
    $this->userRepository = $userRepository;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   * @throws Exception
   */
  public function changeEmailAddresses(Request $request) {
    $this->validate($request, ['email_addresses' => 'required|array']);

    $user = $request->auth;

    $emailAddresses = $request->input('email_addresses');

    if ($this->userRepository->updateUserEmailAddresses($user, $emailAddresses, $user->id) == null) {
      return response()->json(['msg' => 'Failed on email addresses updating...'], 500);
    }

    return response()->json([
                              'msg' => 'All email addresses saved',
                              'user' => $user->getReturnable()], 200);
  }
}
