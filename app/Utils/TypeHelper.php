<?php

namespace App\Utils;

use JetBrains\PhpStorm\Pure;

abstract class TypeHelper {

  /**
   * @param mixed $possibleBoolean
   * @return bool
   */
  #[Pure]
  public static function isBoolean(mixed $possibleBoolean): bool {
    return is_bool($possibleBoolean);
  }

  /**
   * @param mixed $possibleBoolean
   * @return bool
   */
  #[Pure]
  public static function isNotBoolean(mixed $possibleBoolean): bool {
    return ! self::isBoolean($possibleBoolean);
  }

  /**
   * @param mixed $possibleInteger
   * @return bool
   */
  #[Pure]
  public static function isInteger(mixed $possibleInteger): bool {
    return is_int($possibleInteger);
  }

  /**
   * @param mixed $possibleInteger
   * @return bool
   */
  #[Pure]
  public static function isNotInteger(mixed $possibleInteger): bool {
    return ! self::isInteger($possibleInteger);
  }

  /**
   * @param mixed $possibleString
   * @return bool
   */
  #[Pure]
  public static function isString(mixed $possibleString): bool {
    return is_string($possibleString);
  }

  /**
   * @param mixed $possibleString
   * @return bool
   */
  #[Pure]
  public static function isNotString(mixed $possibleString): bool {
    return ! self::isInteger($possibleString);
  }
}
