<?php

namespace App\Repositories\Cinema\MovieBooking;

use App\Models\Cinema\Movie;
use App\Models\Cinema\MoviesBooking;
use App\Models\User\User;
use Exception;

interface IMovieBookingRepository
{
  /**
   * @param Movie $movie
   * @param User $user
   * @return MoviesBooking|null
   */
  public function getMovieBookingByMovieAndUser(Movie $movie, User $user);

  /**
   * @param Movie $movie
   * @param User $user
   * @param int $ticketAmount
   * @return MoviesBooking|null
   */
  public function bookTickets(Movie $movie, User $user, int $ticketAmount);

  /**
   * @param Movie $movie
   * @param User $user
   * @return MoviesBooking|null
   * @throws Exception
   */
  public function cancelBooking(Movie $movie, User $user);
}