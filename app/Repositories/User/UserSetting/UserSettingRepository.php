<?php /** @noinspection PhpMultipleClassesDeclarationsInOneFile */

namespace App\Repositories\User\UserSetting;

abstract class UserSettingKey {
  const SHARE_BIRTHDAY = 'share_birthday';
  const SHOW_MOVIES_IN_CALENDAR = 'show_movies_in_calendar';
  const SHOW_EVENTS_IN_CALENDAR = 'show_events_in_calendar';
  const SHOW_BIRTHDAYS_IN_CALENDAR = 'show_birthdays_in_calendar';
  const NOTIFY_ME_OF_NEW_EVENTS = 'notify_me_of_new_events';
}

use App\Models\User\User;
use App\Repositories\User\UserToken\IUserTokenRepository;

class UserSettingRepository implements IUserSettingRepository {
  protected IUserTokenRepository $userTokenRepository;

  public function __construct(IUserTokenRepository $userTokenRepository) {
    $this->userTokenRepository = $userTokenRepository;
  }

  /**
   * @param User $user
   * @return bool
   */
  public function getShareBirthdayForUser(User $user): bool {
    return $this->getUserSetting($user, UserSettingKey::SHARE_BIRTHDAY, true);
  }

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setShareBirthdayForUser(User $user, bool $value): bool {
    return $this->setUserSetting($user, UserSettingKey::SHARE_BIRTHDAY, $value);
  }

  /**
   * @param User $user
   * @return bool
   */
  public function getShowMoviesInCalendarForUser(User $user): bool {
    return $this->getUserSetting($user, UserSettingKey::SHOW_MOVIES_IN_CALENDAR, true);
  }

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setShowMoviesInCalendarForUser(User $user, bool $value): bool {
    return $this->setUserSetting($user, UserSettingKey::SHOW_MOVIES_IN_CALENDAR, $value);
  }

  /**
   * @param User $user
   * @return bool
   */
  public function getShowEventsInCalendarForUser(User $user): bool {
    return $this->getUserSetting($user, UserSettingKey::SHOW_EVENTS_IN_CALENDAR, true);
  }

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setShowEventsInCalendarForUser(User $user, bool $value): bool {
    return $this->setUserSetting($user, UserSettingKey::SHOW_EVENTS_IN_CALENDAR, $value);
  }

  /**
   * @param User $user
   * @return bool
   */
  public function getShowBirthdaysInCalendarForUser(User $user): bool {
    return $this->getUserSetting($user, UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR, false);
  }

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setShowBirthdaysInCalendarForUser(User $user, bool $value): bool {
    return $this->setUserSetting($user, UserSettingKey::SHOW_BIRTHDAYS_IN_CALENDAR, $value);
  }

  /**
   * @param User $user
   * @return bool
   */
  public function getNotifyMeOfNewEventsForUser(User $user): bool {
    return $this->getUserSetting($user, UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS, false);
  }

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setNotifyMeOfNewEventsForUser(User $user, bool $value): bool {
    return $this->setUserSetting($user, UserSettingKey::NOTIFY_ME_OF_NEW_EVENTS, $value);
  }

  /**
   * @param User $user
   * @param string $settingKey
   * @param bool $value
   * @return bool
   */
  private function setUserSetting(User $user, string $settingKey, bool $value): bool {
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
   * @param User $user
   * @param string $settingKey
   * @param bool $default
   * @return bool
   */
  private function getUserSetting(User $user, string $settingKey, bool $default): bool {
    $setting = $this->userTokenRepository->getUserTokenByUserAndPurpose($user, $settingKey);
    if ($setting == null) {
      $setting = $this->userTokenRepository->createUserToken($user, $default, $settingKey);

      return $setting->token;
    } else {
      return $setting->token;
    }
  }
}
