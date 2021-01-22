<?php

namespace App\Utils;

abstract class EnvironmentHelper {
  public static string $APP_NAME = 'APP_NAME';
  public static string $APP_ENV = 'APP_ENV';
  public static string $APP_DEBUG = 'APP_DEBUG';
  public static string $APP_TIMEZONE = 'APP_TIMEZONE';
  public static string $DB_CONNECTION = 'DB_CONNECTION';
  public static string $DB_HOST = 'DB_HOST';
  public static string $DB_PORT = 'DB_PORT';
  public static string $DB_DATABASE = 'DB_DATABASE';
  public static string $DB_USERNAME = 'DB_USERNAME';
  public static string $DB_PASSWORD = 'DB_PASSWORD';
  public static string $MAIL_DRIVER = 'MAIL_DRIVER';
  public static string $MAIL_PORT = 'MAIL_PORT';
  public static string $MAIL_INCOMING_PORT = 'MAIL_INCOMING_PORT';
  public static string $MAIL_ENCRYPTION = 'MAIL_ENCRYPTION';
  public static string $MAIL_FROM_NAME = 'MAIL_FROM_NAME';
  public static string $MAIL_HOST = 'MAIL_HOST';
  public static string $MAIL_USERNAME = 'MAIL_USERNAME';
  public static string $MAIL_PASSWORD = 'MAIL_PASSWORD';
  public static string $MAIL_FROM_ADDRESS = 'MAIL_FROM_ADDRESS';
  public static string $MAIL_REPLY_ADDRESS = 'MAIL_REPLY_ADDRESS';
  public static string $APP_KEY = 'APP_KEY';
  public static string $JWT_SECRET = 'JWT_SECRET';

  /**
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  public static function getEnvironmentVariable(string $key, mixed $default): mixed {
    return env($key, $default);
  }

  /**
   * @param string $key
   * @param mixed $value
   */
  public static function setEnvironmentVariable(string $key, mixed $value): void {
    $path = base_path('.env');

    if (TypeHelper::isBoolean(env($key))) {
      $old = Converter::stringToBoolean(env($key));
    } elseif (env($key) === null) {
      $old = 'null';
    } else {
      $old = '"'.env($key).'"';
    }

    if (TypeHelper::isBoolean($value)) {
      $value = Converter::booleanToString($value);
    } elseif ($value === null) {
      $value = 'null';
    } else {
      $value = '"'.$value.'"';
    }

    if (file_exists($path)) {
      file_put_contents($path, str_replace("$key=" . $old, "$key=" . $value, file_get_contents($path)));
    }
  }

  /**
   * @return bool
   */
  public static function isDebug(): bool {
    return env(self::$APP_DEBUG, false);
  }

  /**
   * @return bool
   */
  public static function isProduction(): bool {
    return ! self::isDebug();
  }
}
