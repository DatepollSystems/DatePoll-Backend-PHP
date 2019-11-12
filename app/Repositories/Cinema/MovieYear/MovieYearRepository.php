<?php

namespace App\Repositories\Cinema\MovieYear;

use App\Models\Cinema\MovieYear;
use Exception;

class MovieYearRepository implements IMovieYearRepository
{
  /**
   * @param int $id
   * @return MovieYear
   */
  public function getMovieYearById(int $id) {
    return MovieYear::find($id);
  }

  /**
   * @param int $id
   * @return bool
   */
  public function checkIfMovieYearExistsById(int $id): bool {
    return (MovieYear::find($id) != null);
  }

  /**
   * @return array
   */
  public function getMovieYearsOrderedByDate() {
    return MovieYear::orderBy('year')
                    ->get();
  }

  /**
   * @param int $year
   * @return MovieYear
   */
  public function createMovieYear(int $year) {
    $movieYear = new MovieYear(['year' => $year]);

    if ($movieYear->save()) {
      return $movieYear;
    } else {
      return null;
    }
  }

  /**
   * @param MovieYear $movieYear
   * @param int $year
   * @return MovieYear
   */
  public function updateMovieYear(MovieYear $movieYear, int $year) {
    $movieYear->year = $year;

    if ($movieYear->save()) {
      return $movieYear;
    } else {
      return null;
    }
  }

  /**
   * @param MovieYear $movieYear
   * @return bool
   * @throws Exception
   */
  public function deleteMovieYear(MovieYear $movieYear): bool {
    return $movieYear->delete();
  }
}