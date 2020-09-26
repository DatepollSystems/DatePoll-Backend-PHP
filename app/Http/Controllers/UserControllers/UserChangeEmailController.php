<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\User\UserEmailAddress;
use App\Repositories\User\User\IUserRepository;
use Illuminate\Http\Request;

class UserChangeEmailController extends Controller
{

  protected $userRepository = null;

  public function __construct(IUserRepository $userRepository) {
    $this->userRepository = $userRepository;
  }

  public function changeEmailAddresses(Request $request) {
    $this->validate($request, ['email_addresses' => 'required|array']);

    $user = $request->auth;

    $emailAddresses = $request->input('email_addresses');

    if($this->userRepository->updateUserEmailAddresses($user, $emailAddresses, $user->id) == null) {
      return response()->json(['msg' => 'Failed on email addresses updating...'], 500);
    }

    return response()->json([
      'msg' => 'All email addresses saved',
      'user' => $user->getReturnable()], 200);
  }
}
