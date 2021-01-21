<?php

namespace App\Repositories\Cinema\MovieWorker;

use App\Models\Cinema\Movie;
use App\Models\User\User;

interface IMovieWorkerRepository {
  /**
   * @param User $worker
   * @param Movie $movie
   * @return bool
   */
  public function setWorkerForMovie(User $worker, Movie $movie): bool;

  /**
   * @param User $emergencyWorker
   * @param Movie $movie
   * @return bool
   */
  public function setEmergencyWorkerForMovie(User $emergencyWorker, Movie $movie): bool;

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
