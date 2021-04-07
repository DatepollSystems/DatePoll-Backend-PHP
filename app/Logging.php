<?php

namespace App;

use Illuminate\Support\Facades\Log;

class Logging {
  /**
   * @param string $function
   * @param string $message
   * @param array $context
   */
  public static function info(string $function, string $message, array $context = []): void {
    Log::info($function . ' | ' . $message, $context);
  }

  /**
   * @param string $function
   * @param string $message
   * @param array $context
   */
  public static function warning(string $function, string $message, array $context = []): void {
    Log::warning($function . ' | ' . $message, $context);
  }

  /**
   * @param string $function
   * @param string $message
   * @param array $context
   */
  public static function error(string $function, string $message, array $context = []): void {
    Log::error($function . ' | ' . $message, $context);
  }
}
