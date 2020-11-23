<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\Controllers\Controller;
use App\Repositories\Cinema\Movie\IMovieRepository;
use App\Repositories\Cinema\MovieWorker\IMovieWorkerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovieWorkerController extends Controller {
  protected IMovieWorkerRepository $movieWorkerRepository;
  protected IMovieRepository $movieRepository;

  public function __construct(IMovieWorkerRepository $movieWorkerRepository, IMovieRepository $movieRepository) {
    $this->movieWorkerRepository = $movieWorkerRepository;
    $this->movieRepository = $movieRepository;
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function applyForWorker(Request $request, int $id) {
    /* Check if movie exists */
    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found', 'error_code' => 'movie_not_found'], 404);
    }

    if ((time() - (60 * 60 * 24)) > strtotime($movie->date . ' 20:00:00')) {
      return response()->json(['msg' => 'Movie already showed', 'error_code' => 'movie_already_shown'], 400);
    }

    if ($movie->worker() != null) {
      return response()->json(['msg' => 'There applied a worker already for this movie', 'error_code' => 'worker_already_applied'], 400);
    }

    $user = $request->auth;

    if ($this->movieWorkerRepository->setWorkerForMovie($user, $movie)) {
      return response()->json(['msg' => 'Successfully applied for worker'], 200);
    }

    return response()->json(['msg' => 'An error occurred during applying'], 500);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function signOutForWorker(Request $request, int $id) {
    /* Check if movie exists */
    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found', 'error_code' => 'movie_not_found'], 404);
    }

    if ((time() - (60 * 60 * 24)) > strtotime($movie->date . ' 20:00:00')) {
      return response()->json(['msg' => 'Movie already showed', 'error_code' => 'movie_already_shown'], 400);
    }

    if ($movie->worker() == null) {
      return response()->json(['msg' => 'No worker found for this movie', 'error_code' => 'no_worker_found_for_movie'], 400);
    }

    $user = $request->auth;

    if ($movie->worker_id != $user->id) {
      return response()->json(['msg' => 'You are not the worker for this movie', 'error_code' => 'not_the_worker_for_movie'], 400);
    }

    if ($this->movieWorkerRepository->removeWorkerFromMovie($movie)) {
      return response()->json(['msg' => 'Successfully signed out for worker'], 200);
    }

    return response()->json(['msg' => 'An error occurred during signing out'], 500);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function applyForEmergencyWorker(Request $request, int $id) {
    /* Check if movie exists */
    $movie = $this->movieRepository->getMovieById($id);

    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found', 'error_code' => 'movie_not_found'], 404);
    }

    if ((time() - (60 * 60 * 24)) > strtotime($movie->date . ' 20:00:00')) {
      return response()->json(['msg' => 'Movie already showed', 'error_code' => 'movie_already_shown'], 400);
    }

    if ($movie->emergencyWorker() != null) {
      return response()->json(['msg' => 'There applied a emergency worker already for this movie', 'error_code' => 'worker_already_applied'], 400);
    }

    $user = $request->auth;

    if ($this->movieWorkerRepository->setEmergencyWorkerForMovie($user, $movie)) {
      return response()->json(['msg' => 'Successfully applied for emergency worker'], 200);
    }

    return response()->json(['msg' => 'An error occurred during applying'], 500);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function signOutForEmergencyWorker(Request $request, int $id) {
    /* Check if movie exists */
    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found', 'error_code' => 'movie_not_found'], 404);
    }

    if ((time() - (60 * 60 * 24)) > strtotime($movie->date . ' 20:00:00')) {
      return response()->json(['msg' => 'Movie already showed', 'error_code' => 'movie_already_shown'], 400);
    }

    if ($movie->emergencyWorker() == null) {
      return response()->json(['msg' => 'No emergency worker found for this movie', 'error_code' => 'no_worker_found_for_movie'], 400);
    }

    $user = $request->auth;

    if ($movie->emergencyWorker()->id != $user->id) {
      return response()->json(['msg' => 'You are not the emergency worker for this movie', 'error_code' => 'not_the_worker_for_movie'], 400);
    }

    if ($this->movieWorkerRepository->removeEmergencyWorkerFromMovie($movie)) {
      return response()->json(['msg' => 'Successfully signed out for emergency worker'], 200);
    }

    return response()->json(['msg' => 'An error occurred during signing out'], 500);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getMovies(Request $request) {
    $user = $request->auth;

    $movies = $this->movieWorkerRepository->getMoviesWhereUserAppliedAsWorker($user);

    return response()->json(['msg' => 'Booked tickets for your movie service', 'movies' => $movies], 200);
  }
}
