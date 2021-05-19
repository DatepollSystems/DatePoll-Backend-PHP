<?php

namespace App\Utils;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
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

  /**
   * @param array $array
   * @param mixed $toAdd
   * @return array
   */
  #[Pure]
  public static function addToArrayIfNotInIt(array $array, mixed $toAdd): array {
    if (self::notInArray($array, $toAdd)) {
      $array[] = $toAdd;
    }

    return $array;
  }

  /**
   * @param Collection $results
   * @param int $pageSize
   * @return LengthAwarePaginator
   */
  public static function paginate(Collection $results, int $pageSize): LengthAwarePaginator {
    $page = Paginator::resolveCurrentPage('page');

    $total = $results->count();

    return self::paginator($results->forPage($page, $pageSize), $total, $pageSize, $page, [
      'path' => Paginator::resolveCurrentPath(),
      'pageName' => 'page',
    ]);
  }

  /**
   * Create a new length-aware paginator instance.
   *
   * @param Collection $items
   * @param int $total
   * @param int $perPage
   * @param int $currentPage
   * @param array $options
   * @return LengthAwarePaginator
   */
  protected static function paginator(Collection $items, int $total, int $perPage, int $currentPage, array $options): LengthAwarePaginator {
    return Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
      'items',
      'total',
      'perPage',
      'currentPage',
      'options'
    ));
  }
}
