<?php

namespace App\Utils;

use JetBrains\PhpStorm\Pure;

abstract class ArrayHelper {

  /**
   * @param array $array
   * @param mixed $toFind
   * @return bool
   */
  #[Pure]
  public static function inArray(array $array, mixed $toFind): bool {
    return in_array($toFind, $array);
  }

  /**
   * @param array $array
   * @param string $propertyName
   * @return array
   */
  #[Pure]
  public static function getPropertyArrayOfObjectArray(array $array, string $propertyName): array {
    return array_column($array, $propertyName);
  }

  /**
   * @param array $array
   * @return int
   */
  #[Pure]
  public static function getCount(array $array): int {
    return sizeof($array);
  }

  /**
   * @param $possibleArray
   * @return bool
   */
  #[Pure]
  public static function isArray($possibleArray): bool {
    return is_array($possibleArray);
  }
}
