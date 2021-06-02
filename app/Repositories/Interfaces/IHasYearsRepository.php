<?php

namespace App\Repositories\Interfaces;

/**
 * @template T of \Illuminate\Database\Eloquent\Model
 */
interface IHasYearsRepository {
  /**
   * @return int[]
   */
  public function getYears(): array;

  /**
   * @param int|null $year
   * @return T[]
   */
  public function getDataOrderedByDate(int $year = null): array;
}
