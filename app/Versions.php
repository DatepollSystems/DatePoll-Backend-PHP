<?php

namespace App;

class Versions {
  private static string $application_version_string = '0.12.0';
  private static int $application_version = 27;

  private static int $database_version = 8;

  /**
   * @return string
   */
  public static function getApplicationVersionString(): string {
    return self::$application_version_string;
  }

  /**
   * @return int
   */
  public static function getApplicationVersion(): int {
    return self::$application_version;
  }

  /**
   * @return int
   */
  public static function getApplicationDatabaseVersion(): int {
    return self::$database_version;
  }
}
