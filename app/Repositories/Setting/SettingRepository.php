<?php

namespace App\Repositories\Setting;

abstract class SettingKey
{
  const CINEMA_ENABLED = "cinema_enabled";
  const CINEMA_OPENWEATHERMAP_CITY_ID = "cinema_openweathermap_city_id";

  const EVENTS_ENABLED = "events_enabled";

  const FILES_ENABLED = "files_enabled";

  const URL = "url";

  const COMMUNITY_NAME = "community_name";
  const COMMUNITY_URL = "community_url";

  const OPENWEATHERMAP_KEY = "openweathermap_key";
}

use App\Models\System\Setting;
use App\Models\System\SettingValueType;

//TODO Remove env file dependencies
class SettingRepository implements ISettingRepository
{
  /**
   * @return bool
   */
  public function getCinemaEnabled(): bool {
    return $this->getBoolValueByKey(SettingKey::CINEMA_ENABLED, env('APP_FEATURE_CINEMA_ENABLED', true));
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
    return $this->getBoolValueByKey(SettingKey::EVENTS_ENABLED, env('APP_FEATURE_EVENTS_ENABLED', true));
  }

  /**
   * @param bool $isEnabled
   * @return bool
   */
  public function setEventsEnabled(bool $isEnabled): bool {
    return $this->setBoolValueByKey(SettingKey::EVENTS_ENABLED, $isEnabled);
  }

  /**
   * @return string
   */
  public function getCommunityName(): string {
    return $this->getStringValueByKey(SettingKey::COMMUNITY_NAME, env('APP_COMMUNITY_NAME', 'DatePoll'));
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
  public function getUrl(): string {
    return $this->getStringValueByKey(SettingKey::URL, env('APP_URL', 'https://test.at'));
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
  public function getCommunityUrl(): string {
    return $this->getStringValueByKey(SettingKey::COMMUNITY_URL, env('APP_COMMUNITY_URL', 'https://datepoll.dafnik.me'));
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
  public function getOpenWeatherMapKey(): string {
    return $this->getStringValueByKey(SettingKey::OPENWEATHERMAP_KEY, env('APP_OPENWEATHERMAP_KEY', 'testkey'));
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
    return $this->getStringValueByKey(SettingKey::CINEMA_OPENWEATHERMAP_CITY_ID, env('APP_OPENWEATHERMAP_CINEMA_CITY_ID', '1'));
  }

  /**
   * @param string $openWeatherMapCityId
   * @return string
   */
  public function setCinemaOpenWeatherMapCityId(string $openWeatherMapCityId): string {
    return $this->setStringValueByKey(SettingKey::CINEMA_OPENWEATHERMAP_CITY_ID, $openWeatherMapCityId);
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
   * @return Setting
   */
  private function getSettingValueByKey(string $settingKey) {
    return Setting::where('key', '=', $settingKey)
                  ->first();
  }
}