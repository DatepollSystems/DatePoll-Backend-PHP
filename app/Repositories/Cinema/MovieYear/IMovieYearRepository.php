<?php

namespace App\Repositories\Cinema\MovieYear;

use App\Models\Cinema\MovieYear;
use Exception;

interface IMovieYearRepository {
  /**
   * @param int $id
   * @return MovieYear|null
   */
  public function getMovieYearById(int $id): ?MovieYear;

  /**
   * @param int $id
   * @return bool
   */
  public function checkIfMovieYearExistsById(int $id): bool;

  /**
   * @return MovieYear[]
   */
  public function getMovieYearsOrderedByDate(): array;

  /**
   * @param int $year
   * @return MovieYear | null
   */
  public function createMovieYear(int $year): ?MovieYear;

  /**
   * @param MovieYear $movieYear
   * @param int $year
   * @return MovieYear | null
   */
  public function updateMovieYear(MovieYear $movieYear, int $year): ?MovieYear;

  /**
   * @param MovieYear $movieYear
   * @return bool
   * @throws Exception
   */
  public function deleteMovieYear(MovieYear $movieYear): bool;
}
