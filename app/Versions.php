<?php

namespace App;

class Versions
{
  private static $application_version_string = '0.8.1';
  private static $application_version = 16;

  private static $database_version = 2;

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