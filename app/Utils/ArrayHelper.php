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
   * @param array|null $array
   * @return int
   */
  #[Pure]
  public static function getSize(?array $array): int {
    return $array ? count($array) : 0;
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

  /**
   * @param mixed $toFind
   * @param array $array
   * @return array
   */
  #[Pure]
  public static function addToArrayIfNotInIt(mixed $toFind, array $array): array {
    if (self::notInArray($array, $toFind)) {
      $array[] = $toFind;
    }

    return $array;
  }
}
