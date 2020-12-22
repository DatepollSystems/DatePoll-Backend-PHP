<?php

namespace App\Utils;

abstract class Converter {

  /**
   * @param bool $boolean
   * @return string
   */
  public static function booleanToString(bool $boolean): string {
    return $boolean ? 'true' : 'false';
  }

  /**
   * @param string $string
   * @return bool
   */
  public static function stringToBoolean(string $string): bool {
    return $string === 'true';
  }

  /**
   * @param int $integer
   * @return string
   */
  public static function integerToString(int $integer): string {
    return strval($integer);
  }

  /**
   * @param string $string
   * @return int
   */
  public static function stringToInteger(string $string): int {
    return intval($string);
  }
}
