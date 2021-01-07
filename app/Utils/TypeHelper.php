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
   * @param mixed $possibleInteger
   * @return bool
   */
  #[Pure]
  public static function isInteger(mixed $possibleInteger): bool {
    return is_integer($possibleInteger);
  }
}
