<?php

namespace App;

use App\Repositories\Log\LogRepository;

abstract class LogTypes
{
  const INFO = "INFO";
  const WARNING = "WARNING";
  const ERROR = "ERROR";
}

class Logging
{

  private static $logRepository = null;

  public static function info(string $function, string $message) {
    return self::log("INFO", $function, $message);
  }

  public static function warning(string $function, string $message) {
    return self::log("WARNING", $function, $message);
  }

  public static function error(string $function, string $message) {
    return self::log("ERROR", $function, $message);
  }

  private static function log(string $type, string $function, string $message) {
    if (self::$logRepository == null) {
      self::$logRepository = new LogRepository();
    }

    $message = $function . " | " . $message;

    return self::$logRepository->createLog($type, $message);
  }
}