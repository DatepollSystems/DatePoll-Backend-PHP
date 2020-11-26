<?php

namespace App\Repositories\Cinema\MovieYear;

use App\Models\Cinema\MovieYear;
use Exception;
use Illuminate\Database\Eloquent\Collection;

interface IMovieYearRepository {
  /**
   * @param int $id
   * @return MovieYear
   */
  public function getMovieYearById(int $id);

  /**
   * @param int $id
   * @return bool
   */
  public function checkIfMovieYearExistsById(int $id): bool;

  /**
   * @return MovieYear[] | null | Collection
   */
  public function getMovieYearsOrderedByDate();

  /**
   * @param int $year
   * @return MovieYear | null
   */
  public function createMovieYear(int $year);

  /**
   * @param MovieYear $movieYear
   * @param int $year
   * @return MovieYear | null
   */
  public function updateMovieYear(MovieYear $movieYear, int $year);

  /**
   * @param MovieYear $movieYear
   * @return bool
   * @throws Exception
   */
  public function deleteMovieYear(MovieYear $movieYear): bool;
}
