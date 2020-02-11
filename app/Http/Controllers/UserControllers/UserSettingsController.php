<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Logging;
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
    return $this->getValueRequest($request, UserSettingKey::SHARE_BIRTHDAY);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setShareBirthday(Request $request) {
    return $this->setValueRequest($request, UserSettingKey::SHARE_BIRTHDAY);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getShowMoviesInCalendar(Request $request) {
    return $this->getValueRequest($request, UserSettingKey::SHOW_MOVIES_IN_CALENDAR);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setShowMoviesInCalendar(Request $request) {
    return $this->setValueRequest($request, UserSettingKey::SHOW_MOVIES_IN_CALENDAR);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getShowEventsInCalendar(Request $request) {
    return $this->getValueRequest($request, UserSettingKey::SHOW_EVENTS_IN_CALENDAR);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setShowEventsInCalendar(Request $request) {
    return $this->setValueRequest($request, UserSettingKey::SHOW_EVENTS_IN_CALENDAR);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getShowBirthdaysInCalendar(Request $request) {
    return $this->getValueRequest($request, UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setShowBirthdaysInCalendar(Request $request) {
    return $this->setValueRequest($request, UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getNotifyMeOfNewEvents(Request $request) {
    return $this->getValueRequest($request, UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setNotifyMeOfNewEvents(Request $request) {
    return $this->setValueRequest($request, UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS);
  }

  /**
   * @param Request $request
   * @param string $settingKey
   * @return JsonResponse
   * @throws ValidationException
   */
  private function setValueRequest(Request $request, string $settingKey) {
    $this->validate($request, ['setting_value' => 'required|boolean']);

    $user = $this->userRepository->getUserById($request->auth->id);
    $value = $request->input('setting_value');

    switch ($settingKey) {
      case UserSettingKey::SHARE_BIRTHDAY:
        $returnValue = $this->userSettingRepository->setShareBirthdayForUser($user, $value);
        break;
      case UserSettingKey::SHOW_MOVIES_IN_CALENDAR:
        $returnValue = $this->userSettingRepository->setShowMoviesInCalendarForUser($user, $value);
        break;
      case UserSettingKey::SHOW_EVENTS_IN_CALENDAR:
        $returnValue = $this->userSettingRepository->setShowEventsInCalendarForUser($user, $value);
        break;
      case UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR:
        $returnValue = $this->userSettingRepository->setShowBirthdaysInCalendarForUser($user, $value);
        break;
      case UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS:
        $returnValue = $this->userSettingRepository->setNotifyMeOfNewEventsForUser($user, $value);
        break;
      default:
        Logging::error('setValueRequest UserSettingsRepository', 'Unknown setting_key');
        return response()->json(['msg' => 'Could not find setting_key'], 500);
    }

    return response()->json([
      'msg' => 'Set settings successful',
      'setting_key' => $returnValue,
      'setting_value' => $value]);
  }

  /**
   * @param Request $request
   * @param string $settingKey
   * @return JsonResponse
   */
  private function getValueRequest(Request $request, string $settingKey) {
    $user = $this->userRepository->getUserById($request->auth->id);

    switch ($settingKey) {
      case UserSettingKey::SHARE_BIRTHDAY:
        $value = $this->userSettingRepository->getShareBirthdayForUser($user);
        break;
      case UserSettingKey::SHOW_MOVIES_IN_CALENDAR:
        $value = $this->userSettingRepository->getShowMoviesInCalendarForUser($user);
        break;
      case UserSettingKey::SHOW_EVENTS_IN_CALENDAR:
        $value = $this->userSettingRepository->getShowEventsInCalendarForUser($user);
        break;
      case UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR:
        $value = $this->userSettingRepository->getShowBirthdaysInCalendarForUser($user);
        break;
      case UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS:
        $value = $this->userSettingRepository->getNotifyMeOfNewEventsForUser($user);
        break;
      default:
        Logging::error('getValueRequest UserSettingsRepository', 'Unknown setting_key');
        return response()->json(['msg' => 'Could not find setting_key'], 500);
    }

    return response()->json([
      'msg' => 'Get settings successful',
      'setting_key' => $settingKey,
      'setting_value' => $value]);
  }
}
