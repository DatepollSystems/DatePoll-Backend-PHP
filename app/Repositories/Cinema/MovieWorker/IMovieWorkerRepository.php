<?php

namespace App\Repositories\Cinema\MovieWorker;

use App\Models\Cinema\Movie;
use App\Models\User\User;

interface IMovieWorkerRepository {
  /**
   * @param int $workerId
   * @param Movie $movie
   * @return bool
   */
  public function setWorkerForMovie(int $workerId, Movie $movie): bool;

  /**
   * @param int $emergencyWorkerId
   * @param Movie $movie
   * @return bool
   */
  public function setEmergencyWorkerForMovie(int $emergencyWorkerId, Movie $movie): bool;

  /**
   * @param Movie $movie
   * @return bool
   */
  public function removeWorkerFromMovie(Movie $movie): bool;

  /**
   * @param Movie $movie
   * @return bool
   */
  public function removeEmergencyWorkerFromMovie(Movie $movie): bool;

  /**
   * @param User $user
   * @return array
   */
  public function getMoviesWhereUserAppliedAsWorker(User $user): array;
}
