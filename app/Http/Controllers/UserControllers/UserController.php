<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use stdClass;

class UserController extends Controller
{
  protected $userRepository = null;

  public function __construct(IUserRepository $userRepository) {
    $this->userRepository = $userRepository;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getMyself(Request $request) {
    $user = $request->auth;

    $toReturnUser = $user->getReturnable();

    return response()->json([
      'msg' => 'Get yourself',
      'user' => $toReturnUser], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function updateMyself(Request $request) {
    $this->validate($request, [
      'title' => 'max:190',
      'streetname' => 'required|max:190|min:1',
      'streetnumber' => 'required|max:190|min:1',
      'zipcode' => 'required|integer',
      'location' => 'required|max:190|min:1',
      'birthday' => 'required|date']);

    $user = $request->auth;

    $user->title = $request->input('title');
    $user->streetname = $request->input('streetname');
    $user->streetnumber = $request->input('streetnumber');
    $user->zipcode = $request->input('zipcode');
    $user->location = $request->input('location');
    $user->birthday = $request->input('birthday');

    if ($user->save()) {
      $userToShow = $user->getReturnable();

      $userToShow->view_yourself = [
        'href' => 'api/v1/user/myself',
        'method' => 'GET'];

      $response = [
        'msg' => 'User updated',
        'user' => $userToShow];

      return response()->json($response, 201);
    }

    $response = ['msg' => 'An error occurred'];

    return response()->json($response, 500);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function homepage(Request $request) {
    $user = $request->auth;

    $response = $this->userRepository->getHomepageDataForUser($user);

    return response()->json($response, 200);
  }
}
