<?php

namespace App\Utils;

use DateInterval;
use DateTime;
use Exception;
use JetBrains\PhpStorm\Pure;

abstract class DateHelper {

  /**
   * @param string $timestamp
   * @return int
   */
  #[Pure]
  public static function convertStringDateToUnixTimestamp(string $timestamp): int {
    return strtotime($timestamp);
  }

  /**
   * @param int|string|null $timestamp
   * @return string
   */
  #[Pure]
  private static function getDateFormatted(int|string|null $timestamp = null): string {
    $timestamp = self::getUnixTimestampTypeChecked($timestamp);

    return date('Y-m-d H:i:s', $timestamp);
  }

  /**
   * @param int|string|null $timestamp
   * @return int|null
   */
  #[Pure]
  private static function getUnixTimestampTypeChecked(int|string|null $timestamp): int|null {
    if ($timestamp != null && TypeHelper::isNotInteger($timestamp)) {
      return self::convertStringDateToUnixTimestamp($timestamp);
    }
    return $timestamp;
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

  /**
   * @return int
   */
  public static function getCurrentUnixTimestamp(): int {
    return time();
  }

  /**
   * @param int|null $time Default <code>null</code>, uses DateHelper::getCurrentUnixTimestamp()
   * @param int $day Defaults to <code>1</code>
   * @return int
   */
  public static function removeDayFromUnixTimestamp(?int $time = null, int $day = 1): int {
    if ($time == null) {
      $time = self::getCurrentUnixTimestamp();
    }
    return $time - (60 * 60 * 24 * $day);
  }

  /**
   * @param int|string $timestamp0
   * @param int|string $timestamp1
   * @return bool Returns <code>true</code> if first timestamp is smaller / before second timestamp. Otherwise returns
   *   <code>false</code>.
   */
  #[Pure]
  public static function ifFirstTimestampIsBeforeSecondOne(int|string $timestamp0, int|string $timestamp1): bool {
    if (TypeHelper::isNotInteger($timestamp0)) {
      $timestamp0 = self::convertStringDateToUnixTimestamp($timestamp0);
    }
    if (TypeHelper::isNotInteger($timestamp1)) {
      $timestamp1 = self::convertStringDateToUnixTimestamp($timestamp1);
    }
    return $timestamp0 < $timestamp1;
  }

  /**
   * @param int|string $timestamp0
   * @param int|string $timestamp1
   * @return bool Returns <code>true</code> if first timestamp is bigger / after second timestamp. Otherwise returns
   *   <code>false</code>.
   */
  #[Pure]
  public static function ifFirstTimestampIsAfterSecondOne(int|string $timestamp0, int|string $timestamp1): bool {
    return ! self::ifFirstTimestampIsBeforeSecondOne($timestamp0, $timestamp1);
  }

  /**
   * @param int|string|null $time
   * @return int
   */
  #[Pure]
  public static function getYearOfDate(int|string|null $time = null): int {
    $time = self::getUnixTimestampTypeChecked($time);
    return (int) date('Y', $time);
  }
}
