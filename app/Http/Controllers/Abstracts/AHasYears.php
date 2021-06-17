<?php

namespace App\Http\Controllers\Abstracts;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Interfaces\IHasYears;
use App\Repositories\Interfaces\IHasYearsRepository;
use App\Utils\Converter;
use App\Utils\EnvironmentHelper;
use App\Utils\StringHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

abstract class AHasYears extends Controller implements IHasYears {
  protected string $YEARS_CACHE_KEY = '';
  protected ?string $DATA_ORDERED_BY_DATE_WITH_YEAR_CACHE_KEY = null;
  protected bool $debug = false;

  /**
   * AHasYears constructor.
   * @param IHasYearsRepository $hasYearRepository
   */
  public function __construct(protected IHasYearsRepository $hasYearRepository) {
    $this->debug = EnvironmentHelper::isDebug();
  }

  /**
   * Resets the cache
   * @param string|null $year
   */
  protected function forgetCache(?string $year = null): void {
    Cache::forget($this->YEARS_CACHE_KEY);
    if (StringHelper::notNull($this->DATA_ORDERED_BY_DATE_WITH_YEAR_CACHE_KEY)) {
      if ($year != null) {
        Cache::forget($this->DATA_ORDERED_BY_DATE_WITH_YEAR_CACHE_KEY  . $year);
      } else {
        Cache::flush();
      }
    }
  }

  /**
   * @return JsonResponse
   */
  public function getYears(): JsonResponse {
    if (! $this->debug && Cache::has($this->YEARS_CACHE_KEY)) {
      $years = Cache::get($this->YEARS_CACHE_KEY);
    } else {
      $years = $this->hasYearRepository->getYears();
      // Time to live 3 hours
      Cache::put($this->YEARS_CACHE_KEY, $years, 3 * 60 * 60);
    }

    return response()->json(['msg' => 'List of all years', 'years' => $years]);
  }

  /**
   * @param string|null $year
   * @return JsonResponse
   */
  public function getDataOrderedByDate(?string $year = null): JsonResponse {
    $iYear = null;
    if (StringHelper::notNull($year)) {
      $iYear = Converter::stringToInteger($year);
    }

    if (! $this->debug && StringHelper::notNull($this->DATA_ORDERED_BY_DATE_WITH_YEAR_CACHE_KEY)) {
      $cacheKey = $this->DATA_ORDERED_BY_DATE_WITH_YEAR_CACHE_KEY . $year;
      if (Cache::has($cacheKey)) {
        $data = Cache::get($cacheKey);
      } else {
        $data = $this->hasYearRepository->getDataOrderedByDate($iYear);
        // Time to live 3 hours
        Cache::put($cacheKey, $data, 3 * 60 * 60);
      }
    } else {
      $data = $this->hasYearRepository->getDataOrderedByDate($iYear);
    }

    return response()->json([
      'msg' => 'List of ' . get_class($this),
      'data' => $data,
      'year' => $iYear?: 'all', ]);
  }
}
