<?php

namespace App\Repositories\Cinema\Movie;

use App\Models\Cinema\Movie;
use Exception;
use Illuminate\Database\Eloquent\Collection;

interface IMovieRepository {
  /**
   * @param int $id
   * @return Movie|null
   */
  public function getMovieById(int $id);

  /**
   * @return Movie[] | Collection
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
   * @param int $userId
   * @return array
   */
  #[ArrayShape(["id" => "int", 'name' => "string", 'date' => "string", 'trailer_link' => "string", 'poster_link' => "string", 'booked_tickets' => "int", 'movie_year_id' => "int", 'created_at' => "string", 'updated_at' => "string", 'booked_tickets_for_yourself' => 'int'])]
  public function getNotShownMoviesForUser(int $userId): array;
}
