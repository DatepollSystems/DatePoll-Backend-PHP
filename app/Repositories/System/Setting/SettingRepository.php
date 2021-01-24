<?php /** @noinspection PhpMultipleClassesDeclarationsInOneFile */

namespace App\Repositories\System\Setting;

abstract class SettingKey {
  public const CINEMA_ENABLED = 'cinema_enabled';
  public const CINEMA_OPENWEATHERMAP_CITY_ID = 'cinema_openweathermap_city_id';

  public const EVENTS_ENABLED = 'events_enabled';

  public const BROADCASTS_ENABLED = 'broadcasts_enabled';
  public const BROADCASTS_PROCESS_INCOMING_EMAIL_ENABLED = 'broadcasts_process_incoming_emails_enabled';
  public const BROADCASTS_PROCESS_INCOMING_EMAIL_FORWARDING_ENABLED = 'broadcasts_process_incoming_emails_forwarding_enabled';
  public const BROADCASTS_PROCESS_INCOMING_EMAIL_FORWARDING_EMAIL_ADDRESSES = 'broadcasts_process_incoming_emails_forwarding_email_addresses';

  public const SEAT_RESERVATION_ENABLED = 'seat_reservation_enabled';

  public const FILES_ENABLED = 'files_enabled';

  public const URL = 'url';

  public const COMMUNITY_NAME = 'community_name';
  public const COMMUNITY_URL = 'community_url';
  public const COMMUNITY_DESCRIPTION = 'community_description';
  public const COMMUNITY_IMPRINT = 'community_imprint';
  public const COMMUNITY_PRIVACY_POLICY = 'community_privacy_policy';
  public const COMMUNITY_ALERT = 'community_alert';
  public const COMMUNITY_ALERT_TYPE = 'community_alert_type';

  public const JITSI_INSTANCE_URL = 'jitsi_instance_url';
  public const OPENWEATHERMAP_KEY = 'openweathermap_key';

  public const DATABASE_VERSION = 'database_version';
}

abstract class CommunityAlertTypes {
  public const HAPPY = 'happy';
  public const NORMAL = 'normal';
}

use App\Models\System\Setting;
use App\Utils\ArrayHelper;
use App\Utils\Converter;
use App\Utils\StringHelper;
use App\Versions;
use Exception;
use Illuminate\Support\Facades\Cache;
use stdClass;

class SettingRepository implements ISettingRepository {

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
   * @return bool
   */
  public function getBroadcastsProcessIncomingEmailsEnabled(): bool {
    return $this->getBoolValueByKey(SettingKey::BROADCASTS_PROCESS_INCOMING_EMAIL_ENABLED, false);
  }

  /**
   * @param bool $isEnabled
   * @return bool
   */
  public function setBroadcastsProcessIncomingEmailsEnabled(bool $isEnabled): bool {
    return $this->setBoolValueByKey(SettingKey::BROADCASTS_PROCESS_INCOMING_EMAIL_ENABLED, $isEnabled);
  }

  /**
   * @return bool
   */
  public function getBroadcastsProcessIncomingEmailsForwardingEnabled(): bool {
    return $this->getBoolValueByKey(SettingKey::BROADCASTS_PROCESS_INCOMING_EMAIL_FORWARDING_ENABLED, true);
  }

  /**
   * @param bool $isEnabled
   * @return bool
   */
  public function setBroadcastsProcessIncomingEmailsForwardingEnabled(bool $isEnabled): bool {
    return $this->setBoolValueByKey(SettingKey::BROADCASTS_PROCESS_INCOMING_EMAIL_FORWARDING_ENABLED, $isEnabled);
  }

  /**
   * @return string[]
   */
  public function getBroadcastsProcessIncomingEmailsForwardingEmailAddresses(): array {
    return $this->getStringArrayValuesByKey(SettingKey::BROADCASTS_PROCESS_INCOMING_EMAIL_FORWARDING_EMAIL_ADDRESSES);
  }

  /**
   * @param string[] $emailAddresses
   * @return string[]
   * @throws Exception
   */
  public function setBroadcastsProcessIncomingEmailsForwardingEmailAddresses(array $emailAddresses): array {
    return $this->setStringArrayValueByKey(
      SettingKey::BROADCASTS_PROCESS_INCOMING_EMAIL_FORWARDING_EMAIL_ADDRESSES,
      $emailAddresses
    );
  }

  /**
   * @return bool
   */
  public function getSeatReservationEnabled(): bool {
    return $this->getBoolValueByKey(SettingKey::SEAT_RESERVATION_ENABLED, false);
  }

  /**
   * @param bool $isEnabled
   * @return bool
   */
  public function setSeatReservationEnabled(bool $isEnabled): bool {
    return $this->setBoolValueByKey(SettingKey::SEAT_RESERVATION_ENABLED, $isEnabled);
  }

  /**
   * @return string
   */
  public function getUrl(): string {
    return $this->getStringValueByKey(SettingKey::URL, 'https://datepoll.org');
  }

  /**
   * @return string
   */
  public function getBackendUrl(): string {
    $url = $this->getUrl();
    if (StringHelper::contains($url, 'localhost')) {
      return 'http://localhost:9130';
    }

    $urlWithoutPort = explode(':', $url);

    return $urlWithoutPort[0] . ':' . $urlWithoutPort[1] . ':9230';
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
    return $this->getStringValueByKey(
      SettingKey::COMMUNITY_PRIVACY_POLICY,
      'You should provide your website privacy policy here.'
    );
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
   * @return stdClass
   */
  public function getAlert(): stdClass {
    $alert = new stdClass();
    $alert->message = $this->getStringValueByKey(SettingKey::COMMUNITY_ALERT, '');
    $alert->type = $this->getStringValueByKey(SettingKey::COMMUNITY_ALERT_TYPE, CommunityAlertTypes::NORMAL);

    return $alert;
  }

  /**
   * @param string $alertMessage
   * @param string $communityAlertType
   * @return stdClass
   */
  public function setAlert(string $alertMessage, string $communityAlertType): stdClass {
    $alert = new stdClass();
    $alert->message = $this->setStringValueByKey(SettingKey::COMMUNITY_ALERT, $alertMessage);
    $alert->type = $this->setStringValueByKey(SettingKey::COMMUNITY_ALERT_TYPE, $communityAlertType);

    return $alert;
  }

  /**
   * @return string
   */
  public function getJitsiInstanceUrl(): string {
    return $this->getStringValueByKey(SettingKey::JITSI_INSTANCE_URL, '');
  }

  /**
   * @param string $jitsiInstanceUrl
   * @return string
   */
  public function setJitsiInstanceUrl(string $jitsiInstanceUrl): string {
    return $this->setStringValueByKey(SettingKey::JITSI_INSTANCE_URL, $jitsiInstanceUrl);
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

  // -------------------------------------- Base methods --------------------------------------

  /**
   * @param string $settingKey
   * @param string $default
   * @return string
   */
  private function getStringValueByKey(string $settingKey, string $default): string {
    $setting = $this->getSettingValueByKey($settingKey);
    if ($setting == null) {
      $newSetting = new Setting([
        'key' => $settingKey,
        'value' => $default,]);

      $newSetting->save();

      return $newSetting->value;
    }

    return $setting->value;
  }

  /**
   * @param string $settingKey
   * @param string $value
   * @return string
   */
  private function setStringValueByKey(string $settingKey, string $value): string {
    Cache::forget('server.info');
    $setting = $this->getSettingValueByKey($settingKey);
    if ($setting == null) {
      $newSetting = new Setting([
        'key' => $settingKey,
        'value' => $value,]);

      $newSetting->save();

      return $newSetting->value;
    }

    $setting->value = $value;
    $setting->save();

    return $setting->value;
  }

  /**
   * @param string $settingKey
   * @param bool $default
   * @return bool
   */
  private function getBoolValueByKey(string $settingKey, bool $default): bool {
    $setting = $this->getSettingValueByKey($settingKey);

    if ($setting == null) {
      $valueToSave = Converter::booleanToString($default);

      $newSetting = new Setting([
        'key' => $settingKey,
        'value' => $valueToSave,]);

      $newSetting->save();

      return Converter::stringToBoolean($newSetting->value);
    }

    return Converter::stringToBoolean($setting->value);
  }

  /**
   * @param string $settingKey
   * @param bool $value
   * @return bool
   */
  private function setBoolValueByKey(string $settingKey, bool $value): bool {
    Cache::forget('server.info');
    $setting = $this->getSettingValueByKey($settingKey);

    $valueToSave = Converter::booleanToString($value);

    if ($setting == null) {
      $newSetting = new Setting([
        'key' => $settingKey,
        'value' => $valueToSave,]);

      $newSetting->save();

      return Converter::stringToBoolean($newSetting->value);
    }

    $setting->value = $valueToSave;
    $setting->save();

    return Converter::stringToBoolean($setting->value);
  }

  /**
   * @param string $settingKey
   * @param int $value
   * @return int
   */
  private function setIntegerValueByKey(string $settingKey, int $value): int {
    Cache::forget('server.info');
    $setting = $this->getSettingValueByKey($settingKey);

    $valueToSave = Converter::integerToString($value);

    if ($setting == null) {
      $newSetting = new Setting([
        'key' => $settingKey,
        'value' => $valueToSave,]);

      $newSetting->save();

      return Converter::stringToInteger($newSetting->value);
    }

    $setting->value = $valueToSave;
    $setting->save();

    return Converter::stringToInteger($setting->value);
  }

  /**
   * @param string $settingKey
   * @param int $default
   * @return int
   */
  private function getIntegerValueByKey(string $settingKey, int $default): int {
    $setting = $this->getSettingValueByKey($settingKey);

    if ($setting == null) {
      $newSetting = new Setting([
        'key' => $settingKey,
        'value' => Converter::integerToString($default),]);

      $newSetting->save();

      return Converter::stringToInteger($newSetting->value);
    }

    return Converter::stringToInteger($setting->value);
  }

  /**
   * @param string $settingKey
   * @param string[] $array
   * @return string[]
   * @throws Exception
   */
  private function setStringArrayValueByKey(string $settingKey, array $array): array {
    $oldArray = $this->getStringArrayByKey($settingKey);

    foreach ($array as $string) {
      $in = false;

      foreach ($oldArray as $oldString) {
        if (StringHelper::compareCaseSensitive($oldString->value, $string)) {
          $in = true;
          break;
        }
      }
      if (! $in) {
        $setting = new Setting(['key' => $settingKey, 'value' => $string]);
        $setting->save();
      }
    }

    foreach ($oldArray as $oldString) {
      $in = false;
      foreach ($array as $string) {
        if (StringHelper::compareCaseSensitive($oldString->value, $string)) {
          $in = true;
          break;
        }
      }
      if (! $in) {
        $oldString->delete();
      }
    }

    return $this->getStringArrayValuesByKey($settingKey);
  }

  /**
   * @param string $settingKey
   * @return array
   */
  private function getStringArrayValuesByKey(string $settingKey): array {
    return ArrayHelper::getPropertyArrayOfObjectArray(Setting::where('key', '=', $settingKey)->get()->all(), 'value');
  }

  /**
   * @param string $settingKey
   * @return Setting[]
   */
  private function getStringArrayByKey(string $settingKey): array {
    return Setting::where('key', '=', $settingKey)->get()->all();
  }

  /**
   * @param string $settingKey
   * @return Setting|null
   */
  private function getSettingValueByKey(string $settingKey): ?Setting {
    return Setting::where('key', '=', $settingKey)
      ->first();
  }
}
