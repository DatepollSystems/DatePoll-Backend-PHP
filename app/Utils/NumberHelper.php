<?php

namespace App\Utils;

abstract class NumberHelper {

  /**
   * @param int $int1
   * @param int $int2
   * @return bool
   */
  public static function equalsInteger(int $int1, int $int2): bool {
    return $int1 == $int2;
  }

  /**
   * @param int $int1
   * @param int $int2
   * @return bool
   */
  public static function notEqualsInteger(int $int1, int $int2): bool {
    return $int1 != $int2;
  }
}
