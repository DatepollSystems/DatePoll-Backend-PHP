<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\Cinema\Movie\IMovieRepository;
use App\Repositories\Cinema\MovieYear\IMovieYearRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MovieController extends Controller
{

  protected $movieRepository = null;
  protected $movieYearRepository = null;

  public function __construct(IMovieRepository $movieRepository, IMovieYearRepository $movieYearRepository) {
    $this->movieRepository = $movieRepository;
    $this->movieYearRepository = $movieYearRepository;
  }

  /**
   * Display a listing of the resource.
   *
   * @return JsonResponse
   */
  public function getAll() {
    $toReturnMovies = array();

    $movies = $this->movieRepository->getAllMoviesOrderedByDate();
    foreach ($movies as $movie) {
      $returnable = $movie->getReturnable();

      $returnable->view_movie = ['href' => 'api/v1/cinema/administration/movie/' . $movie->id, 'method' => 'GET'];
      $toReturnMovies[] = $returnable;
    }

    $response = ['msg' => 'List of all movies', 'movies' => $toReturnMovies];

    return response()->json($response);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'date' => 'required|date',
      'trailer_link' => 'required|max:190|min:1',
      'poster_link' => 'required|max:190|min:1',
      'booked_tickets' => 'integer',
      'movie_year_id' => 'required|integer']);

    $movieYearId = $request->input('movie_year_id');

    if (!$this->movieYearRepository->checkIfMovieYearExistsById($movieYearId)) {
      Logging::warning('createMovie', 'User - ' . $request->auth->id . ' | Tried to create new movie with non-existing movie_year_id - ' . $movieYearId);
      return response()->json(['msg' => 'Movie year does not exist'], 404);
    }

    $movie = $this->movieRepository->createMovie($request->input('name'), $request->input('date'), $request->input('trailer_link'), $request->input('poster_link'), $request->input('booked_tickets'), $movieYearId);

    if ($movie != null) {
      Logging::info('createMovie', 'User - ' . $request->auth->id . ' | New movie created - ' . $movie->id);

      $returnable = $movie->getReturnable();
      $returnable->view_movie = ['href' => 'api/v1/cinema/administration/movie/' . $movie->id, 'method' => 'GET'];

      $response = ['msg' => 'Movie created', 'movie' => $returnable];

      return response()->json($response, 201);
    }

    Logging::error('createMovie', 'User - ' . $request->auth->id . ' | Could not create movie');
    return response()->json(['msg' => 'An error occurred during movie creating!'], 500);
  }

  /**
   * Display the specified resource.
   *
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(Request $request, $id) {
    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      Logging::warning('getSingleMovie', 'User - ' . $request->auth->id . ' | Movie - ' . $id . ' | Movie not found');
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    $returnable = $movie->getAdminReturnable();

    $returnable->view_movies = ['href' => 'api/v1/cinema/administration/movie', 'method' => 'GET'];

    $response = ['msg' => 'Movie information', 'movie' => $returnable];
    return response()->json($response);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(Request $request, $id) {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'date' => 'required|date',
      'trailer_link' => 'required|max:190|min:1',
      'poster_link' => 'required|max:190|min:1',
      'booked_tickets' => 'integer',
      'movie_year_id' => 'required|integer']);

    $movieYearId = $request->input('movie_year_id');

    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      Logging::warning('updateMovie', 'User - ' . $request->auth->id . ' | Movie - ' . $id . ' | Movie not found');
      return response()->json(['msg' => 'Movie does not exist'], 404);
    }

    if (!$this->movieYearRepository->checkIfMovieYearExistsById($movieYearId)) {
      Logging::warning('updateMovie', 'User - ' . $request->auth->id . ' | Tried to update movie with non-existing movie_year_id - ' . $movieYearId);
      return response()->json(['msg' => 'Movie year does not exist'], 404);
    }

    $movie = $this->movieRepository->updateMovie($movie, $request->input('name'), $request->input('date'), $request->input('trailer_link'), $request->input('poster_link'), $request->input('booked_tickets'), $movieYearId);

    if ($movie != null) {
      $returnable = $movie->getReturnable();
      $returnable->view_movie = ['href' => 'api/v1/cinema/administration/movie/' . $movie->id, 'method' => 'GET'];

      return response()->json([
        'msg' => 'Movie updated',
        'movie' => $returnable], 201);
    }

    Logging::error('updateMovie', 'User . ' . $request->auth->id . ' | Could not update movie');
    return response()->json(['msg' => 'An error occurred during movie saving'], 500);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function delete(Request $request, $id) {
    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      Logging::warning('deleteMovie', 'User - ' . $request->auth->id . ' | Movie - ' . $id . ' | Movie not found');
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    if (!$this->movieRepository->deleteMovie($movie)) {
      Logging::error('deleteMovie', 'User - ' . $request->auth->id . ' | Movie - ' . $id . ' | Could not delete movie');
      return response()->json(['msg' => 'Movie deletion failed'], 500);
    }

    return response()->json([
      'msg' => 'Movie deleted',
      'create' => [
        'href' => 'api/v1/cinema/administration/movie',
        'method' => 'POST',
        'params' => 'name, date, trailer_link, poster_link, booked_tickets, movie_year_id']]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getNotShownMovies(Request $request) {
    $user = $request->auth;

    $returnableMovies = $this->movieRepository->getNotShownMoviesForUser($user);

    $response = ['msg' => 'List of not shown movies', 'movies' => $returnableMovies];

    return response()->json($response);
  }
}
