<?php

namespace App\Repositories\User\UserSetting;

interface IUserSettingRepository
{
  /**
   * @param $user
   * @return bool
   */
  public function getShareBirthdayForUser($user): bool;

  /**
   * @param $user
   * @param bool $value
   * @return bool
   */
  public function setShareBirthdayForUser($user, bool $value): bool;
}