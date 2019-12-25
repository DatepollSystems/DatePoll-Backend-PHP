<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use App\Repositories\User\UserSetting\UserSettingKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserSettingsController extends Controller
{

  protected $userSettingRepository = null;
  protected $userRepository = null;

  public function __construct(IUserSettingRepository $userSettingRepository, IUserRepository $userRepository) {
    $this->userSettingRepository = $userSettingRepository;
    $this->userRepository = $userRepository;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getShareBirthday(Request $request) {
    $user = $this->userRepository->getUserById($request->auth->id);

    $shareBirthday = $this->userSettingRepository->getShareBirthdayForUser($user);

    return $this->userSettingJsonWrapper(UserSettingKey::SHARE_BIRTHDAY, $shareBirthday, true);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setShareBirthday(Request $request) {
    $this->validate($request, ['setting_value' => 'required|boolean']);

    $user = $this->userRepository->getUserById($request->auth->id);
    $value = $request->input('setting_value');

    $shareBirthday = $this->userSettingRepository->setShareBirthdayForUser($user, $value);

    return $this->userSettingJsonWrapper(UserSettingKey::SHARE_BIRTHDAY, $shareBirthday, false);
  }

  /**
   * @param string $userSettingKey
   * @param bool $value
   * @param bool $get
   * @return JsonResponse
   */
  private function userSettingJsonWrapper(string $userSettingKey, bool $value, bool $get) {
    if ($get) {
      return response()->json(['msg' => 'Get settings successful', 'setting_key' => $userSettingKey, 'setting_value' => $value]);
    } else {
      return response()->json(['msg' => 'Set settings successful', 'setting_key' => $userSettingKey, 'setting_value' => $value]);
    }
  }
}
