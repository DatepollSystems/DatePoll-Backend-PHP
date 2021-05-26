<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface IHasYearsRepository {
  /**
   * @return int[]
   */
  public function getYears(): array;

  /**
   * @param int|null $year
   * @return Model[]
   */
  public function getDataOrderedByDate(int $year = null): array;
}
