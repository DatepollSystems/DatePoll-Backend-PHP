<?php

namespace App\Http\Controllers\Interfaces;

use Illuminate\Http\JsonResponse;

interface IHasYears {
  /**
   * @return JsonResponse
   */
  public function getYears(): JsonResponse;

  /**
   * @param string|null $year
   * @return JsonResponse
   */
  public function getDataOrderedByDate(?string $year = null): JsonResponse;
}
