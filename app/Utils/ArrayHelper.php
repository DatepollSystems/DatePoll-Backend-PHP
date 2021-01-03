<?php

namespace App\Utils;

abstract class ArrayHelper {

  /**
   * @param array $array
   * @param mixed $toFind
   * @return bool
   */
  public static function inArray(array $array, $toFind): bool {
    return in_array($toFind, $array);
  }

  /**
   * @param array $array
   * @param string $propertyName
   * @return array
   */
  public static function getPropertyArrayOfObjectArray(array $array, string $propertyName): array {
    return array_column($array, $propertyName);
  }

  /**
   * @param array $array
   * @return int
   */
  public static function getCount(array $array): int {
    return sizeof($array);
  }

  /**
   * @param $possibleArray
   * @return bool
   */
  public static function isArray($possibleArray): bool {
    return is_array($possibleArray);
  }
}
