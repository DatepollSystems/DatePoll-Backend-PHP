<?php

namespace App;

use App\Models\System\Log;

abstract class LogTypes
{
  const INFO = "INFO";
  const WARNING = "WARNING";
  const ERROR = "ERROR";
}

class Logging
{

  public static function info(string $function, string $message) {
    $message = $function . " | " . $message;

    $log = new Log([
      'type' => LogTypes::INFO,
      'message' => $message]);

    return $log->save();
  }

  public static function warning(string $function, string $message) {
    $message = $function . " | " . $message;

    $log = new Log([
      'type' => LogTypes::WARNING,
      'message' => $message]);

    return $log->save();
  }

  public static function error(string $function, string $message) {
    $message = $function . " | " . $message;

    $log = new Log([
      'type' => LogTypes::ERROR,
      'message' => $message]);

    return $log->save();
  }
}