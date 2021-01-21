<?php

namespace App\Repositories\Cinema\MovieBooking;

use App\Models\Cinema\Movie;
use App\Models\Cinema\MoviesBooking;
use App\Models\User\User;
use App\Utils\ArrayHelper;
use Exception;

class MovieBookingRepository implements IMovieBookingRepository {
  /**
   * @param Movie $movie
   * @param User $user
   * @return MoviesBooking|null
   */
  public function getMovieBookingByMovieAndUser(Movie $movie, User $user): ?MoviesBooking {
    return MoviesBooking::where('user_id', $user->id)
      ->where('movie_id', $movie->id)
      ->first();
  }

  /**
   * @param Movie $movie
   * @param User $user
   * @param int $ticketAmount
   * @return MoviesBooking|null
   */
  public function bookTickets(Movie $movie, User $user, int $ticketAmount): ?MoviesBooking {
    $movieBooking = $this->getMovieBookingByMovieAndUser($movie, $user);

    /* If movie booking doesn't exist, create it */
    if ($movieBooking == null) {
      $movieBooking = new MoviesBooking([
        'user_id' => $user->id,
        'movie_id' => $movie->id,
        'amount' => $ticketAmount,]);

      if ($movieBooking->save()) {
        /* If movie booking was successful, update movie booked tickets */
        $movie->bookedTickets += $ticketAmount;
        $movie->save();

        return $movieBooking;
      } else {
        return null;
      }
    }

    /* Else update existing booking */
    $movieBooking->amount += $ticketAmount;

    if ($movieBooking->save()) {
      /* If movie booking was successful, update movie booked tickets */
      $movie->bookedTickets += $ticketAmount;
      $movie->save();

      return $movieBooking;
    } else {
      return null;
    }
  }

  /**
   * @param Movie $movie
   * @param User $user
   * @return MoviesBooking|null
   * @throws Exception
   */
  public function cancelBooking(Movie $movie, User $user): ?MoviesBooking {
    $movieBooking = $this->getMovieBookingByMovieAndUser($movie, $user);

    if ($movieBooking == null) {
      return null;
    }

    $ticketsAmount = $movieBooking->amount;
    if ($movieBooking->delete()) {
      $movie->bookedTickets -= $ticketsAmount;

      return null;
    } else {
      return $movieBooking;
    }
  }

  /**
   * @param int $userId
   * @return Movie[]
   */
  public function getMoviesWhereUserBookedTickets(int $userId): array {
    return Movie::find(ArrayHelper::getPropertyArrayOfObjectArray(MoviesBooking::where('user_id', '=', $userId)->get()->all(), 'movie_id'))->all();
  }
}
