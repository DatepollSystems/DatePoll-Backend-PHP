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
    return in_array($toFind, $array, true);
  }

  /**
   * @param array $array
   * @param mixed $toFind
   * @return bool
   */
  #[Pure]
  public static function notInArray(array $array, mixed $toFind): bool {
    return ! self::inArray($array, $toFind);
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
    return count($array);
  }

  /**
   * @param mixed $possibleArray
   * @return bool
   */
  #[Pure]
  public static function isArray(mixed $possibleArray): bool {
    return is_array($possibleArray);
  }

  /**
   * @param mixed $possibleArray
   * @return bool
   */
  #[Pure]
  public static function isNotArray(mixed $possibleArray): bool {
    return ! self::isArray($possibleArray);
  }

//  /**
//   * @param array $array
//   * @param mixed $toAdd
//   * @return mixed
//   */
//  #[Pure]
//  public static function addToArrayIfNotInIt(array $array, mixed $toAdd): mixed {
//    if (! self::inArray($array, $toAdd)) {
//      return $toAdd;
//    }
//  }
}
