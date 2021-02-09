<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\Cinema\Movie\IMovieRepository;
use App\Repositories\User\User\UserRepository;
use App\Utils\Converter;
use App\Utils\DateHelper;
use App\Utils\StringHelper;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MovieController extends Controller {
  private static string $YEARS_CACHE_KEY = 'movie.years';
  private static string $MOVIES_ORDERED_BY_DATE_WITH_YEAR_CACHE_KEY = 'movie.ordered.date.years.';

  public function __construct(protected IMovieRepository $movieRepository,
                              protected UserRepository $userRepository) {
  }

  /**
   * @return JsonResponse
   */
  public function getYearsOfMovies(): JsonResponse {
    if (Cache::has(self::$YEARS_CACHE_KEY)) {
      $years = Cache::get(self::$YEARS_CACHE_KEY);
    } else {
      $years = $this->movieRepository->getYearsOfMovies();
      // Time to live 3 hours
      Cache::put(self::$YEARS_CACHE_KEY, $years, 3 * 60 * 60);
    }

    return response()->json(['msg' => 'List of all years', 'years' => $years]);
  }

  /**
   * @param string|null $year
   * @return JsonResponse
   */
  public function getMoviesOrderedByDate(?string $year = null): JsonResponse {
    $iYear = null;
    if (StringHelper::notNull($year)) {
      $iYear = Converter::stringToInteger($year);
    }

    $cacheKey = self::$MOVIES_ORDERED_BY_DATE_WITH_YEAR_CACHE_KEY . Converter::integerToString($iYear);
    if (Cache::has($cacheKey)) {
      $movies = Cache::get($cacheKey);
    } else {
      $movies = $this->movieRepository->getAllMoviesOrderedByDate($iYear);
      // Time to live 3 hours
      Cache::put($cacheKey, $movies, 3 * 60 * 60);
    }

    return response()->json([
      'msg' => 'List of all movies',
      'movies' => $movies,
      'year' => $iYear]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'date' => 'required|date',
      'trailer_link' => 'required|max:190|min:1',
      'poster_link' => 'required|max:190|min:1',
      'booked_tickets' => 'integer|nullable',
      'maximal_tickets' => 'integer|nullable']);

    $movie = $this->movieRepository->createMovie($request->input('name'), $request->input('date'),
      $request->input('trailer_link'), $request->input('poster_link'), $request->input('booked_tickets'),
      $request->input('maximal_tickets'));

    if ($movie == null) {
      Logging::error('createMovie', 'User - ' . $request->auth->id . ' | Could not create movie');
      return response()->json(['msg' => 'An error occurred during movie creating!'], 500);
    }
    Logging::info('createMovie', 'User - ' . $request->auth->id . ' | New movie created - ' . $movie->id);
    Cache::forget(self::$YEARS_CACHE_KEY);
    Cache::forget(self::$MOVIES_ORDERED_BY_DATE_WITH_YEAR_CACHE_KEY . DateHelper::getYearOfDate($movie->date));

    return response()->json([
      'msg' => 'Movie created',
      'movie' => $movie,], 201);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(AuthenticatedRequest $request, int $id): JsonResponse {
    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      Logging::warning('getSingleMovie', 'User - ' . $request->auth->id . ' | Movie - ' . $id . ' | Movie not found');

      return response()->json(['msg' => 'Movie not found'], 404);
    }

    $returnableMovie = $movie->toArray();
    $bookings = [];
    foreach ($movie->moviesBookings() as $moviesBooking) {
      $user = $moviesBooking->user;
      $booking = ['user_id' => $user->id,
                  'firstname' => $user->firstname,
                  'surname' => $user->surname,
                  'amount' => $moviesBooking->amount];
      $bookings[] = $booking;
    }

    $usersNotBooked = DB::select('SELECT id, firstname, surname FROM users WHERE users.id 
                                                                     NOT IN (SELECT mb.user_id FROM movies_bookings mb
                                                                     WHERE mb.movie_id = ' . $returnableMovie['id'] . ')');

    foreach ($usersNotBooked as $user) {
      $booking = ['user_id' => $user->id,
                  'firstname' => $user->firstname,
                  'surname' => $user->surname,
                  'amount' => 0];
      $bookings[] = $booking;
    }

    usort($bookings, static function ($a, $b) {
      return strcmp($b['amount'], $a['amount']);
    });

    $returnableMovie['bookings'] = $bookings;

    return response()->json([
      'msg' => 'Movie information',
      'movie' => $returnableMovie,]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(AuthenticatedRequest $request, int $id): JsonResponse {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'date' => 'required|date',
      'trailer_link' => 'required|max:190|min:1',
      'poster_link' => 'required|max:190|min:1',
      'booked_tickets' => 'integer|nullable',
      'maximal_tickets' => 'nullable|integer',]);

    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      Logging::warning('updateMovie', 'User - ' . $request->auth->id . ' | Movie - ' . $id . ' | Movie not found');

      return response()->json(['msg' => 'Movie does not exist'], 404);
    }

    $movie = $this->movieRepository->updateMovie($movie, $request->input('name'), $request->input('date'),
      $request->input('trailer_link'), $request->input('poster_link'), $request->input('booked_tickets'),
      $request->input('maximal_tickets'));

    if ($movie == null) {
      Logging::error('updateMovie', 'User . ' . $request->auth->id . ' | Could not update movie');
      return response()->json(['msg' => 'An error occurred during movie saving'], 500);
    }
    Logging::info('updateMovie', 'User - ' . $request->auth->id . ' | Movie updated - ' . $movie->id);
    Cache::forget(self::$YEARS_CACHE_KEY);
    Cache::forget(self::$MOVIES_ORDERED_BY_DATE_WITH_YEAR_CACHE_KEY . DateHelper::getYearOfDate($movie->date));

    return response()->json([
      'msg' => 'Movie updated',
      'movie' => $movie,], 201);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function delete(AuthenticatedRequest $request, int $id): JsonResponse {
    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      Logging::warning('deleteMovie', 'User - ' . $request->auth->id . ' | Movie - ' . $id . ' | Movie not found');

      return response()->json(['msg' => 'Movie not found'], 404);
    }

    Cache::forget(self::$YEARS_CACHE_KEY);
    Cache::forget(self::$MOVIES_ORDERED_BY_DATE_WITH_YEAR_CACHE_KEY . DateHelper::getYearOfDate($movie->date));

    if (! $this->movieRepository->deleteMovie($movie)) {
      Logging::error('deleteMovie', 'User - ' . $request->auth->id . ' | Movie - ' . $id . ' | Could not delete movie');

      return response()->json(['msg' => 'Movie deletion failed'], 500);
    }

    return response()->json(['msg' => 'Movie deleted',]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function getNotShownMovies(AuthenticatedRequest $request): JsonResponse {
    return response()->json([
      'msg' => 'List of not shown movies',
      'movies' => $this->movieRepository->getNotShownMoviesForUser($request->auth->id),]);
  }
}
