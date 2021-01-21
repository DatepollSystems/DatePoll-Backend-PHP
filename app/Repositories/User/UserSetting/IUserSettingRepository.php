<?php

namespace App\Repositories\User\UserSetting;

use App\Models\User\User;

interface IUserSettingRepository {
  /**
   * @param User $user
   * @return bool
   */
  public function getShareBirthdayForUser(User $user): bool;

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setShareBirthdayForUser(User $user, bool $value): bool;

  /**
   * @param User $user
   * @return bool
   */
  public function getShowMoviesInCalendarForUser(User $user): bool;

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setShowMoviesInCalendarForUser(User $user, bool $value): bool;

  /**
   * @param User $user
   * @return bool
   */
  public function getShowEventsInCalendarForUser(User $user): bool;

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setShowEventsInCalendarForUser(User $user, bool $value): bool;

  /**
   * @param User $user
   * @return bool
   */
  public function getShowBirthdaysInCalendarForUser(User $user): bool;

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setShowBirthdaysInCalendarForUser(User $user, bool $value): bool;

  /**
   * @param User $user
   * @return bool
   */
  public function getNotifyMeOfNewEventsForUser(User $user): bool;

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setNotifyMeOfNewEventsForUser(User $user, bool $value): bool;
}
