<?php

namespace App\Repositories\Cinema\MovieYear;

use App\Models\Cinema\MovieYear;
use Exception;
use Illuminate\Database\Eloquent\Collection;

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
   * @return MovieYear[] | null | Collection
   */
  public function getMovieYearsOrderedByDate() {
    return MovieYear::orderBy('year')
                    ->get();
  }

  /**
   * @param int $year
   * @return MovieYear | null
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
   * @return MovieYear | null
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