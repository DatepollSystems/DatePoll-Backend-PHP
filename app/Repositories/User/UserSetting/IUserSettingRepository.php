<?php

namespace App\Repositories\User\UserSetting;

use App\Models\User\User;

interface IUserSettingRepository {
  /**
   * @param User $user
   * @return bool
   */
  public function getShareBirthdayForUser($user): bool;

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setShareBirthdayForUser($user, bool $value): bool;

  /**
   * @param User $user
   * @return bool
   */
  public function getShowMoviesInCalendarForUser($user): bool;

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setShowMoviesInCalendarForUser($user, bool $value): bool;

  /**
   * @param User $user
   * @return bool
   */
  public function getShowEventsInCalendarForUser($user): bool;

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setShowEventsInCalendarForUser($user, bool $value): bool;

  /**
   * @param User $user
   * @return bool
   */
  public function getShowBirthdaysInCalendarForUser($user): bool;

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setShowBirthdaysInCalendarForUser($user, bool $value): bool;

  /**
   * @param User $user
   * @return bool
   */
  public function getNotifyMeOfNewEventsForUser($user): bool;

  /**
   * @param User $user
   * @param bool $value
   * @return bool
   */
  public function setNotifyMeOfNewEventsForUser($user, bool $value): bool;
}
