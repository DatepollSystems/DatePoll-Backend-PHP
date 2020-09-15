<?php

namespace App\Repositories\System\Setting;

abstract class SettingKey
{
  const CINEMA_ENABLED = "cinema_enabled";
  const CINEMA_OPENWEATHERMAP_CITY_ID = "cinema_openweathermap_city_id";

  const EVENTS_ENABLED = "events_enabled";

  const BROADCASTS_ENABLED = "broadcasts_enabled";

  const FILES_ENABLED = "files_enabled";

  const URL = "url";

  const COMMUNITY_NAME = "community_name";
  const COMMUNITY_URL = "community_url";
  const COMMUNITY_DESCRIPTION = "community_description";
  const COMMUNITY_IMPRINT = "community_imprint";
  const COMMUNITY_PRIVACY_POLICY = 'community_privacy_policy';
  const COMMUNITY_HAPPY_ALERT = 'community_happy_alert';

  const OPENWEATHERMAP_KEY = "openweathermap_key";

  const DATABASE_VERSION = "database_version";
}

use App\Models\System\Setting;
use App\Models\System\SettingValueType;
use App\Versions;

class SettingRepository implements ISettingRepository
{
  /**
   * @return bool
   */
  public function getCinemaEnabled(): bool {
    return $this->getBoolValueByKey(SettingKey::CINEMA_ENABLED, true);
  }

  /**
   * @param bool $isEnabled
   * @return bool
   */
  public function setCinemaEnabled(bool $isEnabled): bool {
    return $this->setBoolValueByKey(SettingKey::CINEMA_ENABLED, $isEnabled);
  }

  /**
   * @return bool
   */
  public function getEventsEnabled(): bool {
    return $this->getBoolValueByKey(SettingKey::EVENTS_ENABLED, true);
  }

  /**
   * @param bool $isEnabled
   * @return bool
   */
  public function setEventsEnabled(bool $isEnabled): bool {
    return $this->setBoolValueByKey(SettingKey::EVENTS_ENABLED, $isEnabled);
  }

  /**
   * @return bool
   */
  public function getBroadcastsEnabled(): bool {
    return $this->getBoolValueByKey(SettingKey::BROADCASTS_ENABLED, false);
  }

  /**
   * @param bool $isEnabled
   * @return bool
   */
  public function setBroadcastsEnabled(bool $isEnabled): bool {
    return $this->setBoolValueByKey(SettingKey::BROADCASTS_ENABLED, $isEnabled);
  }

  /**
   * @return string
   */
  public function getUrl(): string {
    return $this->getStringValueByKey(SettingKey::URL, 'https://datepoll.org');
  }

  /**
   * @param string $url
   * @return string
   */
  public function setUrl(string $url): string {
    return $this->setStringValueByKey(SettingKey::URL, $url);
  }

  /**
   * @return string
   */
  public function getCommunityName(): string {
    return $this->getStringValueByKey(SettingKey::COMMUNITY_NAME, 'DatePoll');
  }

  /**
   * @param string $communityName
   * @return string
   */
  public function setCommunityName(string $communityName): string {
    return $this->setStringValueByKey(SettingKey::COMMUNITY_NAME, $communityName);
  }

  /**
   * @return string
   */
  public function getCommunityUrl(): string {
    return $this->getStringValueByKey(SettingKey::COMMUNITY_URL, 'https://datepoll.org');
  }

  /**
   * @param string $communityUrl
   * @return string
   */
  public function setCommunityUrl(string $communityUrl): string {
    return $this->setStringValueByKey(SettingKey::COMMUNITY_URL, $communityUrl);
  }

  /**
   * @return string
   */
  public function getCommunityDescription(): string {
    return $this->getStringValueByKey(SettingKey::COMMUNITY_DESCRIPTION, 'Vereine sind schön! :)');
  }

  /**
   * @param string $communityDescription
   * @return string
   */
  public function setCommunityDescription(string $communityDescription): string {
    return $this->setStringValueByKey(SettingKey::COMMUNITY_DESCRIPTION, $communityDescription);
  }

  /**
   * @return string
   */
  public function getCommunityImprint(): string {
    return $this->getStringValueByKey(SettingKey::COMMUNITY_IMPRINT, 'You should provide your website imprint here.');
  }

  /**
   * @param string $communityImprint
   * @return string
   */
  public function setCommunityImprint(string $communityImprint): string {
    return $this->setStringValueByKey(SettingKey::COMMUNITY_IMPRINT, $communityImprint);
  }

  /**
   * @return string
   */
  public function getCommunityPrivacyPolicy(): string {
    return $this->getStringValueByKey(SettingKey::COMMUNITY_PRIVACY_POLICY, 'You should provide your website privacy policy here.');
  }

  /**
   * @param string $communityPrivacyPolicy
   * @return string
   */
  public function setCommunityPrivacyPolicy(string $communityPrivacyPolicy): string {
    return $this->setStringValueByKey(SettingKey::COMMUNITY_PRIVACY_POLICY, $communityPrivacyPolicy);
  }

  /**
   * @return string
   */
  public function getOpenWeatherMapKey(): string {
    return $this->getStringValueByKey(SettingKey::OPENWEATHERMAP_KEY, 'testkey');
  }

  /**
   * @param string $openWeatherMapKey
   * @return string
   */
  public function setOpenWeatherMapKey(string $openWeatherMapKey): string {
    return $this->setStringValueByKey(SettingKey::OPENWEATHERMAP_KEY, $openWeatherMapKey);
  }

  /**
   * @return string
   */
  public function getCinemaOpenWeatherMapCityId(): string {
    return $this->getStringValueByKey(SettingKey::CINEMA_OPENWEATHERMAP_CITY_ID, '1');
  }

  /**
   * @param string $openWeatherMapCityId
   * @return string
   */
  public function setCinemaOpenWeatherMapCityId(string $openWeatherMapCityId): string {
    return $this->setStringValueByKey(SettingKey::CINEMA_OPENWEATHERMAP_CITY_ID, $openWeatherMapCityId);
  }

  /**
   * @return string
   */
  public function getHappyAlert(): string {
    return $this->getStringValueByKey(SettingKey::COMMUNITY_HAPPY_ALERT, '');
  }

  /**
   * @param string $happyAlert
   * @return string
   */
  public function setHappyAlert(string $happyAlert): string {
    return $this->setStringValueByKey(SettingKey::COMMUNITY_HAPPY_ALERT, $happyAlert);
  }

  /**
   * @return int
   */
  public function getCurrentDatabaseVersion(): int {
    return $this->getIntegerValueByKey(SettingKey::DATABASE_VERSION, Versions::getApplicationDatabaseVersion());
  }

  /**
   * @param int $currentDatabaseVersion
   * @return int
   */
  public function setCurrentDatabaseVersion(int $currentDatabaseVersion): int {
    return $this->setIntegerValueByKey(SettingKey::DATABASE_VERSION, $currentDatabaseVersion);
  }


  /**
   * @param string $settingKey
   * @param string $default
   * @return string
   */
  private function getStringValueByKey(string $settingKey, string $default) {
    $setting = $this->getSettingValueByKey($settingKey);
    if ($setting == null) {
      $newSetting = new Setting([
        'type' => SettingValueType::STRING,
        'key' => $settingKey,
        'value' => $default]);

      $newSetting->save();

      return $newSetting->value;
    } else {
      return $setting->value;
    }
  }

  /**
   * @param string $settingKey
   * @param string $value
   * @return string
   */
  private function setStringValueByKey(string $settingKey, string $value) {
    $setting = $this->getSettingValueByKey($settingKey);
    if ($setting == null) {
      $newSetting = new Setting([
        'type' => SettingValueType::STRING,
        'key' => $settingKey,
        'value' => $value]);

      $newSetting->save();

      return $newSetting->value;
    } else {
      $setting->value = $value;
      $setting->save();
      return $setting->value;
    }
  }

  /**
   * @param string $settingKey
   * @param bool $default
   * @return bool
   */
  private function getBoolValueByKey(string $settingKey, bool $default) {
    $setting = $this->getSettingValueByKey($settingKey);

    if ($setting == null) {
      if ($default) {
        $valueToSave = 'true';
      } else {
        $valueToSave = 'false';
      }

      $newSetting = new Setting([
        'type' => SettingValueType::BOOLEAN,
        'key' => $settingKey,
        'value' => $valueToSave]);

      $newSetting->save();

      return $newSetting->value;
    } else {
      return $setting->value;
    }
  }

  /**
   * @param string $settingKey
   * @param bool $value
   * @return bool
   */
  private function setBoolValueByKey(string $settingKey, bool $value) {
    $setting = $this->getSettingValueByKey($settingKey);

    if ($setting == null) {
      if ($value) {
        $valueToSave = 'true';
      } else {
        $valueToSave = 'false';
      }

      $newSetting = new Setting([
        'type' => SettingValueType::STRING,
        'key' => $settingKey,
        'value' => $valueToSave]);

      $newSetting->save();

      return $newSetting->value;
    } else {
      $setting->value = $value;
      $setting->save();
      return $setting->value;
    }
  }

  /**
   * @param string $settingKey
   * @param int $value
   * @return int
   */
  private function setIntegerValueByKey(string $settingKey, int $value) {
    $setting = $this->getSettingValueByKey($settingKey);
    if ($setting == null) {
      $newSetting = new Setting([
        'type' => SettingValueType::INTEGER,
        'key' => $settingKey,
        'value' => $value]);

      $newSetting->save();

      return $newSetting->value;
    } else {
      $setting->value = $value;
      $setting->save();
      return $setting->value;
    }
  }

  /**
   * @param string $settingKey
   * @param int $default
   * @return int
   */
  private function getIntegerValueByKey(string $settingKey, int $default) {
    $setting = $this->getSettingValueByKey($settingKey);

    if ($setting == null) {
      $newSetting = new Setting([
        'type' => SettingValueType::INTEGER,
        'key' => $settingKey,
        'value' => $default]);

      $newSetting->save();

      return $newSetting->value;
    } else {
      return $setting->value;
    }
  }

  /**
   * @param string $settingKey
   * @return Setting
   */
  private function getSettingValueByKey(string $settingKey) {
    return Setting::where('key', '=', $settingKey)
                  ->first();
  }
}
