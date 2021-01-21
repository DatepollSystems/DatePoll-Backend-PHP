<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use App\Repositories\User\UserSetting\UserSettingKey;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserSettingsController extends Controller {
  protected IUserSettingRepository $userSettingRepository;
  protected IUserRepository $userRepository;

  public function __construct(IUserSettingRepository $userSettingRepository, IUserRepository $userRepository) {
    $this->userSettingRepository = $userSettingRepository;
    $this->userRepository = $userRepository;
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function getShareBirthday(AuthenticatedRequest $request): JsonResponse {
    return $this->getValueRequest($request, UserSettingKey::SHARE_BIRTHDAY);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setShareBirthday(AuthenticatedRequest $request): JsonResponse {
    return $this->setValueRequest($request, UserSettingKey::SHARE_BIRTHDAY);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function getShowMoviesInCalendar(AuthenticatedRequest $request): JsonResponse {
    return $this->getValueRequest($request, UserSettingKey::SHOW_MOVIES_IN_CALENDAR);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setShowMoviesInCalendar(AuthenticatedRequest $request): JsonResponse {
    return $this->setValueRequest($request, UserSettingKey::SHOW_MOVIES_IN_CALENDAR);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function getShowEventsInCalendar(AuthenticatedRequest $request): JsonResponse {
    return $this->getValueRequest($request, UserSettingKey::SHOW_EVENTS_IN_CALENDAR);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setShowEventsInCalendar(AuthenticatedRequest $request): JsonResponse {
    return $this->setValueRequest($request, UserSettingKey::SHOW_EVENTS_IN_CALENDAR);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function getShowBirthdaysInCalendar(AuthenticatedRequest $request): JsonResponse {
    return $this->getValueRequest($request, UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setShowBirthdaysInCalendar(AuthenticatedRequest $request): JsonResponse {
    return $this->setValueRequest($request, UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function getNotifyMeOfNewEvents(AuthenticatedRequest $request): JsonResponse {
    return $this->getValueRequest($request, UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setNotifyMeOfNewEvents(AuthenticatedRequest $request): JsonResponse {
    return $this->setValueRequest($request, UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param string $settingKey
   * @return JsonResponse
   * @throws ValidationException
   */
  private function setValueRequest(AuthenticatedRequest $request, string $settingKey): JsonResponse {
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
      'msg' => 'Set setting successful',
      'setting_key' => $settingKey,
      'setting_value' => $returnValue, ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param string $settingKey
   * @return JsonResponse
   */
  private function getValueRequest(AuthenticatedRequest $request, string $settingKey): JsonResponse {
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
      'msg' => 'Get setting successful',
      'setting_key' => $settingKey,
      'setting_value' => $value, ]);
  }
}
