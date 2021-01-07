<?php

namespace App\Repositories\Cinema\Movie;

use App\Models\Cinema\Movie;
use Exception;
use JetBrains\PhpStorm\ArrayShape;

class MovieRepository implements IMovieRepository {
  /**
   * @param int $id
   * @return Movie|null
   */
  public function getMovieById(int $id): ?Movie {
    return Movie::find($id);
  }

  /**
   * @return Movie[]
   */
  public function getAllMoviesOrderedByDate(): array {
    return Movie::orderBy('date')
      ->get()->all();
  }

  /**
   * @param string $name
   * @param string $date
   * @param string $trailerLink
   * @param string $posterLink
   * @param int $bookedTickets
   * @param int $movieYearId
   * @return Movie|null
   */
  public function createMovie(
    string $name,
    string $date,
    string $trailerLink,
    string $posterLink,
    int $bookedTickets,
    int $movieYearId
  ): ?Movie {
    $movie = new Movie([
      'name' => $name,
      'date' => $date,
      'trailerLink' => $trailerLink,
      'posterLink' => $posterLink,
      'bookedTickets' => $bookedTickets,
      'movie_year_id' => $movieYearId,]);

    if ($movie->save()) {
      return $movie;
    } else {
      return null;
    }
  }

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
  ): ?Movie {
    $movie->name = $name;
    $movie->date = $date;
    $movie->trailerLink = $trailerLink;
    $movie->posterLink = $posterLink;
    $movie->bookedTickets = $bookedTickets;
    $movie->movie_year_id = $movieYearId;

    if ($movie->save()) {
      return $movie;
    } else {
      return null;
    }
  }

  /**
   * @param Movie $movie
   * @return bool
   * @throws Exception
   */
  public function deleteMovie(Movie $movie): bool {
    return $movie->delete();
  }

  /**
   * @param int $userId
   * @return array
   */
  #[ArrayShape(["id" => "int", 'name' => "string", 'date' => "string", 'trailer_link' => "string", 'poster_link' => "string", 'booked_tickets' => "int", 'movie_year_id' => "int", 'created_at' => "string", 'updated_at' => "string", 'booked_tickets_for_yourself' => 'int'])]
  public function getNotShownMoviesForUser(int $userId): array {
    $allMovies = $this->getAllMoviesOrderedByDate();
    $movies = [];

    foreach ($allMovies as $movie) {
      if ((time() - (60 * 60 * 24)) < strtotime($movie->date . ' 05:00:00')) {
        $movies[] = $movie;
      }
    }

    $returnableMovies = [];
    foreach ($movies as $movie) {
      $returnable = $movie->toArray();
      $returnable['booked_tickets_for_yourself'] = $movie->getBookedTicketsForUser($userId);
      $returnableMovies[] = $returnable;
    }

    return $returnableMovies;
  }
}
