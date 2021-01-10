<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\Cinema\Movie\IMovieRepository;
use App\Utils\Converter;
use App\Utils\StringHelper;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class MovieController extends Controller {
  private static string $YEARS_CACHE_KEY = 'movie.years';

  public function __construct(protected IMovieRepository $movieRepository) {
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
    if (! StringHelper::notNull($year)) {
      $year = null;
    } else {
      $year = Converter::stringToInteger($year);
    }

    return response()->json([
      'msg' => 'List of all movies',
      'movies' => $this->movieRepository->getAllMoviesOrderedByDate($year),
      'year' => $year]);
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

    return response()->json([
      'msg' => 'Movie information',
      'movie' => $movie->getAdminReturnable(),]);
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
      $request->input('trailer_link'), $request->input('poster_link'), $request->input('booked_tickets'), $request->input('maximal_tickets'));

    if ($movie == null) {
      Logging::error('updateMovie', 'User . ' . $request->auth->id . ' | Could not update movie');
      return response()->json(['msg' => 'An error occurred during movie saving'], 500);
    }
    Logging::info('updateMovie', 'User - ' . $request->auth->id . ' | Movie updated - ' . $movie->id);
    Cache::forget(self::$YEARS_CACHE_KEY);

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

    if (! $this->movieRepository->deleteMovie($movie)) {
      Logging::error('deleteMovie', 'User - ' . $request->auth->id . ' | Movie - ' . $id . ' | Could not delete movie');

      return response()->json(['msg' => 'Movie deletion failed'], 500);
    }

    return response()->json([
      'msg' => 'Movie deleted',
      'create' => [
        'href' => 'api/v1/cinema/administration/movie',
        'method' => 'POST',
        'params' => 'name, date, trailer_link, poster_link, booked_tickets, movie_year_id',],]);
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
