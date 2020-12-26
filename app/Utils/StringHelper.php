<?php

namespace App\Utils;

abstract class StringHelper {

  /**
   * @param string|null $string
   * @param string|null $keyword
   * @return bool Returns <code>true</code> if the string contains the keyword. If string or keyword is empty or
   *   <code>null</code> returns <code>false</code>.
   */
  public static function contains(?string $string, ?string $keyword): bool {
    if (! self::notNull($string) || ! self::notNull($keyword)) {
      return false;
    }

    return str_contains($string, $keyword);
  }

  /**
   * @param string|null $string
   * @return int Returns length of string. If string is <code>null</code> or empty returns <code>0</code>.
   */
  public static function length(?string $string): int {
    if (! self::notNull($string)) {
      return 0;
    }

    return strlen($string);
  }

  /**
   * @param string|null $string
   * @param string|null $substring
   * @return int Returns how often the substring occurs in the string. If string or substring is empty or
   *   <code>null</code> returns <code>0</code>.
   */
  public static function countSubstring(?string $string, ?string $substring): int {
    if (! self::notNull($string) || ! self::notNull($substring)) {
      return 0;
    }

    return substr_count($string, $substring);
  }

  /**
   * @param string|null $string
   * @return bool Returns <code>false</code> if string is null or empty, otherwise <code>true</code>.
   */
  public static function notNullAndEmpty(?string $string): bool {
    return ! self::trim($string) === '' && ! self::trim($string) === 'NaN';
  }

  /**
   * @param string|null $string $string
   * @return bool Returns <code>false</code> if string is null, otherwise <code>true</code>.
   */
  public static function notNull(?string $string): bool {
    return isset($string);
  }

  /**
   * @param string|null $string
   * @return string|null Converters string to lower case. If string is <code>null</code> returns <code>null</code>.
   */
  public static function toLowerCase(?string $string): ?string {
    return self::notNull($string) ? strtolower($string) : $string;
  }

  /**
   * @param string|null $string
   * @return string|null Converters string to upper case. If string is <code>null</code> returns <code>null</code>.
   */
  public static function toUpperCase(?string $string): ?string {
    return self::notNull($string) ? strtoupper($string) : $string;
  }

  /**
   * @param string|null $string
   * @return string|null Trims string and returns it. If string is <code>null</code> returns <code>null</code>.
   */
  public static function trim(?string $string): ?string {
    return self::notNull($string) ? trim($string) : $string;
  }

  /**
   * @param string|null $string
   * @return string|null Trims string, converts it to lower case and returns it. If string is <code>null</code> returns
   *   <code>null</code>.
   */
  public static function toLowerCaseWithTrim(?string $string): ?string {
    return self::toLowerCase(self::trim($string));
  }

  /**
   * @param string|null $string
   * @return string|null Trims string, converts it to upper case and returns it. If string is <code>null</code> returns
   *   <code>null</code>.
   */
  public static function toUpperCaseWithTrim(?string $string): ?string {
    return self::toUpperCase(self::trim($string));
  }
}
