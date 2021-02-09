<?php

namespace App\Repositories\Cinema\MovieWorker;

use App\Models\Cinema\Movie;
use App\Models\User\User;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use App\Utils\ArrayHelper;
use App\Utils\DateHelper;
use JetBrains\PhpStorm\ArrayShape;
use stdClass;

class MovieWorkerRepository implements IMovieWorkerRepository {

  public function __construct(private IUserSettingRepository $userSettingRepository) {
  }

  /**
   * @param int $workerId
   * @param Movie $movie
   * @return bool
   */
  public function setWorkerForMovie(int $workerId, Movie $movie): bool {
    $movie->worker_id = $workerId;

    return $movie->save();
  }

  /**
   * @param int $emergencyWorkerId
   * @param Movie $movie
   * @return bool
   */
  public function setEmergencyWorkerForMovie(int $emergencyWorkerId, Movie $movie): bool {
    $movie->emergency_worker_id = $emergencyWorkerId;

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
    $movies = [];
    $moviesIDs = [];

    $movieWorkerShowsNumber = $this->userSettingRepository->getShareMovieWorkerPhoneNumber($user->id);
    foreach ($user->workerMovies() as $movie) {
      if (DateHelper::ifFirstTimestampIsBeforeSecondOne(DateHelper::removeDayFromUnixTimestamp(),
        DateHelper::convertStringDateToUnixTimestamp($movie->date . ' 20:00:00'))) {
        $moviesIDs[] = $movie->id;
        $movies[] = $this->getMoviesBookingWithMovie($movie, $movieWorkerShowsNumber);
      }
    }

    foreach ($user->emergencyWorkerMovies() as $movie) {
      if (DateHelper::ifFirstTimestampIsBeforeSecondOne(DateHelper::removeDayFromUnixTimestamp(),
        DateHelper::convertStringDateToUnixTimestamp($movie->date . ' 20:00:00'))) {
        if (ArrayHelper::notInArray($moviesIDs, $movie->id)) {
          $movies[] = $this->getMoviesBookingWithMovie($movie, $movieWorkerShowsNumber);
        }
      }
    }

    return $movies;
  }

  /**
   * @param Movie $movie
   * @param bool $movieWorkerShowsNumbers
   * @return array
   */
  #[ArrayShape(['movie_name' => "string", 'movie_id' => "int", 'date' => "string", 'orders' => "array"])]
  private function getMoviesBookingWithMovie(Movie $movie, bool $movieWorkerShowsNumbers): array {
    $orders = [];
    foreach ($movie->moviesBookings() as $moviesBooking) {
      $bookingUser = $moviesBooking->user;
      $localBooking = ['user_name' => $bookingUser->getCompleteName(),
                       'user_id ' => $bookingUser->id,
                       'amount ' => $moviesBooking->amount,];
      if ($movieWorkerShowsNumbers && $this->userSettingRepository->getShareMovieWorkerPhoneNumber($bookingUser->id)) {
        $localBooking['numbers'] = $bookingUser->telephoneNumbers();
      } else {
        $localBooking['numbers'] = [];
      }
      $orders[] = $localBooking;
    }
    return ['movie_name' => $movie->name,
            'movie_id' => $movie->id,
            'date' => $movie->date,
            'orders' => $orders];
  }
}
