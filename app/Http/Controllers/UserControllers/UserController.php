<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserChange\IUserChangeRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use stdClass;

class UserController extends Controller
{
  protected $userRepository = null;
  protected $userChangeRepository = null;

  public function __construct(IUserRepository $userRepository, IUserChangeRepository $userChangeRepository) {
    $this->userRepository = $userRepository;
    $this->userChangeRepository = $userChangeRepository;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getMyself(Request $request) {
    $user = $request->auth;
    return response()->json([
      'msg' => 'Get yourself',
      'user' => $user->getReturnable()], 200);
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

    $title = $request->input('title');
    $birthday = $request->input('birthday');
    $streetname = $request->input('streetname');
    $streetnumber = $request->input('streetnumber');
    $zipcode = $request->input('zipcode');
    $location = $request->input('location');

    $this->userRepository->checkForPropertyChange('title', $user->id, $user->id, $title, $user->title);
    $this->userRepository->checkForPropertyChange('birthday', $user->id, $user->id, $birthday, $user->birthday);
    $this->userRepository->checkForPropertyChange('streetname', $user->id, $user->id, $streetname, $user->streetname);
    $this->userRepository->checkForPropertyChange('streetnumber', $user->id, $user->id, $streetnumber, $user->streetnumber);
    $this->userRepository->checkForPropertyChange('location', $user->id, $user->id, $location, $user->location);
    // Don't use checkForPropertyChange function because these values aren't strings
    if ($user->zipcode != $zipcode) {
      $this->userChangeRepository->createUserChange('zipcode', $user->id,  $user->id, $zipcode, $user->zipcode);
    }

    $user->title = $title;
    $user->streetname = $streetname;
    $user->streetnumber = $streetnumber;
    $user->zipcode = $zipcode;
    $user->location = $location;
    $user->birthday = $birthday;

    if (!$user->save()) {
      return response()->json(['msg' => 'An error occurred'], 500);
    }

    return response()->json([
      'msg' => 'User updated',
      'user' => $user->getReturnable()], 201);
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
