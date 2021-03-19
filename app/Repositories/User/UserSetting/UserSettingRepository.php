<?php /** @noinspection PhpMultipleClassesDeclarationsInOneFile */

namespace App\Repositories\User\UserSetting;

abstract class UserSettingKey {
  public const SHARE_BIRTHDAY = 'share_birthday';
  public const SHARE_MOVIE_WORKER_PHONE_NUMBER = 'share_movie_worker_phone_number';
  public const SHOW_MOVIES_IN_CALENDAR = 'show_movies_in_calendar';
  public const SHOW_EVENTS_IN_CALENDAR = 'show_events_in_calendar';
  public const SHOW_BIRTHDAYS_IN_CALENDAR = 'show_birthdays_in_calendar';
  public const NOTIFY_ME_OF_NEW_EVENTS = 'notify_me_of_new_events';
  public const NOTIFY_ME_BROADCAST_EMAILS = 'notify_me_broadcast_emails';
}

use App\Repositories\User\UserToken\IUserTokenRepository;
use App\Utils\Converter;

class UserSettingRepository implements IUserSettingRepository {
  protected IUserTokenRepository $userTokenRepository;

  public function __construct(IUserTokenRepository $userTokenRepository) {
    $this->userTokenRepository = $userTokenRepository;
  }

  /**
   * @param int $userId
   * @return bool
   */
  public function getShareBirthdayForUser(int $userId): bool {
    return $this->getUserSetting($userId, UserSettingKey::SHARE_BIRTHDAY, true);
  }

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setShareBirthdayForUser(int $userId, bool $value): bool {
    return $this->setUserSetting($userId, UserSettingKey::SHARE_BIRTHDAY, $value);
  }

  /**
   * @param int $userId
   * @return bool
   */
  public function getShareMovieWorkerPhoneNumber(int $userId): bool {
    return $this->getUserSetting($userId, UserSettingKey::SHARE_MOVIE_WORKER_PHONE_NUMBER, true);
  }

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setShareMovieWorkerPhoneNumber(int $userId, bool $value): bool {
    return $this->setUserSetting($userId, UserSettingKey::SHARE_MOVIE_WORKER_PHONE_NUMBER, $value);
  }

  /**
   * @param int $userId
   * @return bool
   */
  public function getShowMoviesInCalendarForUser(int $userId): bool {
    return $this->getUserSetting($userId, UserSettingKey::SHOW_MOVIES_IN_CALENDAR, true);
  }

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setShowMoviesInCalendarForUser(int $userId, bool $value): bool {
    return $this->setUserSetting($userId, UserSettingKey::SHOW_MOVIES_IN_CALENDAR, $value);
  }

  /**
   * @param int $userId
   * @return bool
   */
  public function getShowEventsInCalendarForUser(int $userId): bool {
    return $this->getUserSetting($userId, UserSettingKey::SHOW_EVENTS_IN_CALENDAR, true);
  }

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setShowEventsInCalendarForUser(int $userId, bool $value): bool {
    return $this->setUserSetting($userId, UserSettingKey::SHOW_EVENTS_IN_CALENDAR, $value);
  }

  /**
   * @param int $userId
   * @return bool
   */
  public function getShowBirthdaysInCalendarForUser(int $userId): bool {
    return $this->getUserSetting($userId, UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR, false);
  }

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setShowBirthdaysInCalendarForUser(int $userId, bool $value): bool {
    return $this->setUserSetting($userId, UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR, $value);
  }

  /**
   * @param int $userId
   * @return bool
   */
  public function getNotifyMeOfNewEventsForUser(int $userId): bool {
    return $this->getUserSetting($userId, UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS, false);
  }

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setNotifyMeOfNewEventsForUser(int $userId, bool $value): bool {
    return $this->setUserSetting($userId, UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS, $value);
  }

  /**
   * @param int $userId
   * @return bool
   */
  public function getNotifyMeBroadcastEmailsForUser(int $userId): bool {
    return $this->getUserSetting($userId, UserSettingKey::NOTIFY_ME_BROADCAST_EMAILS, true);
  }

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setNotifyMeBroadcastEmailsForUser(int $userId, bool $value): bool {
    return $this->setUserSetting($userId, UserSettingKey::NOTIFY_ME_BROADCAST_EMAILS, $value);
  }

  /**
   * @param int $userId
   * @param string $settingKey
   * @param bool $value
   * @return bool
   */
  private function setUserSetting(int $userId, string $settingKey, bool $value): bool {
    $valueToSave = Converter::booleanToString($value);

    $setting = $this->userTokenRepository->getUserTokenByUserAndPurpose($userId, $settingKey);
    if ($setting == null) {
      $setting = $this->userTokenRepository->createUserToken($userId, $valueToSave, $settingKey)->token;
    } else {
      $setting->token = $valueToSave;
      $setting->save();
    }

    return Converter::stringToBoolean($setting->token);
  }

  /**
   * @param int $userId
   * @param string $settingKey
   * @param bool $default
   * @return bool
   */
  private function getUserSetting(int $userId, string $settingKey, bool $default): bool {
    $setting = $this->userTokenRepository->getUserTokenByUserAndPurpose($userId, $settingKey);
    if ($setting == null) {
      return $default;
    }

    return Converter::stringToBoolean($setting->token);
  }
}
