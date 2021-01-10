<?php

namespace App\Utils;

use Exception;
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

  /**
   * @param int $integer
   * @return bool
   * @throws Exception
   */
  public static function integerToBoolean(int $integer): bool {
    if ($integer != 0 && $integer != 1) {
      throw new Exception('Integer is not 0 or 1');
    }

    return (bool) $integer;
  }
}
