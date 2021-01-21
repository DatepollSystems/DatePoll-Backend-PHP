<?php

namespace App\Repositories\Cinema\Movie;

use App\Models\Cinema\Movie;
use Exception;

interface IMovieRepository {
  /**
   * @param int $id
   * @return Movie|null
   */
  public function getMovieById(int $id): ?Movie;

  /**
   * @return int[]
   */
  public function getYearsOfMovies(): array;

  /**
   * @param int|null $year
   * @return Movie[]
   */
  public function getAllMoviesOrderedByDate(int $year = null): array;

  /**
   * @param string $name
   * @param string $date
   * @param string $trailerLink
   * @param string $posterLink
   * @param int $bookedTickets
   * @param int $maximalTickets
   * @return Movie|null
   */
  public function createMovie(
    string $name,
    string $date,
    string $trailerLink,
    string $posterLink,
    int $bookedTickets = 0,
    int $maximalTickets = 20
  ): ?Movie;

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
  public function updateMovie(
    Movie $movie,
    string $name,
    string $date,
    string $trailerLink,
    string $posterLink,
    int $bookedTickets,
    int $movieYearId
  ): ?Movie;

  /**
   * @param Movie $movie
   * @return bool
   * @throws Exception
   */
  public function deleteMovie(Movie $movie): bool;

  /**
   * @param int $userId
   * @return array
   */
  public function getNotShownMoviesForUser(int $userId): array;
}
