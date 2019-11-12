<?php


namespace App\Repositories\Cinema\MovieWorker;

use App\Models\Cinema\Movie;
use App\Models\User\User;
use stdClass;

class MovieWorkerRepository implements IMovieWorkerRepository
{

  /**
   * @param User $worker
   * @param Movie $movie
   * @return bool
   */
  public function setWorkerForMovie(User $worker, Movie $movie): bool {
    $movie->worker_id = $worker->id;

    return $movie->save();
  }

  /**
   * @param User $emergencyWorker
   * @param Movie $movie
   * @return bool
   */
  public function setEmergencyWorkerForMovie(User $emergencyWorker, Movie $movie): bool {
    $movie->emergency_worker_id = $emergencyWorker->id;

    return $movie->save();
  }

  /**
   * @param Movie $movie
   * @return bool
   */
  public function removeWorkerFromMovie(Movie $movie): bool {
    $movie->worker_id = null;

    return $movie->save();
  }

  /**
   * @param Movie $movie
   * @return bool
   */
  public function removeEmergencyWorkerFromMovie(Movie $movie): bool {
    $movie->emergency_worker_id = null;

    return $movie->save();
  }

  /**
   * @param User $user
   * @return array
   */
  public function getMoviesWhereUserAppliedAsWorker(User $user): array {
    $movies = array();
    $moviesIDs = array();

    foreach ($user->workerMovies() as $movie) {
      if ((time() - (60 * 60 * 24)) < strtotime($movie->date . ' 20:00:00')) {
        $moviesIDs[] = $movie->id;

        $localMovie = new stdClass();
        $localMovie->movie_name = $movie->name;
        $localMovie->movie_id = $movie->id;
        $localMovie->date = $movie->date;

        $orders = array();
        foreach ($movie->moviesBookings() as $moviesBooking) {
          $localBooking = new stdClass();
          $bookingUser = $moviesBooking->user();
          $localBooking->user_name = $bookingUser->firstname . ' ' . $bookingUser->surname;
          $localBooking->user_id = $bookingUser->id;
          $localBooking->amount = $moviesBooking->amount;
          $orders[] = $localBooking;
        }
        $localMovie->orders = $orders;

        $movies[] = $localMovie;
      }
    }

    foreach ($user->emergencyWorkerMovies() as $movie) {
      if ((time() - (60 * 60 * 24)) < strtotime($movie->date . ' 20:00:00')) {
        if (!in_array($movie->id, $moviesIDs)) {
          $localMovie = new stdClass();
          $localMovie->movie_name = $movie->name;
          $localMovie->movie_id = $movie->id;
          $localMovie->date = $movie->date;

          $orders = array();
          foreach ($movie->moviesBookings() as $moviesBooking) {
            $localBooking = new stdClass();
            $bookingUser = $moviesBooking->user();
            $localBooking->user_name = $bookingUser->firstname . ' ' . $bookingUser->surname;
            $localBooking->user_id = $bookingUser->id;
            $localBooking->amount = $moviesBooking->amount;
            $orders[] = $localBooking;
          }
          $localMovie->orders = $orders;

          $movies[] = $localMovie;
        }
      }
    }

    return $movies;
  }
}