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
  public function __construct(protected IUserSettingRepository $userSettingRepository,
                              protected IUserRepository $userRepository) {
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
  public function getShareMovieWorkerPhoneNumber(AuthenticatedRequest $request): JsonResponse {
    return $this->getValueRequest($request, UserSettingKey::SHARE_MOVIE_WORKER_PHONE_NUMBER);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setShareMovieWorkerPhoneNumber(AuthenticatedRequest $request): JsonResponse {
    return $this->setValueRequest($request, UserSettingKey::SHARE_MOVIE_WORKER_PHONE_NUMBER);
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
   * @return JsonResponse
   */
  public function getNotifyMeBroadcastEmails(AuthenticatedRequest $request): JsonResponse {
    return $this->getValueRequest($request, UserSettingKey::NOTIFY_ME_BROADCAST_EMAILS);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function setNotifyMeBroadcastEmails(AuthenticatedRequest $request): JsonResponse {
    return $this->setValueRequest($request, UserSettingKey::NOTIFY_ME_BROADCAST_EMAILS);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param string $settingKey
   * @return JsonResponse
   * @throws ValidationException
   */
  private function setValueRequest(AuthenticatedRequest $request, string $settingKey): JsonResponse {
    $this->validate($request, ['setting_value' => 'required|boolean']);

    $userId = $request->auth->id;
    $value = $request->input('setting_value');

    switch ($settingKey) {
      case UserSettingKey::SHARE_BIRTHDAY:
        $returnValue = $this->userSettingRepository->setShareBirthdayForUser($userId, $value);
        break;
      case UserSettingKey::SHARE_MOVIE_WORKER_PHONE_NUMBER:
        $returnValue = $this->userSettingRepository->setShareMovieWorkerPhoneNumber($userId, $value);
        break;
      case UserSettingKey::SHOW_MOVIES_IN_CALENDAR:
        $returnValue = $this->userSettingRepository->setShowMoviesInCalendarForUser($userId, $value);
        break;
      case UserSettingKey::SHOW_EVENTS_IN_CALENDAR:
        $returnValue = $this->userSettingRepository->setShowEventsInCalendarForUser($userId, $value);
        break;
      case UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR:
        $returnValue = $this->userSettingRepository->setShowBirthdaysInCalendarForUser($userId, $value);
        break;
      case UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS:
        $returnValue = $this->userSettingRepository->setNotifyMeOfNewEventsForUser($userId, $value);
        break;
      case UserSettingKey::NOTIFY_ME_BROADCAST_EMAILS:
        $returnValue = $this->userSettingRepository->setNotifyMeBroadcastEmailsForUser($userId, $value);
        break;
      default:
        Logging::error('setValueRequest UserSettingsRepository', 'Unknown setting_key');

        return response()->json(['msg' => 'Could not find setting_key'], 500);
    }

    Logging::info('UserSettingsController@setValueRequest',
      'Set user setting; key: "' . $settingKey . '", value: "' . $value . '", user id: ' . $userId);

    return response()->json([
      'msg' => 'Set setting successful',
      'setting_key' => $settingKey,
      'setting_value' => $returnValue,]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param string $settingKey
   * @return JsonResponse
   */
  private function getValueRequest(AuthenticatedRequest $request, string $settingKey): JsonResponse {
    $userId = $request->auth->id;

    switch ($settingKey) {
      case UserSettingKey::SHARE_BIRTHDAY:
        $value = $this->userSettingRepository->getShareBirthdayForUser($userId);
        break;
      case UserSettingKey::SHARE_MOVIE_WORKER_PHONE_NUMBER:
        $value = $this->userSettingRepository->getShareMovieWorkerPhoneNumber($userId);
        break;
      case UserSettingKey::SHOW_MOVIES_IN_CALENDAR:
        $value = $this->userSettingRepository->getShowMoviesInCalendarForUser($userId);
        break;
      case UserSettingKey::SHOW_EVENTS_IN_CALENDAR:
        $value = $this->userSettingRepository->getShowEventsInCalendarForUser($userId);
        break;
      case UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR:
        $value = $this->userSettingRepository->getShowBirthdaysInCalendarForUser($userId);
        break;
      case UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS:
        $value = $this->userSettingRepository->getNotifyMeOfNewEventsForUser($userId);
        break;
      case UserSettingKey::NOTIFY_ME_BROADCAST_EMAILS:
        $value = $this->userSettingRepository->getNotifyMeBroadcastEmailsForUser($userId);
        break;
      default:
        Logging::error('getValueRequest UserSettingsRepository', 'Unknown setting_key');

        return response()->json(['msg' => 'Could not find setting_key'], 500);
    }

    return response()->json([
      'msg' => 'Get setting successful',
      'setting_key' => $settingKey,
      'setting_value' => $value,]);
  }
}
