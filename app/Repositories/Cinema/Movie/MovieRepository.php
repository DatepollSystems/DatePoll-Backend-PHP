<?php


namespace App\Repositories\Cinema\Movie;

use App\Models\Cinema\Movie;
use App\Models\User\User;

class MovieRepository implements IMovieRepository
{

  public function getMovieById(int $id) {
    return Movie::find($id);
  }

  public function getAllMoviesOrderedByDate() {
    return Movie::orderBy('date')
                ->get();
  }

  public function createMovie(string $name, string $date, string $trailerLink, string $posterLink, int $bookedTickets, int $movieYearId) {
    $movie = new Movie([
      'name' => $name,
      'date' => $date,
      'trailerLink' => $trailerLink,
      'posterLink' => $posterLink,
      'bookedTickets' => $bookedTickets,
      'movie_year_id' => $movieYearId]);

    if ($movie->save()) {
      return $movie;
    } else {
      return null;
    }
  }

  public function updateMovie(Movie $movie, string $name, string $date, string $trailerLink, string $posterLink, int $bookedTickets, int $movieYearId) {
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

  public function deleteMovie(Movie $movie): bool {
    return $movie->delete();
  }

  public function getNotShownMoviesForUser(User $user): array {
    $allMovies = $this->getAllMoviesOrderedByDate();
    $movies = [];

    foreach ($allMovies as $movie) {
      if ((time() - (60 * 60 * 24)) < strtotime($movie->date . ' 05:00:00')) {
        $movies[] = $movie;
      }
    }

    $returnableMovies = array();
    foreach ($movies as $movie) {
      $returnable = $movie->getReturnable();

      $movieBookingForYourself = $user->moviesBookings()
                                      ->where('movie_id', $movie->id)
                                      ->first();

      if ($movieBookingForYourself == null) {
        $returnable->booked_tickets_for_yourself = 0;
      } else {
        $returnable->booked_tickets_for_yourself = $movieBookingForYourself->amount;
      }

      $returnable->view_movie = [
        'href' => 'api/v1/cinema/movie/administration/' . $movie->id,
        'method' => 'GET'];
      $returnableMovies[] = $returnable;
    }

    return $returnableMovies;
  }
}