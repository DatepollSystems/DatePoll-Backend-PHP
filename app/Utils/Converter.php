<?php

namespace App\Utils;

use JetBrains\PhpStorm\Pure;

abstract class Converter {

  /**
   * @param bool $boolean
   * @return string
   */
  #[Pure]
  public static function booleanToString(bool $boolean): string {
    return $boolean ? 'true' : 'false';
  }

  /**
   * @param string $string
   * @return bool
   */
  #[Pure]
  public static function stringToBoolean(string $string): bool {
    return $string === 'true';
  }

  /**
   * @param int $integer
   * @return string
   */
  #[Pure]
  public static function integerToString(int $integer): string {
    return strval($integer);
  }

  /**
   * @param string $string
   * @return int
   */
  #[Pure]
  public static function stringToInteger(string $string): int {
    return intval($string);
  }
}
