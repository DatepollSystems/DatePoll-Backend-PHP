<?php

namespace App\Repositories\Interfaces;

use App\Utils\ArrayHelper;
use Illuminate\Support\Facades\DB;

/**
 * @template T of \Illuminate\Database\Eloquent\Model
 */
abstract class AHasYearsRepository implements IHasYearsRepository {

  /**
   * AHasYearsRepository constructor.
   * @param string $tableName Table name of table to be checked for years
   * @param string $datePropertyName Property name to be checked for years
   */
  public function __construct(private string $tableName, private string $datePropertyName = 'date') {
  }

  /**
   * @return int[]
   */
  public function getYears(): array {
    return ArrayHelper::getPropertyArrayOfObjectArray(
      DB::table($this->tableName)->orderBy($this->datePropertyName, 'DESC')->selectRaw('YEAR(' . $this->datePropertyName . ') as year')->get()->unique()->values()->toArray(),
      'year'
    );
  }

  /**
   * @param int|null $year
   * @return T[]
   */
  abstract public function getDataOrderedByDate(int $year = null): array;
}
