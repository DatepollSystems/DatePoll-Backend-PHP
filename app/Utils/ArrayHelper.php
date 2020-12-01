<?php

namespace App\Utils;

abstract class ArrayHelper {

  /**
   * @param array $array
   * @param mixed $toFind
   * @return bool
   */
  public static function inArray(array $array, $toFind) {
    return in_array($toFind, $array);
  }

  /**
   * @param array $array
   * @param string $propertyName
   * @return array
   */
  public static function getPropertyArrayOfObjectArray(array $array, string $propertyName) {
    return array_column($array, $propertyName);
  }
}
