<?php

namespace App\Repositories\Cinema\MovieBooking;

use App\Models\Cinema\Movie;
use App\Models\Cinema\MoviesBooking;
use App\Models\User\User;

class MovieBookingRepository implements IMovieBookingRepository
{

  public function getMovieBookingByMovieAndUser(Movie $movie, User $user) {
    return MoviesBooking::where('user_id', $user->id)
                        ->where('movie_id', $movie->id)
                        ->first();
  }

  public function bookTickets(Movie $movie, User $user, int $ticketAmount) {
    $movieBooking = $this->getMovieBookingByMovieAndUser($movie, $user);

    /* If movie booking doesn't exist, create it */
    if ($movieBooking == null) {
      $movieBooking = new MoviesBooking([
        'user_id' => $user->id,
        'movie_id' => $movie->id,
        'amount' => $ticketAmount]);

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

  public function cancelBooking(Movie $movie, User $user) {
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
}