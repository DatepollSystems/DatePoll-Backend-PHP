<?php

namespace App\Utils;

use JetBrains\PhpStorm\Pure;

abstract class DateHelper {

  /**
   * @param int|string|null $timestamp
   * @return string
   */
  #[Pure]
  private static function getDateFormatted(int|string|null $timestamp = null): string {
    if (! TypeHelper::isInteger($timestamp) && $timestamp != null) {
      $timestamp = strtotime($timestamp);
    }

    return date('Y-m-d H:i:s', $timestamp);
  }

  /**
   * @return string
   */
  #[Pure]
  public static function getCurrentDateFormatted(): string {
    return self::getDateFormatted();
  }

  /**
   * @param string $formattedDate
   * @param int $day
   * @return string
   */
  #[Pure]
  public static function addDayToDateFormatted(string $formattedDate, int $day): string {
    return self::getDateFormatted($formattedDate . ' +' . Converter::integerToString($day) . ' day');
  }
}
