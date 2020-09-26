<?php

namespace App\Repositories\System\Setting;

use App\Models\System\Setting;

interface ISettingRepository
{
  /**
   * @return bool
   */
  public function getCinemaEnabled(): bool;

  /**
   * @param bool $isEnabled
   * @return bool
   */
  public function setCinemaEnabled(bool $isEnabled);

  /**
   * @return bool
   */
  public function getEventsEnabled(): bool;

  /**
   * @param bool $isEnabled
   * @return bool
   */
  public function setEventsEnabled(bool $isEnabled): bool;

  /**
   * @return bool
   */
  public function getBroadcastsEnabled(): bool;

  /**
   * @param bool $isEnabled
   * @return bool
   */
  public function setBroadcastsEnabled(bool $isEnabled): bool;

  /**
   * @return string
   */
  public function getUrl(): string;

  /**
   * @param string $url
   * @return string
   */
  public function setUrl(string $url): string;

  /**
   * @return string
   */
  public function getCommunityName(): string;

  /**
   * @param string $communityName
   * @return string
   */
  public function setCommunityName(string $communityName): string;

  /**
   * @return string
   */
  public function getCommunityUrl(): string;

  /**
   * @param string $communityUrl
   * @return string
   */
  public function setCommunityUrl(string $communityUrl): string;

  /**
   * @return string
   */
  public function getCommunityDescription(): string;

  /**
   * @param string $communityDescription
   * @return string
   */
  public function setCommunityDescription(string $communityDescription): string;

  /**
   * @return string
   */
  public function getCommunityImprint(): string;

  /**
   * @param string $communityImprint
   * @return string
   */
  public function setCommunityImprint(string $communityImprint): string;

  /**
   * @return string
   */
  public function getCommunityPrivacyPolicy(): string;

  /**
   * @param string $communityPrivacyPolicy
   * @return string
   */
  public function setCommunityPrivacyPolicy(string $communityPrivacyPolicy): string;

  /**
   * @return string
   */
  public function getOpenWeatherMapKey(): string;

  /**
   * @param string $openWeatherMapKey
   * @return string
   */
  public function setOpenWeatherMapKey(string $openWeatherMapKey): string;

  /**
   * @return string
   */
  public function getCinemaOpenWeatherMapCityId(): string;

  /**
   * @param string $openWeatherMapCityId
   * @return string
   */
  public function setCinemaOpenWeatherMapCityId(string $openWeatherMapCityId): string;

  /**
   * @return string
   */
  public function getHappyAlert(): string;

  /**
   * @param string $happyAlert
   * @return string
   */
  public function setHappyAlert(string $happyAlert): string;

  /**
   * @return int
   */
  public function getCurrentDatabaseVersion(): int;

  /**
   * @param int $currentDatabaseVersion
   * @return int
   */
  public function setCurrentDatabaseVersion(int $currentDatabaseVersion): int;
}
