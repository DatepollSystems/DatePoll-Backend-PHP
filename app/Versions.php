<?php

namespace App;

class Versions
{
  private static $application_version_string = '0.8.2-a';
  private static $application_version = 18;

  private static $database_version = 4;

  /**
   * @return string
   */
  public static function getApplicationVersionString() : string {
    return Versions::$application_version_string;
  }

  /**
   * @return int
   */
  public static function getApplicationVersion() : int {
    return Versions::$application_version;
  }

  /**
   * @return int
   */
  public static function getApplicationDatabaseVersion(): int {
    return Versions::$database_version;
  }
}