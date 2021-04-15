<?php

namespace App\Repositories\Cinema\Movie;

use App\Models\Cinema\Movie;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use App\Utils\ArrayHelper;
use App\Utils\DateHelper;
use Exception;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;

class MovieRepository implements IMovieRepository {

  public function __construct(protected IUserSettingRepository $userSettingRepository) {
  }

  /**
   * @param int $id
   * @return Movie|null
   */
  public function getMovieById(int $id): ?Movie {
    return Movie::find($id);
  }

  /**
   * @return int[]
   */
  public function getYearsOfMovies(): array {
    return ArrayHelper::getPropertyArrayOfObjectArray(
      DB::table('movies')->orderBy('date')->selectRaw('YEAR(date) as year')->get()->unique()->values()->toArray(),
      'year'
    );
  }

  /**
   * @param int|null $year
   * @return Movie[]
   */
  public function getAllMoviesOrderedByDate(int $year = null): array {
    if ($year != null) {
      return Movie::whereYear('date', '=', $year)->orderBy('date')->get()->all();
    }

    return Movie::orderBy('date')->get()->all();
  }

  /**
   * @param string $date
   * @return Movie[]
   */
  private function getMoviesAfterDate(string $date): array {
    return Movie::where(
      'date',
      '>',
      $date
    )->orderBy('date')->get()->all();
  }

  /**
   * @param string $name
   * @param string $date
   * @param string $trailerLink
   * @param string $posterLink
   * @param int $bookedTickets
   * @param int $maximalTickets
   * @return Movie|null
   */
  public function createMovie(
    string $name,
    string $date,
    string $trailerLink,
    string $posterLink,
    int $bookedTickets = 0,
    int $maximalTickets = 20
  ): ?Movie {
    $movie = new Movie([
      'name' => $name,
      'date' => $date,
      'trailerLink' => $trailerLink,
      'posterLink' => $posterLink,
      'bookedTickets' => $bookedTickets,
      'maximalTickets' => $maximalTickets,]);

    if ($movie->save()) {
      return $movie;
    }

    return null;
  }

  /**
   * @param Movie $movie
   * @param string $name
   * @param string $date
   * @param string $trailerLink
   * @param string $posterLink
   * @param int $bookedTickets
   * @param int $movieYearId
   * @return Movie|null
   */
  public function updateMovie(
    Movie $movie,
    string $name,
    string $date,
    string $trailerLink,
    string $posterLink,
    int $bookedTickets,
    int $movieYearId
  ): ?Movie {
    $movie->name = $name;
    $movie->date = $date;
    $movie->trailerLink = $trailerLink;
    $movie->posterLink = $posterLink;
    $movie->bookedTickets = $bookedTickets;
    $movie->maximalTickets = $movieYearId;

    if ($movie->save()) {
      return $movie;
    } else {
      return null;
    }
  }

  /**
   * @param Movie $movie
   * @return bool
   * @throws Exception
   */
  public function deleteMovie(Movie $movie): bool {
    return $movie->delete();
  }

  /**
   * @param int $userId
   * @return array
   */
  #[ArrayShape(["id" => "int", 'name' => "string", 'date' => "string", 'trailer_link' => "string", 'poster_link' => "string", 'booked_tickets' => "int", 'movie_year_id' => "int", 'created_at' => "string", 'updated_at' => "string", 'booked_tickets_for_yourself' => 'int'])]
  public function getNotShownMoviesForUser(int $userId): array {
    $date = DateHelper::removeDayFromDateFormatted(DateHelper::getCurrentDateFormatted(), 1);

    $returnableMovies = [];
    foreach ($this->getMoviesAfterDate($date) as $movie) {
      $returnable = $movie->toArray();
      $returnable['booked_tickets_for_yourself'] = $movie->getBookedTicketsForUser($userId);

      if ($movie->worker_id == null) {
        $returnable[Movie::$workerNumberProperty] = [];
      } else if (! $this->userSettingRepository->getShareMovieWorkerPhoneNumber($movie->worker_id) || ! $this->userSettingRepository->getShareMovieWorkerPhoneNumber($userId)) {
        $returnable[Movie::$workerNumberProperty] = [];
      }

      if ($movie->emergency_worker_id == null) {
        $returnable[Movie::$emergencyWorkerNumberProperty] = [];
      } else if (! $this->userSettingRepository->getShareMovieWorkerPhoneNumber($movie->emergency_worker_id) || ! $this->userSettingRepository->getShareMovieWorkerPhoneNumber($userId)) {
        $returnable[Movie::$emergencyWorkerNumberProperty] = [];
      }

      $returnableMovies[] = $returnable;
    }

    return $returnableMovies;
  }
}
