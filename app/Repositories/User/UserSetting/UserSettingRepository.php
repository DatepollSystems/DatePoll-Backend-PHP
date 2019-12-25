<?php

namespace App\Repositories\User\UserSetting;

abstract class UserSettingKey
{
  const SHARE_BIRTHDAY = "share_birthday";
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