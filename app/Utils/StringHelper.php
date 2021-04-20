<?php

namespace App\Utils;

use JetBrains\PhpStorm\Pure;

abstract class StringHelper {

  /**
   * @param string|null $string
   * @param string|null $keyword
   * @return bool Returns <code>true</code> if the string contains the keyword. If string or keyword is empty or
   *   <code>null</code> returns <code>false</code>.
   * @noinspection PhpPureFunctionMayProduceSideEffectsInspection (Because it's 100% pure)
   */
  #[Pure]
  public static function contains(?string $string, ?string $keyword): bool {
    if (self::null($string) || self::null($keyword)) {
      return false;
    }

    return str_contains($string, $keyword);
  }

  /**
   * @param string|null $string
   * @return int Returns length of string. If string is <code>null</code> or empty returns <code>0</code>.
   */
  #[Pure]
  public static function length(?string $string): int {
    if (self::nullAndEmpty($string)) {
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
  #[Pure]
  public static function countSubstring(?string $string, ?string $substring): int {
    if (self::null($string) || self::null($substring)) {
      return 0;
    }

    return substr_count($string, $substring);
  }

  /**
   * @param string|null $string
   * @return bool Returns <code>false</code> if string is null or empty, otherwise <code>true</code>.
   */
  #[Pure]
  public static function notNullAndEmpty(?string $string): bool {
    return (self::trim($string) != '' && self::trim($string) != 'NaN');
  }

  /**
   * @param string|null $string
   * @return bool Returns <code>true</code> if string is null or empty, otherwise <code>false</code>.
   */
  #[Pure]
  public static function nullAndEmpty(?string $string): bool {
    return ! self::notNullAndEmpty($string);
  }

  /**
   * @param string|null $string $string
   * @return bool Returns <code>false</code> if string is null, otherwise <code>true</code>.
   */
  #[Pure]
  public static function notNull(?string $string): bool {
    return isset($string);
  }

  /**
   * @param string|null $string $string
   * @return bool Returns <code>true</code> if string is null, otherwise <code>false</code>.
   */
  #[Pure]
  public static function null(?string $string): bool {
    return ! self::notNull($string);
  }

  /**
   * @param string|null $string
   * @return string|null Converters string to lower case. If string is <code>null</code> returns <code>null</code>.
   */
  #[Pure]
  public static function toLowerCase(?string $string): ?string {
    return self::notNull($string) ? strtolower($string) : $string;
  }

  /**
   * @param string|null $string
   * @return string|null Converters string to upper case. If string is <code>null</code> returns <code>null</code>.
   */
  #[Pure]
  public static function toUpperCase(?string $string): ?string {
    return self::notNull($string) ? strtoupper($string) : $string;
  }

  /**
   * @param string|null $string
   * @return string|null Trims string and returns it. If string is <code>null</code> returns <code>null</code>.
   */
  #[Pure]
  public static function trim(?string $string): ?string {
    return self::notNull($string) ? trim($string) : $string;
  }

  /**
   * @param string|null $string
   * @return string|null Trims string, converts it to lower case and returns it. If string is <code>null</code> returns
   *   <code>null</code>.
   */
  #[Pure]
  public static function toLowerCaseWithTrim(?string $string): ?string {
    return self::toLowerCase(self::trim($string));
  }

  /**
   * @param string|null $string
   * @return string|null Trims string, converts it to upper case and returns it. If string is <code>null</code> returns
   *   <code>null</code>.
   */
  #[Pure]
  public static function toUpperCaseWithTrim(?string $string): ?string {
    return self::toUpperCase(self::trim($string));
  }

  /**
   * @param string|null $string1
   * @param string|null $string2
   * @return bool If string1 or string2 is null returns <code>false</code>. If string1 and string2 are null returns
   *   <code>true</code>. If strings match returns <code>true</code>. Otherwise <code>false</code>.
   */
  #[Pure]
  public static function equalsCaseSensitive(?string $string1, ?string $string2): bool {
    if (! $string1 && ! $string2) {
      return true;
    }

    if ((! $string1 && $string2) || ($string1 && ! $string2)) {
      return false;
    }

    return strcmp($string1, $string2) === 0;
  }

  /**
   * @param string|null $string1
   * @param string|null $string2
   * @return bool If string1 or string2 is null returns <code>false</code>. If string1 and string2 are null returns
   *   <code>true</code>. If strings match returns <code>true</code>. Otherwise <code>false</code>.
   */
  #[Pure]
  public static function equals(?string $string1, ?string $string2): bool {
    if (! $string1 && ! $string2) {
      return true;
    }

    if ((! $string1 && $string2) || ($string1 && ! $string2)) {
      return false;
    }

    return strcasecmp($string1, $string2) === 0;
  }

  /**
   * @param string|null $string1
   * @param string|null $string2
   * @return bool If string1 or string2 is null returns <code>true</code>. If string1 and string2 are null returns
   *   <code>false</code>. If strings don't match returns <code>true</code>. Otherwise <code>false</code>.
   */
  #[Pure]
  public static function notEquals(?string $string1, ?string $string2): bool {
    return ! self::equals($string1, $string2);
  }

  /**
   * @param string $string
   * @param string $char
   * @return bool
   */
  public static function startsWithCharacter(string $string, string $char): bool {
    return str_starts_with($string, $char);
  }

  /**
   * @param string $string
   * @param string $pattern
   * @param string $replacement
   * @return string
   */
  private static function removeRegularExpression(string $string, string $pattern, string $replacement): string {
    return preg_replace($pattern, $replacement, $string);
  }

  /**
   * @param string $string
   * @return string
   */
  public static function removeImageHtmlTag(string $string): string {
    return self::removeRegularExpression($string, '/<img[^>]+>/i', ' (image) ');
  }
}
