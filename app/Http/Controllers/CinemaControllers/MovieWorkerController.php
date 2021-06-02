<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\Cinema\Movie\IMovieRepository;
use App\Repositories\Cinema\MovieWorker\IMovieWorkerRepository;
use App\Utils\DateHelper;
use Illuminate\Http\JsonResponse;

class MovieWorkerController extends Controller {

  public function __construct(protected IMovieWorkerRepository $movieWorkerRepository,
                              protected IMovieRepository $movieRepository) {
  }

  /**
   * @return JsonResponse
   */
  private function movieAlreadyShownResponse(): JsonResponse {
    return response()->json(['msg' => 'Movie already showed', 'error_code' => 'movie_already_shown'], 400);
  }

  /**
   * @return JsonResponse
   */
  private function workerAlreadyApplied(): JsonResponse {
    return response()->json(['msg' => 'There applied a worker already for this movie',
                             'error_code' => 'worker_already_applied'], 400);
  }

  /**
   * @return JsonResponse
   */
  private function noWorkerFoundForThisMovie(): JsonResponse {
    return response()->json(['msg' => 'No worker found for this movie', 'error_code' => 'no_worker_found_for_movie'],
      400);
  }

  /**
   * @return JsonResponse
   */
  private function youAreNotTheWorkerForThisMovie(): JsonResponse {
    return response()->json(['msg' => 'You are not the worker for this movie',
                             'error_code' => 'not_the_worker_for_movie'], 400);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   */
  public function applyForWorker(AuthenticatedRequest $request, int $id): JsonResponse {
    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found', 'error_code' => 'movie_not_found'], 404);
    }

    if (DateHelper::firstTimestampIsAfterSecondOne(DateHelper::removeDayFromUnixTimestamp(),
      DateHelper::convertStringDateToUnixTimestamp($movie->date . ' 20:00:00'))) {
      return $this->movieAlreadyShownResponse();
    }

    if ($movie->worker_id != null) {
      return $this->workerAlreadyApplied();
    }

    $userId = $request->auth->id;
    if ($this->movieWorkerRepository->setWorkerForMovie($userId, $movie)) {
      Logging::info('MovieWorkerController@applyForWorker',
        'User - ' . $userId . ' applied for movie ' . $movie->id . ' as worker.');
      return response()->json(['msg' => 'Successfully applied for worker'], 200);
    }

    return response()->json(['msg' => 'An error occurred during applying'], 500);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   */
  public function signOutForWorker(AuthenticatedRequest $request, int $id): JsonResponse {
    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found', 'error_code' => 'movie_not_found'], 404);
    }

    if (DateHelper::firstTimestampIsAfterSecondOne(DateHelper::removeDayFromUnixTimestamp(),
      DateHelper::convertStringDateToUnixTimestamp($movie->date . ' 20:00:00'))) {
      return $this->movieAlreadyShownResponse();
    }

    if ($movie->worker_id == null) {
      return $this->noWorkerFoundForThisMovie();
    }

    $userId = $request->auth->id;
    if ($movie->worker_id != $userId) {
      return $this->youAreNotTheWorkerForThisMovie();
    }

    if ($this->movieWorkerRepository->removeWorkerFromMovie($movie)) {
      Logging::info('MovieWorkerController@signOutForWorker',
        'User - ' . $userId . ' signed out for movie ' . $movie->id . ' as worker.');
      return response()->json(['msg' => 'Successfully signed out for worker'], 200);
    }

    return response()->json(['msg' => 'An error occurred during signing out'], 500);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   */
  public function applyForEmergencyWorker(AuthenticatedRequest $request, int $id): JsonResponse {
    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found', 'error_code' => 'movie_not_found'], 404);
    }

    if (DateHelper::firstTimestampIsAfterSecondOne(DateHelper::removeDayFromUnixTimestamp(),
      DateHelper::convertStringDateToUnixTimestamp($movie->date . ' 20:00:00'))) {
      return $this->movieAlreadyShownResponse();
    }

    if ($movie->emergency_worker_id != null) {
      return $this->workerAlreadyApplied();
    }

    $userId = $request->auth->id;
    if ($this->movieWorkerRepository->setEmergencyWorkerForMovie($userId, $movie)) {
      Logging::info('MovieWorkerController@applyForEmergencyWorker',
        'User - ' . $userId . ' applied for movie ' . $movie->id . ' as emergency worker.');
      return response()->json(['msg' => 'Successfully applied for emergency worker'], 200);
    }

    return response()->json(['msg' => 'An error occurred during applying'], 500);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   */
  public function signOutForEmergencyWorker(AuthenticatedRequest $request, int $id): JsonResponse {
    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found', 'error_code' => 'movie_not_found'], 404);
    }

    if (DateHelper::firstTimestampIsAfterSecondOne(DateHelper::removeDayFromUnixTimestamp(),
      DateHelper::convertStringDateToUnixTimestamp($movie->date . ' 20:00:00'))) {
      return $this->movieAlreadyShownResponse();
    }

    if ($movie->emergency_worker_id == null) {
      return $this->noWorkerFoundForThisMovie();
    }

    $userId = $request->auth->id;
    if ($movie->emergency_worker_id != $userId) {
      return $this->youAreNotTheWorkerForThisMovie();
    }

    if ($this->movieWorkerRepository->removeEmergencyWorkerFromMovie($movie)) {
      Logging::info('MovieWorkerController@signOutForWorker',
        'User - ' . $userId . ' signed out for movie ' . $movie->id . ' as emergency worker.');
      return response()->json(['msg' => 'Successfully signed out for emergency worker'], 200);
    }

    return response()->json(['msg' => 'An error occurred during signing out'], 500);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function getMovies(AuthenticatedRequest $request): JsonResponse {
    return response()->json(['msg' => 'Booked tickets for your movie service',
                             'movies' => $this->movieWorkerRepository->getMoviesWhereUserAppliedAsWorker($request->auth)],
      200);
  }
}
