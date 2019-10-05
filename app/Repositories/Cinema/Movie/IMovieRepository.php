<?php

namespace App\Repositories\Cinema\Movie;

use App\Models\Cinema\Movie;
use App\Models\User\User;
use Exception;

interface IMovieRepository
{
  /**
   * @param int $id
   * @return Movie|null
   */
  public function getMovieById(int $id);

  /**
   * @return Movie[]
   */
  public function getAllMoviesOrderedByDate();

  /**
   * @param string $name
   * @param string $date
   * @param string $trailerLink
   * @param string $posterLink
   * @param int $bookedTickets
   * @param int $movieYearId
   * @return Movie|null
   */
  public function createMovie(string $name, string $date, string $trailerLink, string $posterLink, int $bookedTickets, int $movieYearId);

  /**
   * @param Movie $movie
   * @param string $name
   * @param string $date
   * @param string $trailerLink
   * @param string $posterLink
   * @param int $bookedTickets
   * @param int $movieYearId
   * @return Movie|null
   */
  public function updateMovie(Movie $movie, string $name, string $date, string $trailerLink, string $posterLink, int $bookedTickets, int $movieYearId);

  /**
   * @param Movie $movie
   * @return bool
   * @throws Exception
   */
  public function deleteMovie(Movie $movie): bool;

  /**
   * @param User $user
   * @return array
   */
  public function getNotShownMoviesForUser(User $user): array;
}