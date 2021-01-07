<?php

namespace App\Repositories\Cinema\MovieYear;

use App\Models\Cinema\MovieYear;
use Exception;

class MovieYearRepository implements IMovieYearRepository {
  /**
   * @param int $id
   * @return MovieYear|null
   */
  public function getMovieYearById(int $id): ?MovieYear {
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
   * @return MovieYear[]
   */
  public function getMovieYearsOrderedByDate(): array {
    return MovieYear::orderBy('year')
      ->get()->all();
  }

  /**
   * @param int $year
   * @return MovieYear | null
   */
  public function createMovieYear(int $year): ?MovieYear {
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
  public function updateMovieYear(MovieYear $movieYear, int $year): ?MovieYear {
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
