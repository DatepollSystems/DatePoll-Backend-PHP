<?php

namespace App\Repositories\User\UserSetting;

abstract class UserSettingKey
{
  const SHARE_BIRTHDAY = "share_birthday";
  const SHOW_MOVIES_IN_CALENDAR = 'show_movies_in_calendar';
  const SHOW_EVENTS_IN_CALENDAR = 'show_events_in_calendar';
  const SHOW_BIRTHDAYS_IN_CALENDAR = 'show_birthdays_in_calendar';
  const NOTIFY_ME_OF_NEW_EVENTS = 'notify_me_of_new_events';
}

use App\Repositories\User\UserToken\IUserTokenRepository;

class UserSettingRepository implements IUserSettingRepository
{
  protected $userTokenRepository = null;

  public function __construct(IUserTokenRepository $userTokenRepository) {
    $this->userTokenRepository = $userTokenRepository;
  }

  public function getShareBirthdayForUser($user): bool {
    return $this->getUserSetting($user, UserSettingKey::SHARE_BIRTHDAY, true);
  }

  public function setShareBirthdayForUser($user, bool $value): bool {
    return $this->setUserSetting($user, UserSettingKey::SHARE_BIRTHDAY, $value);
  }

  public function getShowMoviesInCalendarForUser($user): bool {
    return $this->getUserSetting($user, UserSettingKey::SHOW_MOVIES_IN_CALENDAR, true);
  }

  public function setShowMoviesInCalendarForUser($user, bool $value): bool {
    return $this->setUserSetting($user, UserSettingKey::SHOW_MOVIES_IN_CALENDAR, $value);
  }

  public function getShowEventsInCalendarForUser($user): bool {
    return $this->getUserSetting($user, UserSettingKey::SHOW_EVENTS_IN_CALENDAR, true);
  }

  public function setShowEventsInCalendarForUser($user, bool $value): bool {
    return $this->setUserSetting($user, UserSettingKey::SHOW_EVENTS_IN_CALENDAR, $value);
  }

  public function getShowBirthdaysInCalendarForUser($user): bool {
    return $this->getUserSetting($user, UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR, true);
  }

  public function setShowBirthdaysInCalendarForUser($user, bool $value): bool {
    return $this->setUserSetting($user, UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR, $value);
  }

  public function getNotifyMeOfNewEventsForUser($user): bool {
    return $this->getUserSetting($user, UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS, false);
  }

  public function setNotifyMeOfNewEventsForUser($user, bool $value): bool {
    return $this->setUserSetting($user, UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS, $value);
  }

  /**
   * @param $user
   * @param string $settingKey
   * @param bool $value
   * @return bool
   */
  private function setUserSetting($user, string $settingKey, bool $value): bool {
    $setting = $this->userTokenRepository->getUserTokenByUserAndPurpose($user, $settingKey);
    if ($setting == null) {
      return $this->userTokenRepository->createUserToken($user, $value, $settingKey)->token;
    } else {
      $setting->token = $value;
      $setting->save();
    }
    return $setting->token;
  }

  /**
   * @param $user
   * @param string $settingKey
   * @param bool $default
   * @return bool
   */
  private function getUserSetting($user, string $settingKey, bool $default): bool {
    $setting = $this->userTokenRepository->getUserTokenByUserAndPurpose($user, $settingKey);
    if ($setting == null) {
      $setting = $this->userTokenRepository->createUserToken($user, $default, $settingKey);
      return $setting->token;
    } else {
      return $setting->token;
    }
  }
}