<?php

namespace App\Repositories\User\UserSetting;

interface IUserSettingRepository {
  /**
   * @param int $userId
   * @return bool
   */
  public function getShareBirthdayForUser(int $userId): bool;

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setShareBirthdayForUser(int $userId, bool $value): bool;

  /**
   * @param int $userId
   * @return bool
   */
  public function getShareMovieWorkerPhoneNumber(int $userId): bool;

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setShareMovieWorkerPhoneNumber(int $userId, bool $value): bool;

  /**
   * @param int $userId
   * @return bool
   */
  public function getShowMoviesInCalendarForUser(int $userId): bool;

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setShowMoviesInCalendarForUser(int $userId, bool $value): bool;

  /**
   * @param int $userId
   * @return bool
   */
  public function getShowEventsInCalendarForUser(int $userId): bool;

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setShowEventsInCalendarForUser(int $userId, bool $value): bool;

  /**
   * @param int $userId
   * @return bool
   */
  public function getShowBirthdaysInCalendarForUser(int $userId): bool;

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setShowBirthdaysInCalendarForUser(int $userId, bool $value): bool;

  /**
   * @param int $userId
   * @return bool
   */
  public function getNotifyMeOfNewEventsForUser(int $userId): bool;

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setNotifyMeOfNewEventsForUser(int $userId, bool $value): bool;

  /**
   * @param int $userId
   * @return bool
   */
  public function getNotifyMeBroadcastEmailsForUser(int $userId): bool;

  /**
   * @param int $userId
   * @param bool $value
   * @return bool
   */
  public function setNotifyMeBroadcastEmailsForUser(int $userId, bool $value): bool;
}
