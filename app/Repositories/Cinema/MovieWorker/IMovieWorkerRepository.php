<?php

namespace App\Repositories\Cinema\MovieWorker;

use App\Models\Cinema\Movie;
use App\Models\User\User;

interface IMovieWorkerRepository {
  public function setWorkerForMovie(User $worker, Movie $movie): bool;

  public function setEmergencyWorkerForMovie(User $emergencyWorker, Movie $movie): bool;

  public function removeWorkerFromMovie(Movie $movie): bool;

  public function removeEmergencyWorkerFromMovie(Movie $movie): bool;

  public function getMoviesWhereUserAppliedAsWorker(User $user): array;
}
