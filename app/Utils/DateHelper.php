<?php

namespace App\Utils;

use DateInterval;
use DateTime;
use Exception;
use JetBrains\PhpStorm\Pure;

abstract class DateHelper {
  /**
   * @param int|string|null $timestamp
   * @return string
   */
  #[Pure]
  private static function getDateFormatted(int|string|null $timestamp = null): string {
    if ($timestamp != null && ! TypeHelper::isInteger($timestamp)) {
      $timestamp = (int) strtotime($timestamp);
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

  /**
   * @return DateTime
   */
  public static function getCurrentDateTime(): DateTime {
    return new DateTime();
  }

  /**
   * @param DateTime $dateTime
   * @param int $minute
   * @return DateTime
   * @throws Exception
   */
  public static function addMinuteToDateTime(DateTime $dateTime, int $minute): DateTime {
    return $dateTime->add(new DateInterval('PT' . $minute . 'M'));
  }
}
